<?php

namespace App\Http\Controllers;

use App\Models\Pekerjaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RankingUserController extends Controller
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
            ->with('title','Ranking User')
            ->with('content', 'pages/ranking_user/ranking_user_view')
            ->with('after_page', 'pages/ranking_user/ranking_user_after_page')
            ->with('notification_koordinator', $new_pekerjaan->count())
            ->with('notification_pekerjaan_saya', $new_pekerjaan_saya->count())
            ->with('notification_assessment', $new_assessment->count())
            ->with('notification_supervisi', $new_supervisi->count())
            ->with('name',$user->name);
    }

    public function ajax_list_ranking_koordinator(Request $request) {
        $search = $request->input('search.value');
		$order = $request->input('order');
        $filter_start_date = $request->filter_start_date;
        $filter_end_date = $request->filter_end_date;

		$query = DB::table('users')
        ->leftJoin('pekerjaan', function ($join) use ($filter_start_date, $filter_end_date) {
            $join->on('users.id', '=', 'pekerjaan.koordinator_id')
                ->where(function ($query) {
                    $query->where('pekerjaan.status_pekerjaan', 'finish')
                        ->orWhereNull('pekerjaan.status_pekerjaan');
                });

            if (!empty($filter_start_date)) {
                $join->whereBetween('pekerjaan.start_date', [$filter_start_date, $filter_end_date]);
            }
        })
        ->select(
            'users.id',
            'users.name',
            DB::raw('COUNT(CASE WHEN pekerjaan.target = "in" THEN 1 END) as count_in_time'),
            DB::raw('COUNT(CASE WHEN pekerjaan.target = "on" THEN 1 END) as count_on_time'),
            DB::raw('COUNT(CASE WHEN pekerjaan.target = "off" THEN 1 END) as count_off_time'),
            DB::raw('COUNT(pekerjaan.id) as count_pekerjaan')
        )
        ->where('users.name', 'like', '%' . $search . '%')
        ->where('users.level_user', 'user')
        ->groupBy('users.id', 'users.name');
		$recordsCount = $query->get()->count();
		$columns = ['users.name', 'count_pekerjaan', 'count_in_time', 'count_on_time', 'count_off_time'];

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
			$query->orderByDesc('count_in_time');
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
            $row[] = $r->name;
            $row[] = $r->count_pekerjaan;
            $row[] = $r->count_in_time;
            $row[] = $r->count_on_time;
            $row[] = $r->count_off_time;
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

    public function ajax_list_ranking_petugas(Request $request) {
        $search = $request->input('search.value');
		$order = $request->input('order');
        $filter_start_date = $request->filter_start_date;
        $filter_end_date = $request->filter_end_date;

		$query = DB::table('users')
        ->leftJoin('pekerjaan_detail', function ($join) use ($filter_start_date, $filter_end_date) {
            $join->on('users.id', '=', 'pekerjaan_detail.petugas_id')
                ->where(function ($query) {
                    $query->where('pekerjaan_detail.status_tugas', 'approved')
                        ->orWhereNull('pekerjaan_detail.status_tugas');
                });

            if (!empty($filter_start_date)) {
                $join->whereBetween('pekerjaan_detail.end_date', [$filter_start_date, $filter_end_date]);
            }
        })
        ->select(
            'users.id',
            'users.name',
            DB::raw('COUNT(CASE WHEN pekerjaan_detail.target = "in" THEN 1 END) as count_in_time'),
            DB::raw('COUNT(CASE WHEN pekerjaan_detail.target = "on" THEN 1 END) as count_on_time'),
            DB::raw('COUNT(CASE WHEN pekerjaan_detail.target = "off" THEN 1 END) as count_off_time'),
            DB::raw('COUNT(pekerjaan_detail.id) as count_tugas'),
            DB::raw('AVG(pekerjaan_detail.nilai) as average_nilai')
        )
        ->where('users.name', 'like', '%' . $search . '%')
        ->where('users.level_user', 'user')
        ->groupBy('users.id', 'users.name');
		$recordsCount = $query->get()->count();
		$columns = ['users.name', 'count_tugas', 'average_nilai', 'count_in_time', 'count_on_time', 'count_off_time'];

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
			$query->orderByDesc('count_in_time');
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
            $row[] = $r->name;
            $row[] = $r->count_tugas;
            $row[] = $r->average_nilai;
            $row[] = $r->count_in_time;
            $row[] = $r->count_on_time;
            $row[] = $r->count_off_time;
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
