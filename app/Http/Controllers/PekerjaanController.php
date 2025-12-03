<?php

namespace App\Http\Controllers;

use App\Models\Pekerjaan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PekerjaanController extends Controller
{
    public function index()
    {
        $user = auth()->user();
		  $id_user = $user->id;
		  $new_pekerjaan = Pekerjaan::where('koordinator_id', $id_user)
                                                ->where('notification_koordinator', 1);
        $new_supervisi = Pekerjaan::where('koordinator_id', $id_user)
                            ->where('notification_supervisi', 1);		
		  $new_assessment = DB::table('pekerjaan_detail')
                            ->leftJoin('pekerjaan', 'pekerjaan_detail.pekerjaan_id', '=', 'pekerjaan.id')
                            ->where('pekerjaan_detail.assessment_id', $id_user)
                            ->where('pekerjaan_detail.notification_assessment', 1);
        $new_pekerjaan_saya = DB::table('pekerjaan_detail')
                            ->where(function ($query) use ($id_user) {
                                    $query->where('petugas_id', $id_user)
                                        ->where('notification_user_petugas', 1)
                                        ->whereNotNull('real_start_date');
                            })
                            ->orWhere(function ($query) use($id_user){
                                    $query->where('assessment_id', $id_user)
                                        ->where('notification_user_assessment', 1)
                                        ->whereNotNull('real_start_date');
                            })
                            ->select('pekerjaan_id')
                            ->distinct()
                            ->get();
        return view('master_page')
            ->with('title','Pekerjaan')
            ->with('content', 'pages/pekerjaan/pekerjaan_view')
            ->with('after_page', 'pages/pekerjaan/pekerjaan_after_page')
            ->with('name',$user->name)
            ->with('user_id',$user->id)
				->with('akses_koordinator', $user->koordinator_akses)
				->with('notification_koordinator', $new_pekerjaan->count())
				->with('notification_pekerjaan_saya', $new_pekerjaan_saya->count())
				->with('notification_assessment', $new_assessment->count())
				->with('notification_supervisi', $new_supervisi->count())
				->with('level_user', $user->level_user);
    }

    public function ajax_list(Request $request) {
		$user = auth()->user();
		$search = $request->input('search.value');
		$order = $request->input('order');

		$query = Pekerjaan::where('pekerjaan', 'like', '%'.$search.'%')
			->join('users', 'pekerjaan.koordinator_id', '=', 'users.id')
			->selectRaw('pekerjaan.*, users.id as koordinator_id, users.name as koordinator_name');
		$recordsCount = $query->count();
		$columns = ['pekerjaan.no_sk', 'pekerjaan.pekerjaan', 'pekerjaan.kategori', 'pekerjaan.start_date', 'pekerjaan.end_date', 'users.name', 7 => 'pekerjaan.status_pekerjaan'];

		if (!empty($search)) {
			$query->where(function($query) use ($search, $columns) {
					foreach ($columns as $index => $column) {
						if ($index === 0) {
							$query->where($column, 'like', '%'.$search.'%');
						} else {
							$query->orWhere($column, 'like', '%'.$search.'%');
						}
					}
			});
		}
		$recordsFiltered = $query->count();

		if (!empty($order)) {
			$orderColumn = $columns[$order[0]['column']-1];
			$orderDirection = $order[0]['dir'];
			$query->orderBy($orderColumn, $orderDirection);
		} else {
			$query->orderBy('start_date', 'desc');
		}
		
		if ($request->length != -1) {
			$query->skip($request->start)->take($request->length);
		}

		$pekerjaan = $query->get();

		$user = auth()->user();

		$data = array();
		$no = $request->start;
		$now = Carbon::now();

		$new_pekerjaan = Pekerjaan::where('koordinator_id', $user->id)
											->where('notification_koordinator', 1)
											->pluck('id')
											->toArray();
		foreach ($pekerjaan as $r) {

			$html_tugas = '<div class="btn-group btn-group-solid">
							<a href="javascript:void(0)" class="btn btn-sm btn-info" style="margin-right:0px !important" title="Lihat Tugas"
							onclick="view_tugas(\'' . $r->id . '\')">
							<i class="fa-fw fas fa-eye"></i> </a>';
			if(($user->level_user == 'admin' || $r->koordinator_id == $user->id) && $r->pesan_penugasan != '' && ($now < $r->start_date || $r->status_pekerjaan == 'forwarded')) {
				$html_tugas .= '<a href="javascript:void(0)" class="btn btn-sm btn-warning" style="margin-right:0px !important" title="Tambah Tugas"
				onclick="add_tugas(\'' . $r->id . '\', \'' . $r->kategori . '\')">
				<i class="fa-fw fas fa-plus"></i> </a>';
			}
			$html_tugas .= '</div>';

			if(($user->level_user == 'admin' || $user->manager_akses == 'Y')) {
				$html = '<div class="btn-group btn-group-solid">
								<a href="javascript:void(0)" class="btn btn-sm btn-primary" style="margin-right:0px !important" title="Edit"
								onclick="edit_pekerjaan(\'' . $r->id . '\')">
								<i class="fas fa-edit"></i> </a>
								<a href="javascript:void(0)" class="btn btn-sm btn-danger" style="margin-right:0px !important" title="Delete"
								onclick="delete_pekerjaan(\'' . $r->id . '\')">
								<i class="fas fa-trash"></i> </a>';
				
				if($r->status_pekerjaan == 'pending') {
					$html .= '<a href="javascript:void(0)" class="btn btn-sm btn-success" style="margin-right:0px !important" title="Proses"
					onclick="proses_pekerjaan(\'' . $r->id . '\')">
					<i class="fas fa-sync"></i></a>';
				}
				$html .= '</div>';
			}
			
			$no++;
			$row = array();
			$row[] = $no;
			$row[] = $r->no_sk;
			$row[] = in_array($r->id, $new_pekerjaan) ? $r->pekerjaan . ' <span class="badge bg-danger" style="font-size: .5em; margin-left:2px">NEW</span>' : $r->pekerjaan;
			$row[] = $r->kategori;
			$row[] = date('d-m-Y', strtotime($r->start_date));
			$row[] = $r->end_date ? date('d-m-Y', strtotime($r->end_date)) : '';
			$row[] = $r->koordinator_name;
			$file = $r->file_dasar_pelaksanaan;
			$extension = pathinfo($file, PATHINFO_EXTENSION);
			if ($extension == 'jpg' || $extension == 'png') {
				$row[] = "<a href='" . url('storage/' . $file) . "' download><i class='fas fa-file-image' style='font-size: 35px;'></i></a>";
			} else if($extension == 'pdf') {
				$row[] = "<a href='" . url('storage/' . $file) . "' download><i class='fas fa-file-pdf' style='font-size: 35px;'></i></a>";
			} else {
				$row[] = '';
			}

			$status = $r->status_pekerjaan; // Get the status value

			if ($status == "pending") {
				$class = "btn-danger";
			} elseif ($status == "forwarded") {
				$class = "btn-secondary";
			} elseif ($status == "on progress") {
				$class = "btn-warning";
			} elseif ($status == "finish") {
				$class = "btn-success";
			}

			$row[] = '<a href="javascript:void(0)" class="btn btn-sm ' . $class . '" style="margin-right:0px !important" title="Published" onclick="published(\'' . $r->id . '\')">' . $r->status_pekerjaan . '</a>';
			$row[] = $html_tugas;
			$row[] = isset($html) ? $html : '';

			$data[] = $row;
		}   
		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $recordsCount,
			"recordsFiltered" => $recordsFiltered,
			"data" => $data,
		);

		Pekerjaan::where('koordinator_id', $user->id)
				->where('notification_koordinator', 1)
				->update(['notification_koordinator' => 0]);

		echo json_encode($output);
   }

	 public function ajax_add(Request $request)
    {
			$path = '';
			if($request->file('dasar_pelaksanaan_file')) {
				$dasar_pelaksanaan_file = $request->file('dasar_pelaksanaan_file');
				$path = $dasar_pelaksanaan_file->store('file/dasar_pelaksanaan');
			}

			$pekerjaan = new Pekerjaan;
			$pekerjaan->pekerjaan = $request->pekerjaan;
			$pekerjaan->start_date = $request->start_date;
			$pekerjaan->koordinator_id = $request->koordinator_id;
			$pekerjaan->status_pekerjaan = 'pending';
			$pekerjaan->deskripsi_pekerjaan = $request->deskripsi_pekerjaan;
			$pekerjaan->no_sk = $request->no_sk;
			$pekerjaan->file_dasar_pelaksanaan = $path;
			$pekerjaan->kategori = $request->kategori;
			$pekerjaan->save();
			return json_encode(array("status" => TRUE));
    }

	 public function ajax_edit(Pekerjaan $pekerjaan)
    {
			$data['pekerjaan'] = $pekerjaan->load('koordinator');
			echo json_encode($data);
    }

    public function ajax_update(Request $request)
	 {
			$path = '';
			$pekerjaan = Pekerjaan::find($request->id);
			if($request->file('dasar_pelaksanaan_file')) {
				$dasar_pelaksanaan_file = $request->file('dasar_pelaksanaan_file');
				$path = $dasar_pelaksanaan_file->store('file/dasar_pelaksanaan');
				if($pekerjaan->file_dasar_pelaksanaan) {
					unlink('storage/'.$pekerjaan->file_dasar_pelaksanaan);
				}
			}

			$pekerjaan->pekerjaan = $request->pekerjaan;
			$pekerjaan->start_date = $request->start_date;
			$pekerjaan->koordinator_id = $request->koordinator_id;
			$pekerjaan->deskripsi_pekerjaan = $request->deskripsi_pekerjaan;
			$pekerjaan->no_sk = $request->no_sk;
			$pekerjaan->file_dasar_pelaksanaan = $path;
			$pekerjaan->kategori = $request->kategori;
			$pekerjaan->save();

      	return response()->json(['status' => TRUE]);
	 }

    public function ajax_delete(Pekerjaan $pekerjaan)
	 {
			$pekerjaan->delete();
			if($pekerjaan->file_dasar_pelaksanaan) {
				unlink('storage/'.$pekerjaan->file_dasar_pelaksanaan);
			}
			return json_encode(array("status" => TRUE));
	 }

	 public function get_petugas(Request $request) {
		$data = User::where('name', 'LIKE', '%'.$request->q.'%')
						->where('level_user', 'user')
						->limit(20)->get();

			return response()->json($data);
	 }

	 public function get_assessment(Request $request) {
		$id_petugas = $request->petugas;
		$level_petugas = User::find($id_petugas)->level;
		$data = User::where('name', 'LIKE', '%'.$request->q.'%')
						->where('level_user', 'user')
						->where('level', '<', $level_petugas)
						->limit(20)->get();

			return response()->json($data);
	 }

	 public function get_koordinator(Request $request) {
			$data = User::where('name', 'LIKE', '%'.$request->q.'%')
						->where('koordinator_akses', 'Y')
						->limit(20)->get();

			return response()->json($data);
	 }

	 public function get_alur(Request $request) {
		$kategori = $request->kategori;
		$data = DB::table('alur')
					->where('alur', 'LIKE', '%'.$request->q.'%')
					->where('kategori', $kategori)
					->limit(10)->get();

		return response()->json($data);
   }

	public function get_tugas($id_alur, $id_pekerjaan) {
		$data['tugas'] = DB::table('alur')
					->where('alur.id', $id_alur)
					->join('alur_tugas', 'alur.id', '=', 'alur_tugas.alur_id')
					->get();

		$data['tugas_id'] = DB::table('pekerjaan_detail')
					->where('pekerjaan_id', $id_pekerjaan)
					->pluck('id')
    				->toArray();
		return response()->json($data);
	}

	public function ajax_add_tugas(Request $request) {
		$user = auth()->user();
		$id = $request->id;
		$tugas = $request->tugas;
		$pesan = $request->pesan;

		$tgl_pesan = Carbon::now(); // Get the current date and time
      $tgl_pesan = $tgl_pesan->format('Y-m-d H:i:s');
		
		DB::table('dialog')->insert([
			'pengirim' => $user->name,
			'pesan' => $pesan,
			'tgl_pesan' => $tgl_pesan,
			'pekerjaan_id' => $id
	  ]);

		$start_date = DB::table('pekerjaan')
				->where('id', $id)
				->value('start_date');
		DB::table('pekerjaan_detail')
			->where('pekerjaan_id', $id)
			->whereNotIn('id', $request->id_tugas)
			->delete();

		if($request->alur_id) {
			$alur = DB::table('alur')
					  ->where('id', $request->alur_id)
					->select('jumlah_output', 'satuan_id')
					->first();
		} 
		if(!empty($tugas)) {
			$totalTugas = count($tugas);
			foreach ($tugas as $key => $value) {
				$end_date = Carbon::parse($start_date)->addDays($request->durasi[$key]-1);
				if ($request->id_tugas[$key]) {
					$updateData = [
						'nama_tugas' => $value,
						'durasi_plan' => $request->durasi[$key], 
						'petugas_id' => $request->petugas_id[$key],
						'assessment_id' => $request->assessment_id[$key],
						'start_date' => $start_date,
						'end_date' => $end_date,	
						'urutan' => $request->urutan[$key],
						'status_tugas' => 'on going',
						'notification_user_petugas' => 1,
						'notification_user_assessment' => 1,
				  ];
			 
				  if ($request->urutan[$key] == 1) {
						$updateData['real_start_date'] = $start_date;
				  }
			 
				  DB::table('pekerjaan_detail')
						->where('id', $request->id_tugas[$key])
						->update($updateData);
			  } else {
					$insertData = [
						'nama_tugas' => $value,
						'pekerjaan_id' => $id,
						'durasi_plan' => $request->durasi[$key], 
						'petugas_id' => $request->petugas_id[$key],
						'assessment_id' => $request->assessment_id[$key],
						'start_date' => $start_date,
						'end_date' => $end_date,	
						'urutan' => $request->urutan[$key],
						'status_tugas' => 'on going',
						'notification_user_petugas' => 1,
						'notification_user_assessment' => 1,
					];
					
					if ($request->urutan[$key] == 1) {
							$insertData['real_start_date'] = $start_date;
					}
					
					DB::table('pekerjaan_detail')->insert($insertData);
			  }
			  if($key != $totalTugas-1 && $request->urutan[$key] != $request->urutan[$key+1]) {
				  $start_date = Carbon::parse($end_date)->addDays(1);
			  }
				if ($key == $totalTugas-1) {
					$pekerjaan = Pekerjaan::find($id);
					$pekerjaan->end_date = $end_date;
					$pekerjaan->save();
				}
			}
		} else {
			DB::table('pekerjaan_detail')
				->where('pekerjaan_id', $id)
				->delete();
		}
		$pekerjaan = Pekerjaan::find($id);
		$pekerjaan->status_pekerjaan = 'on progress';
		$pekerjaan->notification_supervisi = 1;
		if(isset($alur)) {
			$pekerjaan->jumlah_output = $alur->jumlah_output;
			$pekerjaan->satuan_id = $alur->satuan_id;
		}
		$pekerjaan->save();
		return response()->json(['status' => TRUE]);
	}

	public function get_pekerjaan_detail_by_id($id) {
		$data = DB::table('pekerjaan_detail')
			 ->where('pekerjaan_id', $id)
			 ->join('users as petugas', 'pekerjaan_detail.petugas_id', '=', 'petugas.id')
			 ->join('users as assessment', 'pekerjaan_detail.assessment_id', '=', 'assessment.id')
			 ->orderBy('urutan', 'asc')
			 ->selectRaw('pekerjaan_detail.*, petugas.id as petugas_id, petugas.name as petugas_name, assessment.id as assessment_id, assessment.name as assessment_name')
			 ->get();
  
		return response()->json($data);
  }

	public function ajax_add_proses_pekerjaan(Request $request) {
		$user = auth()->user();
		$pekerjaan = Pekerjaan::find($request->id);
		$pekerjaan->pesan_penugasan = $request->pesan_penugasan;
		$pekerjaan->status_pekerjaan = 'forwarded';
		$pekerjaan->save();
		
		$tgl_pesan = Carbon::now(); // Get the current date and time
		$tgl_pesan = $tgl_pesan->format('Y-m-d H:i:s');

		DB::table('dialog')->insert([
			'pengirim' => $user->name,
			'pesan' => $request->pesan_penugasan,
			'tgl_pesan' => $tgl_pesan,
			'pekerjaan_id' => $request->id
	  ]);
		return json_encode(array("status" => TRUE));
	}

	public function get_pesan_penugasan($id) {
		$pesan_penugasan = Pekerjaan::find($id)->pesan_penugasan;
		return json_encode(array('pesan_penugasan' => $pesan_penugasan));
	}

}
