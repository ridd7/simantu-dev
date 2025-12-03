<?php

namespace App\Http\Controllers;

use App\Models\Pekerjaan;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RealisasiPekerjaanController extends Controller
{
 
    public function index(Request $request)
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
        ->with('title','Realisasi Pekerjaan')
        ->with('content', 'pages/realisasi_pekerjaan/realisasi_pekerjaan_view')
        ->with('after_page', 'pages/realisasi_pekerjaan/realisasi_pekerjaan_after_page')
        ->with('notification_koordinator', $new_pekerjaan->count())
        ->with('notification_pekerjaan_saya', $new_pekerjaan_saya->count())
        ->with('notification_assessment', $new_assessment->count())
        ->with('notification_supervisi', $new_supervisi->count())
        ->with('name',$user->name);
    }

    public function ajax_list(Request $request) {
        $user = auth()->user();
        $id_user = $user->id;

        $search = $request->input('search.value');
		$order = $request->input('order');

		$query = Pekerjaan::where('pekerjaan', 'like', '%'.$search.'%')
                    ->join('users', 'pekerjaan.koordinator_id', '=', 'users.id')
                    ->join('pekerjaan_detail', 'pekerjaan.id', '=', 'pekerjaan_detail.pekerjaan_id')
                    ->selectRaw('pekerjaan.*, users.id as koordinator_id, users.name as koordinator_name')
                    ->where('pekerjaan.koordinator_id', $user->id)
                    ->orWhere('pekerjaan_detail.petugas_id', $user->id)
                    ->orWhere('pekerjaan_detail.assessment_id', $user->id)
                    ->distinct('pekerjaan.id');
		$recordsCount = $query->count();
		$columns = ['pekerjaan.pekerjaan', 'pekerjaan.start_date', 'pekerjaan.end_date', 'users.name', 8 => 'pekerjaan.status_pekerjaan'];

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
			$query->orderBy('pekerjaan.start_date', 'desc');
		}
		
		if ($request->length != -1) {
		    $query->skip($request->start)->take($request->length);
		}

		$pekerjaan = $query->get();

        $data = array();
        $no = $_POST['start'];

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
								->distinct()
                                ->pluck('pekerjaan_id')
                                ->toArray();
        foreach ($pekerjaan as $r) {
            $petugas = '';
            $assessment = '';
            $tugas = '';
            if($r->status_pekerjaan == 'finish'){
                $btn_color = 'btn-success';
            } else if($r->status_pekerjaan == 'on progress') {
                $tugas_active = DB::table('pekerjaan_detail')
                    ->where('pekerjaan_detail.pekerjaan_id', $r->id)
                    ->whereIn('pekerjaan_detail.status_tugas', ['on going', 'submit', 'stuck'])
                    ->join('pekerjaan', 'pekerjaan.id', '=', 'pekerjaan_detail.pekerjaan_id')
                    ->orderBy('urutan', 'asc')
                    ->select('pekerjaan_detail.*', 'pekerjaan.koordinator_id as koordinator_id')
                    ->first();

                if ($tugas_active) {
                    if ($user->id == $tugas_active->petugas_id && $tugas_active->status_tugas == 'on going') {
                        $btn_color = 'btn-danger';
                    } else {
                        $btn_color = 'btn-warning';
                    }
                }
                $detail_pekerjaan = DB::table('pekerjaan_detail')
                ->where('pekerjaan_detail.pekerjaan_id', $r->id)
                ->where('pekerjaan_detail.status_tugas', '!=', 'approved')
                ->join('users as petugas', 'pekerjaan_detail.petugas_id', '=', 'petugas.id')
                ->join('users as assessment', 'pekerjaan_detail.assessment_id', '=', 'assessment.id')
                ->orderBy('urutan', 'asc')
                ->select('pekerjaan_detail.nama_tugas', 'petugas.name as petugas_name', 'assessment.name as assessment_name')
                ->first();
    
                $tugas = $detail_pekerjaan->nama_tugas ?? '';
                $petugas = $detail_pekerjaan->petugas_name ?? '';
                $assessment = $detail_pekerjaan->assessment_name ?? '';
            }

            $countFinish = DB::table('pekerjaan_detail')
                ->where('pekerjaan_id', $r->id)
                ->where('status_tugas', 'approved')
                ->count();

            $countAll = DB::table('pekerjaan_detail')
                ->where('pekerjaan_id', $r->id)
                ->count();

            $pekerjaan = DB::table('pekerjaan')
                ->where('id', $r->id)
                ->first();
                
            $real_end_date = Carbon::parse($pekerjaan->real_end_date); // Convert start_date to Carbon instance
            $start_date = Carbon::parse($pekerjaan->start_date); // Convert start_date to Carbon instance
            $end_date = Carbon::parse($pekerjaan->end_date); // Convert end_date to Carbon instance
            $now = Carbon::now(); // Get the current date and time
            $timezone = new DateTimeZone('Asia/Jakarta');
            $now = new DateTime('now', $timezone);
            $now = Carbon::parse($now->format('Y-m-d'));
            if($pekerjaan->real_end_date != null) {
                $time_used = $real_end_date->diffInDays($start_date) . ' hari';
                if($real_end_date > $end_date) {
                    $time_remaining = $real_end_date->diffInDays($end_date) . ' hari';
                } else {
                    $time_remaining = '-';
                }
            } else {
                $time_used = $now->diffInDays($start_date) . ' hari';
                $time_remaining = $now->diffInDays($end_date) . ' hari';
            }
            $work_progress = $countFinish / $countAll * 100;
            if (strpos($work_progress, '.') !== false) {
                $work_progress = number_format($work_progress, 2);
            }
            $html_alur = '<a href="javascript:void(0)" class="btn btn-sm btn-primary" style="margin-right:0px !important" title="Lihat Alur" onclick="view_timeline(\'' . $r->id . '\')"> <i class="fas fa-search"></i>
            </a>';
            $html_detail = '<div class="btn-group btn-group-solid">
                            <a href="'.url("/realisasi-pekerjaan/detail/".$r->id).'" class="btn btn-sm btn-primary">
                            Detail</a>
                        </div>';

            $no++;
            $row = array();
            $row[] = $no;
            $row[] = in_array($r->id, $new_pekerjaan_saya) ? $r->pekerjaan . ' <span class="badge bg-danger" style="font-size: .5em; margin-left:2px">NEW</span>' : $r->pekerjaan;
            $row[] = date('d-m-Y', strtotime($r->start_date));
            $row[] = $r->end_date ? date('d-m-Y', strtotime($r->end_date)) : '';
            $row[] = $r->koordinator_name;
            $row[] = $tugas ? $tugas . ' (' . $petugas . ')' : '-';
            $row[] = $assessment;
            $row[] = '<div class="progress">
                        <div class="progress-bar progress-bar-striped '.($work_progress >= 100 ? 'bg-success' : 'bg-primary').'" role="progressbar" style="width: '.$work_progress.'%;" aria-valuenow="'.$work_progress.'" aria-valuemin="0" aria-valuemax="100">'.$work_progress.'%</div>
                    </div>';
            $row[] = $time_used;
            $row[] = $time_remaining;
            $row[] = $html_alur;
            $row[] = '<a href="javascript:void(0)" class="btn btn-sm btn-primary" style="margin-right:0px !important" title="Lihat Alur" onclick="view_dialog(\'' . $r->id . '\')"> <i class="fas fa-comment"></i>
            </a>';
            $row[] = '<button class="btn btn-sm '.$btn_color.'">'.$r->status_pekerjaan.'</button>';
            $row[] = $html_detail;

            $data[] = $row;
        }    

        DB::table('pekerjaan_detail')
            ->where('petugas_id', $user->id)
            ->where('notification_user_petugas', 1)
            ->whereNotNull('real_start_date')
            ->update(['notification_user_petugas' => 0]);

        DB::table('pekerjaan_detail')
            ->where('assessment_id', $user->id)
            ->where('notification_user_assessment', 1)
            ->whereNotNull('real_start_date')
            ->update(['notification_user_assessment' => 0]);

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $recordsCount,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data,
        );

        echo json_encode($output);
    }

    public function detail($id) {
        $user = auth()->user();
        $pekerjaan = Pekerjaan::join('users', 'pekerjaan.koordinator_id', '=', 'users.id')
                    ->where('pekerjaan.id', $id)
                    ->select('pekerjaan.*', 'users.name as koordinator_name')
                    ->get();
        
        $events = array();
        $detail_pekerjaan = DB::table('pekerjaan_detail')
                ->where('pekerjaan_id', $id)
                ->join('users', 'pekerjaan_detail.petugas_id', '=', 'users.id')
                ->orderBy('urutan', 'asc')
                ->get();
        
        foreach ($detail_pekerjaan as $row) {
            $events[] = [
                'title' => $row->nama_tugas,
                'name' => $row->name,
                'progress' => $row->progress,
                'start' => $row->start_date,
                'urutan' => $row->urutan,
                'end' => $row->end_date,
                'real_start' => $row->real_start_date,
                'real_end' => $row->real_end_date,
            ];
        }

        return view('master_page')
            ->with('title','Detail Pekerjaan')
            ->with('content', 'pages/realisasi_pekerjaan/realisasi_pekerjaan_detail_view')
            ->with('after_page', 'pages/realisasi_pekerjaan/realisasi_pekerjaan_detail_after_page')
            ->with('name',$user->name)
            ->with('level_assessment',$user->level)
            ->with('pekerjaan',$pekerjaan)
            ->with('events',$events)
            ->with('id_pekerjaan',$id);
    }

    public function timeline_ajax($id) {
        $data['tugas'] = DB::table('pekerjaan_detail')
					->where('pekerjaan_id', $id)
				    ->join('users', 'pekerjaan_detail.petugas_id', '=', 'users.id')
					->orderBy('urutan', 'asc')
					->selectRaw('pekerjaan_detail.*, users.id as petugas_id, users.name as petugas_name')
					->get();
        
        $data['tugas_active'] = DB::table('pekerjaan_detail')
            ->where('pekerjaan_detail.pekerjaan_id', $id)
            ->whereIn('pekerjaan_detail.status_tugas', ['on going', 'submit', 'stuck'])
            ->orderBy('urutan', 'asc')
            ->select('pekerjaan_detail.id')
            ->first();
		return response()->json($data);
    }

    public function detail_ajax_list(Request $request, $id) {
        $user = auth()->user();

        $search = $request->input('search.value');
		$order = $request->input('order');

		$query = DB::table('pekerjaan_detail')
                    ->where('nama_tugas', 'like', '%'.$search.'%')
                    ->where('pekerjaan_id', $id)
                    ->join('users', 'pekerjaan_detail.petugas_id', '=', 'users.id')
                    ->join('pekerjaan', 'pekerjaan.id', '=', 'pekerjaan_detail.pekerjaan_id')
                    ->selectRaw('pekerjaan_detail.*, users.id as id_user, users.name as name, pekerjaan.koordinator_id as koordinator_id');
		$recordsCount = $query->count();
		$columns = ['pekerjaan_detail.nama_tugas', 'users.name', 'pekerjaan_detail.start_date', 'pekerjaan_detail.end_date', 'pekerjaan_detail.status_tugas'];

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
			$query->orderBy('pekerjaan_detail.start_date', 'asc');
		}
		
		if ($request->length != -1) {
		    $query->skip($request->start)->take($request->length);
		}

        $detail_pekerjaan = $query->get();	

        $tugas_active = DB::table('pekerjaan_detail')
            ->where('pekerjaan_detail.pekerjaan_id', $id)
            ->whereIn('pekerjaan_detail.status_tugas', ['on going', 'submit', 'stuck'])
            ->join('pekerjaan', 'pekerjaan.id', '=', 'pekerjaan_detail.pekerjaan_id')
            ->orderBy('urutan', 'asc')
            ->select('urutan', 'petugas_id', 'koordinator_id')
            ->first();

        $data = array();
        $no = $_POST['start'];
        $html_petugas = '';
        $html_koordinator = '';
        $now = Carbon::now(); // Get the current date and time
        foreach ($detail_pekerjaan as $r) {
            if($r->status_tugas == 'submit'){
                $btn_color = 'btn-warning';
            } else if($r->status_tugas == 'on going') {
                $btn_color = 'btn-secondary';
            } else if($r->status_tugas == 'approved') {
                $btn_color = 'btn-success';
            } else if($r->status_tugas == 'stuck') {
                $btn_color = 'btn-danger';
            }
            
            if($r->status_tugas == 'on going' && $r->petugas_id == $user->id){
                $html_petugas = '<div class="btn-group btn-group-solid">
                            <a href="javascript:void(0)" class="btn btn-sm btn-success" style="margin-right:0px !important" title="Input Progress"
                                onclick="add_progress(\'' . $r->id . '\')">
                                <i class="fas fa-spinner"></i> </a>
                            <a href="javascript:void(0)" class="btn btn-sm btn-primary" style="margin-right:0px !important" title="Submit Tugas"
                                onclick="add_laporan(\'' . $r->id . '\', \'Submit\')">
                                <i class="fas fa-upload"></i> </a>
                            <a href="javascript:void(0)" class="btn btn-sm btn-danger" style="margin-right:0px !important" title="Stuck Tugas"
                                onclick="add_laporan(\'' . $r->id . '\', \'Stuck\')">
                                <i class="fas fa-times"></i> </a>
                        </div>';
            }
            if($r->status_tugas == 'submit' && $r->assessment_id == $user->id){
                $html_assessment = '<div class="btn-group btn-group-solid">
                                <a href="javascript:void(0)" class="btn btn-sm btn-secondary" style="margin-right:0px !important" title="Ganti Petugas"
                                    onclick="feedback_stuck_laporan(\'' . $r->id . '\',\'' . $r->urutan . '\')">
                                    <i class="fas fa-exchange-alt"></i> </a>
                                <a href="javascript:void(0)" class="btn btn-sm btn-success" style="margin-right:0px !important" title="Setujui"
                                    onclick="feedback_laporan(\'' . $r->id . '\', \'approved\',\'' . $r->urutan . '\')">
                                    <i class="fas fa-check"></i> </a>
                                <a href="javascript:void(0)" class="btn btn-sm btn-danger" style="margin-right:0px !important" title="Tolak"
                                    onclick="feedback_laporan(\'' . $r->id . '\', \'rejected\',\'' . $r->urutan . '\')">
                                    <i class="fas fa-times"></i> </a>
                            </div>';
            } else if($r->status_tugas == 'stuck' && $r->assessment_id == $user->id) {
                $html_assessment = '<div class="btn-group btn-group-solid">
                                <a href="javascript:void(0)" class="btn btn-sm btn-secondary" style="margin-right:0px !important" title="Ganti Petugas"
                                    onclick="feedback_stuck_laporan(\'' . $r->id . '\',\'' . $r->urutan . '\')">
                                    <i class="fas fa-exchange-alt"></i> </a>
                            </div>';
            }

            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $r->nama_tugas;
            $row[] = $r->name;
            $row[] = date('d-m-Y', strtotime($r->start_date));
            $row[] = date('d-m-Y', strtotime($r->end_date));
            $row[] = $r->urutan ===  1 ? date('d-m-Y', strtotime($r->start_date)) : ($r->real_start_date ? date('d-m-Y', strtotime($r->real_start_date)) : '');
            $row[] = $r->urutan ===  1 ? date('d-m-Y', strtotime($r->end_date)) : ($r->real_target_date ? date('d-m-Y', strtotime($r->real_target_date)) : '');

            $row[] = '<button class="btn btn-sm '.$btn_color.'">'.$r->status_tugas.'</button>';

            $file = $r->file_submit;
            if($file) {
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                if ($extension == 'jpg' || $extension == 'png') {
                    $row[] = "<a href='" . url('storage/' . $file) . "' download><i class='fas fa-file-image' style='font-size: 35px;'></i></a>";
                } else if($extension == 'pdf') {
                    $row[] = "<a href='" . url('storage/' . $file) . "' download><i class='fas fa-file-pdf' style='font-size: 35px;'></i></a>";
                } else {
                    $row[] = "<a href='" . url('storage/' . $file) . "' download><i class='fas fa-file-download' style='font-size: 35px;'></i></a>";
                }
            } else {
                $row[] = "";
            }
           

            $row[] = $r->nilai;

            
            if ($tugas_active) {
                if ($user->id == $r->assessment_id && ($r->status_tugas == 'submit' || $r->status_tugas == 'stuck') && $tugas_active->urutan == $r->urutan) {
                    $row[] = $html_assessment;
                } else if ($user->id == $r->petugas_id && ($r->status_tugas == 'on going' && $tugas_active->urutan == $r->urutan ) && ($now >= ($r->urutan === 1 ? $r->start_date : $r->real_start_date))) {
                    $row[] = $html_petugas;
                } else {
                    $row[] = '';
                }
            } else {
                $row[] = '';
            }

            $data[] = $row;
        }    

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $recordsCount,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data,
        );

        echo json_encode($output);
    }

    public function add_laporan(Request $request) {
        $user = auth()->user();
        $id = $request->id;
        $pekerjaan_id = $request->id_pekerjaan;
        $pesan = $request->pesan;
        $status = $request->status;
        
        $pekerjaan_detail = DB::table('pekerjaan_detail')
            ->where('id', $id)
            ->first();

        
        $tgl_pesan = Carbon::now();
        $tgl_pesan = $tgl_pesan->format('Y-m-d H:i:s');
        
        if($status == "Submit") {
            $path = '';
            if($request->file('file')){
                $file = $request->file('file');
                $path = $file->store('file');
            }
            $real_start_date = $pekerjaan_detail->urutan == 1 ? $pekerjaan_detail->start_date : $pekerjaan_detail->real_start_date;
            $real_target_date = $pekerjaan_detail->urutan == 1 ? $pekerjaan_detail->end_date : $pekerjaan_detail->real_target_date;
            $target = ($tgl_pesan < $real_target_date) ? "in" : (($tgl_pesan == $real_target_date) ? "on" : "off");

            // Update the database with the file path
            DB::table('pekerjaan_detail')
            ->where('id', $id)
            ->update([
                'file_submit' => $path,
                'status_tugas' => 'submit',
                'real_start_date' => $real_start_date,
                'real_target_date' => $real_target_date,
                'progress' => 100,
                'submit_date' => $tgl_pesan,
                'target' => $target,
                'notification_assessment' => 1,
            ]);
            DB::table('dialog')->insert([
                'pengirim' => $user->name,
                'pesan' => $pesan,
                'file' => $path,
                'tgl_pesan' => $tgl_pesan,
                'pekerjaan_id' => $pekerjaan_id
            ]);
        } else if($status == 'Stuck'){
            // Update the database with the file path
            DB::table('pekerjaan_detail')
            ->where('id', $id)
            ->update([
                'status_tugas' => 'stuck',
                'notification_assessment' => 1,
            ]);
            DB::table('dialog')->insert([
                'pengirim' => $user->name,
                'pesan' => $pesan,
                'tgl_pesan' => $tgl_pesan,
                'pekerjaan_id' => $pekerjaan_id,
            ]);
        }
       
        return redirect()->back()->with(['success' => 'Laporan berhasil disubmit']);
    }

    public function proses_ajax_list(Request $request, $id) {

        $search = $request->input('search.value');
		$order = $request->input('order');

		$query = DB::table('dialog')
                ->where('pekerjaan_id', $id);
                
        
		$recordsCount = $query->count();
		$columns = ['pengirim', 'pesan', 'tgl_pesan'];

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
			$query->orderBy('tgl_pesan', 'asc');
		}
		
		if ($request->length != -1) {
		    $query->skip($request->start)->take($request->length);
		}

        $dialog = $query->get();

        $data = array();
        $no = $_POST['start'];
        foreach ($dialog as $r) {

            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $r->pengirim;
            $row[] = $r->pesan;
            $row[] = date('d-m-Y H:i:s', strtotime($r->tgl_pesan));
            $file = $r->file;
            if($file) {
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                if ($extension == 'jpg' || $extension == 'png') {
                    $row[] = "<a href='" . url('storage/' . $file) . "' download><i class='fas fa-file-image' style='font-size: 35px;'></i></a>";
                } else if($extension == 'pdf') {
                    $row[] = "<a href='" . url('storage/' . $file) . "' download><i class='fas fa-file-pdf' style='font-size: 35px;'></i></a>";
                } else {
                    $row[] = "<a href='" . url('storage/' . $file) . "' download><i class='fas fa-file-download' style='font-size: 35px;'></i></a>";
                }
            } else {
                $row[] = "";
            }

            $data[] = $row;
        }    

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $recordsCount,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data,
        );

        echo json_encode($output);
    }

    public function feedback_laporan(Request $request) {
        $user = auth()->user();
        $id = $request->id;
        $pekerjaan_id = $request->id_pekerjaan;

        $pesan = $request->pesan;
        $urutan = $request->urutan;
        $status = $request->status;

        // Update the database with the file path
        if ($status == 'rejected') {
            DB::table('pekerjaan_detail')
                ->where('id', $id)
                ->update([
                    'file_submit' => null,
                    'submit_date' => null,
                    'target' => null,
                    'status_tugas' => 'on going',
                    'progress' => null,
                    'real_end_date' => null,
                    'notification_user_petugas' => 1,
                    'notification_user_assessment' => 1,
                ]);
        } else if($status == 'approved') {
            $tgl_pesan = Carbon::now(); // Get the current date and time // Get the current date and time
            $tgl_pesan = $tgl_pesan->format('Y-m-d');

            $real_start_date = Carbon::now()->addDay(); // Get the current date and time
            DB::table('pekerjaan_detail')
                ->where('id', $id)
                ->update([
                    'status_tugas' => 'approved',
                    'nilai' => $request->nilai,
                    'real_end_date' =>  $tgl_pesan
                ]);

            $isStatusNotApproved = DB::table('pekerjaan_detail')
                ->where('pekerjaan_id', $pekerjaan_id)
                ->where('urutan', $urutan)
                ->where('status_tugas', '!=', 'approved')
                ->exists();

            if(!$isStatusNotApproved) {
                $isIssetUrutan = DB::table('pekerjaan_detail')
                ->where('pekerjaan_id', $pekerjaan_id)
                ->where('urutan', $urutan+1)
                ->exists();
    
                if($isIssetUrutan) {
                    $pekerjaan_detail = DB::table('pekerjaan_detail')
                        ->where('pekerjaan_id', $pekerjaan_id)
                        ->where('urutan', $urutan+1)
                        ->first();
                    $real_target_date = $real_start_date->copy()->addDays($pekerjaan_detail->durasi_plan-1);
                    DB::table('pekerjaan_detail')
                        ->where('pekerjaan_id', $pekerjaan_id)
                        ->where('urutan', $urutan+1)
                        ->update([
                            'real_start_date'=> $real_start_date,
                            'real_target_date' => $real_target_date
                        ]);
                } else {
                    DB::table('pekerjaan')
                    ->where('id', $pekerjaan_id)
                    ->update(['status_pekerjaan' => 'finish']);

                    $pekerjaan = DB::table('pekerjaan')
                        ->where('id', $pekerjaan_id)
                        ->first();
                    $target = ($tgl_pesan < $pekerjaan->end_date) ? "in" : (($tgl_pesan == $pekerjaan->end_date) ? "on" : "off");
                    DB::table('pekerjaan')
                    ->where('id', $pekerjaan_id)
                    ->update([
                        'status_pekerjaan' => 'finish',
                        'real_end_date' => $tgl_pesan,
                        'target' => $target
                    ]);
                }
            }
        } else if ($status == 'change') {
            DB::table('pekerjaan_detail')
                ->where('id', $id)
                ->update([
                    'file_submit' => null,
                    'status_tugas' => 'on going',
                    'petugas_id' => $request->petugas_id,
                    'submit_date' => null,
                    'target' => null,
                    'status_tugas' => 'on going',
                    'progress' => null,
                    'real_end_date' => null,
                    'notification_user_petugas' => 1,
                    'notification_user_assessment' => 1,
                ]);
        }

        $tgl_pesan = Carbon::now(); // Get the current date and time
        $tgl_pesan = $tgl_pesan->format('Y-m-d H:i:s');

        DB::table('dialog')->insert([
            'pengirim' => $user->name,
            'pesan' => $pesan,
            'tgl_pesan' => $tgl_pesan,
            'pekerjaan_id' => $pekerjaan_id
        ]);
        if ($status == 'rejected') {
            return redirect()->back()->with(['success' => 'Laporan ditolak']);
        } else if($status == 'approved') {
            return redirect()->back()->with(['success' => 'Laporan disetujui']);
        } else if($status == 'change') {
            return redirect()->back()->with(['success' => 'Petugas diperbarui']);
        }
    }

    public function ajax_update_progress(Request $request) {
        $id = $request->id;
        $progress = $request->progress;
        DB::table('pekerjaan_detail')
        ->where('id', $id)
        ->update([
            'progress' => $progress,
        ]);

        return response()->json(['status' => TRUE]);
    }

    public function ajax_edit_progress($id) {
        $tugas = DB::table('pekerjaan_detail')
        ->where('id', $id)
        ->select('id', 'progress')->first();
        $data['tugas'] = $tugas;
        echo json_encode($data);
    }

    public function get_petugas_by_assessment(Request $request) {
        $level_assessment = $request->level;
        $data = User::where('name', 'LIKE', '%'.$request->q.'%')
						->where('level_user', 'user')
						->where('level', '>' , $level_assessment)
						->limit(20)->get();

		return response()->json($data);
    }
}
