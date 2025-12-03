<?php

namespace App\Http\Controllers;

use App\Models\Pekerjaan;
use App\Models\Satuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SatuanController extends Controller
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
            ->with('title','Satuan')
            ->with('content', 'pages/satuan/satuan_view')
            ->with('after_page', 'pages/satuan/satuan_after_page')
            ->with('notification_koordinator', $new_pekerjaan->count())
            ->with('notification_pekerjaan_saya', $new_pekerjaan_saya->count())
            ->with('notification_assessment', $new_assessment->count())
            ->with('notification_supervisi', $new_supervisi->count())
            ->with('name',$user->name);
    }

    public function ajax_list(Request $request) {
        $user = auth()->user();
        $search = $request->input('search.value');
        $order = $request->input('order');

        $query = DB::table('satuan');
        $recordsCount = $query->count();
        $columns = ['satuan'];

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
            $query->orderBy('satuan', 'asc');
        }
        
        if ($request->length != -1) {
			$query->skip($request->start)->take($request->length);
        }

        $users = $query->get();
        

        $data = array();
        $no = $request->start;
        foreach ($users as $r) {
            $html = '<div class="btn-group btn-group-solid">
                              <a href="javascript:void(0)" class="btn btn-sm btn-primary" style="margin-right:0px !important" title="Edit"
                              onclick="edit_satuan(\'' . $r->id . '\')">
                              <i class="fas fa-edit"></i> </a>
                              <a href="javascript:void(0)" class="btn btn-sm btn-danger" style="margin-right:0px !important" title="Delete"
                              onclick="delete_satuan(\'' . $r->id . '\')">
                              <i class="fas fa-trash "></i> </a>
                          </div>';

            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $r->satuan;
            $row[] = $html;
            $data[] = $row;
        }

        $output = array(
            "draw" => $request->draw,
            "recordsTotal" => $recordsCount,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data,
        );

        echo json_encode($output);
    }

    public function ajax_add(Request $request) {
        $satuan = new Satuan();
        $satuan->satuan = $request->satuan;
        $satuan->save();
        return json_encode(array("status" => TRUE));
    }

    public function ajax_edit(Satuan $satuan) {
        $data['satuan'] = $satuan;
        echo json_encode($data);
    }

    public function ajax_update(Request $request) {
        $satuan = Satuan::find($request->id);
        $satuan->satuan = $request->satuan;
        $satuan->save();
        return response()->json(['status' => TRUE]);
    }

    public function ajax_delete(Satuan $satuan) {
        $satuan->delete();
        return json_encode(array("status" => TRUE));
    }
}
