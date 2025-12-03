<?php

namespace App\Http\Controllers;

use App\Models\Pekerjaan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupervisiController extends Controller
{
    public function index() {
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
            ->with('title','Supervisi')
            ->with('content', 'pages/supervisi/supervisi_view')
            ->with('after_page', 'pages/supervisi/supervisi_after_page')
            ->with('name',$user->name)
            ->with('notification_koordinator', $new_pekerjaan->count())
            ->with('notification_pekerjaan_saya', $new_pekerjaan_saya->count())
            ->with('notification_assessment', $new_assessment->count())
            ->with('notification_supervisi', $new_supervisi->count());
    }

    public function ajax_list(Request $request) {
        $user = auth()->user();
        $search = $request->input('search.value');
		$order = $request->input('order');

		$query = Pekerjaan::where('pekerjaan', 'like', '%'.$search.'%')
                    ->join('users', 'pekerjaan.koordinator_id', '=', 'users.id')
                    ->join('pekerjaan_detail', 'pekerjaan.id', '=', 'pekerjaan_detail.pekerjaan_id')
                    ->selectRaw('pekerjaan.*, users.id as koordinator_id, users.name as koordinator_name')
                    ->where('pekerjaan.koordinator_id', $user->id)
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

        $new_supervisi = Pekerjaan::where('koordinator_id', $user->id)
                            ->where('notification_supervisi', 1)
                            ->pluck('id')
                            ->toArray();	
        foreach ($pekerjaan as $r) {
            $petugas = '';
            if($r->status_pekerjaan == 'finish'){
                $btn_color = 'btn-success';
            } else if($r->status_pekerjaan == 'on progress') {
                $tugas_active = DB::table('pekerjaan_detail')
                    ->where('pekerjaan_detail.pekerjaan_id', $r->id)
                    ->whereIn('pekerjaan_detail.status_tugas', ['submit', 'stuck'])
                    ->join('pekerjaan', 'pekerjaan.id', '=', 'pekerjaan_detail.pekerjaan_id')
                    ->orderBy('urutan', 'asc')
                    ->select('pekerjaan_detail.*', 'pekerjaan.koordinator_id as koordinator_id')
                    ->first();
                if ($tugas_active) {
                    if ($user->id == $tugas_active->koordinator_id && ($tugas_active->status_tugas == 'submit' || $tugas_active->status_tugas == 'stuck')) {
                        $btn_color = 'btn-danger';
                    } else {
                        $btn_color = 'btn-warning';
                    }
                } else {
                    $btn_color = 'btn-warning';
                }
                $detail_pekerjaan = DB::table('pekerjaan_detail')
                ->where('pekerjaan_detail.pekerjaan_id', $r->id)
                ->where('pekerjaan_detail.status_tugas', 'on going')
                ->join('users', 'pekerjaan_detail.petugas_id', '=', 'users.id')
                ->orderBy('urutan', 'asc')
                ->first();
    
                $petugas = $detail_pekerjaan->name ?? '';
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
            $now = Carbon::now()->locale('id'); // Get the current date and time
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
            $html = '<div class="btn-group btn-group-solid">
                            <a href="'.url("/realisasi-pekerjaan/detail/".$r->id).'" class="btn btn-sm btn-primary">
                            Detail</a>
                        </div>';


            $no++;
            $row = array();
            $row[] = $no;
            $row[] = in_array($r->id, $new_supervisi) ? $r->pekerjaan . ' <span class="badge bg-danger" style="font-size: .5em; margin-left:2px">NEW</span>' : $r->pekerjaan;
            $row[] = date('d-m-Y', strtotime($r->start_date));
            $row[] = $r->end_date ? date('d-m-Y', strtotime($r->end_date)) : '';
            $row[] = $r->koordinator_name;
            $row[] = $petugas ? $petugas : '-';
            $row[] = '<div class="progress">
                        <div class="progress-bar progress-bar-striped '.($work_progress >= 100 ? 'bg-success' : 'bg-primary').'" role="progressbar" style="width: '.$work_progress.'%;" aria-valuenow="'.$work_progress.'" aria-valuemin="0" aria-valuemax="100">'.$work_progress.'%</div>
                    </div>';
            $row[] = $time_used;
            $row[] = $time_remaining;
            $row[] = $html_alur;
            $row[] = '<a href="javascript:void(0)" class="btn btn-sm btn-primary" style="margin-right:0px !important" title="Lihat Alur" onclick="view_dialog(\'' . $r->id . '\')"> <i class="fas fa-comment"></i>
            </a>';
            $row[] = '<button class="btn btn-sm '.$btn_color.'">'.$r->status_pekerjaan.'</button>';
            $row[] = $html;

            $data[] = $row;
        }    

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $recordsCount,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data,
        );

        Pekerjaan::where('koordinator_id', $user->id)
				->where('notification_supervisi', 1)
				->update(['notification_supervisi' => 0]);

        echo json_encode($output);
    }
}
