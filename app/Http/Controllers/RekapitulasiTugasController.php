<?php

namespace App\Http\Controllers;

use App\Models\Pekerjaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RekapitulasiTugasController extends Controller
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
            ->with('title','Rekapitulasi Tugas')
            ->with('content', 'pages/rekapitulasi_tugas/rekapitulasi_tugas_view')
            ->with('after_page', 'pages/rekapitulasi_tugas/rekapitulasi_tugas_after_page')
            ->with('notification_koordinator', $new_pekerjaan->count())
            ->with('notification_pekerjaan_saya', $new_pekerjaan_saya->count())
            ->with('notification_assessment', $new_assessment->count())
            ->with('notification_supervisi', $new_supervisi->count())
            ->with('name',$user->name);
    }

    public function ajax_list(Request $request) {
        $search = $request->input('search.value');
		$order = $request->input('order');
        $filter_start_date = $request->filter_start_date;
        $filter_end_date = $request->filter_end_date;
        $user_id = $request->user_id;

		$query = DB::table('pekerjaan_detail')
            ->join('pekerjaan', 'pekerjaan_detail.pekerjaan_id', '=', 'pekerjaan.id')
            ->join('users', 'pekerjaan_detail.petugas_id', '=', 'users.id')
            ->select('pekerjaan', 'nama_tugas', 'pekerjaan_detail.start_date', 'pekerjaan_detail.end_date', 'name', 'nilai' );

        if (!empty($user_id)) {
            $query->where('pekerjaan_detail.petugas_id', '=', $user_id);
        }

        if (!empty($filter_start_date)) {
            $query->whereBetween('pekerjaan_detail.start_date', [$filter_start_date, $filter_end_date]);
        }
                
		$recordsCount = $query->get()->count();
		$columns = ['pekerjaan', 'nama_tugas', 'pekerjaan_detail.start_date', 'pekerjaan_detail.end_date', 'name', 'nilai'];

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
        $recordsFiltered = $query->get()->count();

		if (!empty($order)) {
			$orderColumn = $columns[$order[0]['column']-1];
			$orderDirection = $order[0]['dir'];
			$query->orderBy($orderColumn, $orderDirection);
		} else {
			$query->orderByDesc('pekerjaan_detail.start_date');
		}
		
		if ($request->length != -1) {
			$query->skip($request->start)->take($request->length);
		}

		$users = $query->get();
       
        
        $data = array();
        $no = $_POST['start'];
        foreach ($users as $r) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $r->pekerjaan;
            $row[] = $r->nama_tugas;
            $row[] = $r->start_date;
            $row[] = $r->end_date;
            $row[] = $r->name;
            $row[] = $r->nilai;
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
}
