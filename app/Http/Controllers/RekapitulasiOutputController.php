<?php

namespace App\Http\Controllers;

use App\Models\Pekerjaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RekapitulasiOutputController extends Controller
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
            ->with('title','Rekapitulasi Output')
            ->with('content', 'pages/rekapitulasi_output/rekapitulasi_output_view')
            ->with('after_page', 'pages/rekapitulasi_output/rekapitulasi_output_after_page')
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

        $query = Pekerjaan::leftJoin('satuan', 'pekerjaan.satuan_id', '=', 'satuan.id');

        if (!empty($filter_start_date)) {
            $query->whereBetween('pekerjaan.start_date', [$filter_start_date, $filter_end_date]);
        }
                
		$recordsCount = $query->get()->count();
		$columns = ['pekerjaan', 'jumlah_output', 'satuan', 'pekerjaan.start_date', 'pekerjaan.real_end_date', 'durasi_penyelesaian'];

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

        $clone_query = clone $query;
        $total_output = $clone_query->selectRaw('SUM(CASE WHEN status_pekerjaan = "finish" THEN jumlah_output ELSE 0 END) as total_output, satuan.satuan')
                        ->whereNotNull('satuan_id')
                        ->groupBy('satuan.satuan')
                        ->get();
        $recordsFiltered = $query->select(
            'pekerjaan.pekerjaan',
            'satuan.satuan',
            'pekerjaan.start_date',
            'pekerjaan.real_end_date',
            DB::raw('TIMESTAMPDIFF(DAY, pekerjaan.start_date, pekerjaan.real_end_date) AS durasi_penyelesaian'),
            DB::raw("CASE WHEN pekerjaan.status_pekerjaan = 'finish' THEN pekerjaan.jumlah_output ELSE NULL END AS jumlah_output")
        )->get()->count();

		if (!empty($order)) {
			$orderColumn = $columns[$order[0]['column']-1];
			$orderDirection = $order[0]['dir'];
			$query->orderBy($orderColumn, $orderDirection);
		} else {
			$query->orderByDesc('pekerjaan');
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
            $row[] = $r->jumlah_output;
            $row[] = $r->satuan;
            $row[] = $r->start_date;
            $row[] = $r->real_end_date;
            $row[] = $r->durasi_penyelesaian;
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $recordsCount,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data,
            "total_output" => $total_output
        );

        echo json_encode($output);
    }

}
