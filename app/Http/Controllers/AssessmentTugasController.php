<?php

namespace App\Http\Controllers;

use App\Models\Pekerjaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssessmentTugasController extends Controller
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
            ->with('title','Assessment Tugas')
            ->with('content', 'pages/assessment_tugas/assessment_tugas_view')
            ->with('after_page', 'pages/assessment_tugas/assessment_tugas_after_page')
            ->with('notification_koordinator', $new_pekerjaan->count())
            ->with('notification_pekerjaan_saya', $new_pekerjaan_saya->count())
            ->with('notification_assessment', $new_assessment->count())
            ->with('notification_supervisi', $new_supervisi->count())
            ->with('level_assessment',$user->level)
            ->with('name',$user->name);
    }

    public function ajax_list(Request $request)
    {
        $user = auth()->user();
        $search = $request->input('search.value');
		$order = $request->input('order');

		$query = DB::table('pekerjaan_detail')
                    ->where('nama_tugas', 'like', '%'.$search.'%')
                    ->where('pekerjaan_detail.assessment_id', $user->id)
                    ->whereIn('pekerjaan_detail.status_tugas', ['submit', 'stuck'])
                    ->join('users', 'pekerjaan_detail.petugas_id', '=', 'users.id')
                    ->join('pekerjaan', 'pekerjaan_detail.pekerjaan_id', '=', 'pekerjaan.id')
                    ->selectRaw('pekerjaan_detail.*, pekerjaan.pekerjaan, users.id as id_user, users.name as name');
		$recordsCount = $query->count();
		$columns = ['pekerjaan.pekerjaan', 'pekerjaan_detail.nama_tugas', 'users.name', 'pekerjaan_detail.start_date', 'pekerjaan_detail.end_date'];

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
			$query->orderBy('pekerjaan_detail.start_date', 'desc');
		}
		
		if ($request->length != -1) {
		    $query->skip($request->start)->take($request->length);
		}

		$detail_pekerjaan = $query->get();

        $data = array();
        $no = $_POST['start'];
        $new_assessment = DB::table('pekerjaan_detail')
                ->leftJoin('pekerjaan', 'pekerjaan_detail.pekerjaan_id', '=', 'pekerjaan.id')
                ->where('pekerjaan_detail.assessment_id', $user->id)
                ->where('pekerjaan_detail.notification_assessment', 1)
                ->pluck('pekerjaan_detail.id')
                ->toArray();	
        foreach ($detail_pekerjaan as $r) {
            if($r->status_tugas == 'submit'){
                $btn_color = 'btn-warning';
            } else if($r->status_tugas == 'stuck') {
                $btn_color = 'btn-danger';
            }

            if($r->status_tugas == 'submit'){
                $html_assessment = '<div class="btn-group btn-group-solid">
                                <a href="javascript:void(0)" class="btn btn-sm btn-secondary" style="margin-right:0px !important" title="Ganti Petugas"
                                    onclick="feedback_stuck_laporan(\'' . $r->id . '\',\'' . $r->urutan . '\',\'' . $r->pekerjaan_id . '\')">
                                    <i class="fas fa-exchange-alt"></i> </a>
                                <a href="javascript:void(0)" class="btn btn-sm btn-success" style="margin-right:0px !important" title="Setujui"
                                    onclick="feedback_laporan(\'' . $r->id . '\', \'approved\',\'' . $r->urutan . '\',\'' . $r->pekerjaan_id . '\')">
                                    <i class="fas fa-check"></i> </a>
                                <a href="javascript:void(0)" class="btn btn-sm btn-danger" style="margin-right:0px !important" title="Tolak"
                                    onclick="feedback_laporan(\'' . $r->id . '\', \'rejected\',\'' . $r->urutan . '\',\'' . $r->pekerjaan_id . '\')">
                                    <i class="fas fa-times"></i> </a>
                            </div>';
            } else if($r->status_tugas == 'stuck') {
                $html_assessment = '<div class="btn-group btn-group-solid">
                                <a href="javascript:void(0)" class="btn btn-sm btn-secondary" style="margin-right:0px !important" title="Ganti Petugas"
                                    onclick="feedback_stuck_laporan(\'' . $r->id . '\',\'' . $r->urutan . '\',\'' . $r->pekerjaan_id . '\')">
                                    <i class="fas fa-exchange-alt"></i> </a>
                            </div>';
            }

            $no++;
            $row = array();
            $row[] = $no;
            $row[] = in_array($r->id, $new_assessment) ? $r->pekerjaan . ' <span class="badge bg-danger" style="font-size: .5em; margin-left:2px">NEW</span>' : $r->pekerjaan;
            $row[] = $r->nama_tugas;
            $row[] = $r->name;
            $row[] = date('d-m-Y', strtotime($r->start_date));
            $row[] = $r->end_date ? date('d-m-Y', strtotime($r->end_date)) : '';
            $row[] = '<a href="javascript:void(0)" class="btn btn-sm btn-primary" style="margin-right:0px !important" title="Lihat Alur" onclick="view_timeline(\'' . $r->pekerjaan_id . '\')"> <i class="fas fa-search"></i>
            </a>';
            $row[] = '<a href="javascript:void(0)" class="btn btn-sm btn-primary" style="margin-right:0px !important" title="Lihat Pesan" onclick="view_dialog(\'' . $r->pekerjaan_id . '\')"> <i class="fas fa-comment"></i>
            </a>';
            $row[] = '<button class="btn btn-sm '.$btn_color.'">'.$r->status_tugas.'</button>';

            $file = $r->file_submit;
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            if ($extension === 'jpg' || $extension === 'png') {
                $row[] = "<a href='" . url('storage/' . $file) . "' download><i class='fas fa-file-image' style='font-size: 35px;'></i></a>";
            } else if($extension === 'pdf') {
                $row[] = "<a href='" . url('storage/' . $file) . "' download><i class='fas fa-file-pdf' style='font-size: 35px;'></i></a>";
            } else {
                $row[] = '-';
            }
            $row[] = $r->nilai;
            $row[] = $html_assessment;
            $data[] = $row;
        }    

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $recordsCount,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data,
        );

        DB::table('pekerjaan_detail')->leftJoin('pekerjaan', 'pekerjaan_detail.pekerjaan_id', '=', 'pekerjaan.id')
            ->where('pekerjaan_detail.assessment_id', $user->id)
            ->where('pekerjaan_detail.notification_assessment', 1)
            ->update(['notification_assessment' => 0]);	

        echo json_encode($output);
    }
}
