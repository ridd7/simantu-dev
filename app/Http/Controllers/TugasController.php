<?php

namespace App\Http\Controllers;

use App\Models\Pekerjaan;
use App\Models\Satuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TugasController extends Controller
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
        $satuan = Satuan::get();
        return view('master_page')
            ->with('title','Template Rangkaian Tugas')
            ->with('satuan', $satuan)
            ->with('content', 'pages/tugas/tugas_view')
            ->with('after_page', 'pages/tugas/tugas_after_page')
            ->with('notification_koordinator', $new_pekerjaan->count())
            ->with('notification_pekerjaan_saya', $new_pekerjaan_saya->count())
            ->with('notification_assessment', $new_assessment->count())
            ->with('notification_supervisi', $new_supervisi->count())
            ->with('name',$user->name);
    }

    public function ajax_list(Request $request) {
        $search = $request->input('search.value');
        $order = $request->input('order');

        $query = DB::table('alur')->leftJoin('satuan', 'alur.satuan_id', '=', 'satuan.id')
                                    ->select('alur.*', 'satuan.satuan');
        $recordsCount = $query->count();
        $columns = ['alur', 'kategori', 3 => 'sasaran', 4 => 'indikator', 5 => 'jumlah_output', 6 => 'satuan'];

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
            $query->orderBy('id', 'asc');
        }
        if ($request->length != -1) {
			$query->skip($request->start)->take($request->length);
        }

        $alur_tugas = $query->get();

        $data = array();
        $no = $request->start;
        foreach ($alur_tugas as $r) {
              $tugas = DB::table('alur_tugas')
                        ->where('alur_id', $r->id)->get();
              $html_tugas = '';
              foreach ($tugas as $row) {
                $html_tugas .= '- ' .  $row->nama_tugas . '<br>';
              }
              $html = '<div class="btn-group btn-group-solid">
                              <a href="javascript:void(0)" class="btn btn-sm btn-primary" style="margin-right:0px !important" title="Edit"
                              onclick="edit_alur_tugas(\'' . $r->id . '\')">
                              <i class="fas fa-edit"></i> </a>
                              <a href="javascript:void(0)" class="btn btn-sm btn-danger" style="margin-right:0px !important" title="Delete"
                              onclick="delete_alur_tugas(\'' . $r->id . '\')">
                              <i class="fas fa-trash "></i> </a>
                              <a href="javascript:void(0)" class="btn btn-sm btn-warning" style="margin-right:0px !important" title="Tambah Tugas"
							  onclick="edit_tugas(\'' . $r->id . '\')">
							  <i class="fas fa-plus "></i> </a>
                          </div>';

              $no++;
              $row = array();
              $row[] = $no;
              $row[] = $r->alur;
              $row[] = $r->kategori;
              $row[] = $html_tugas;
              $row[] = $r->sasaran;
              $row[] = $r->indikator;
              $row[] = $r->jumlah_output;
              $satuan = Satuan::find($r->satuan_id);
              $row[] = $satuan ? $satuan->satuan : '';
              $row[] = $html;

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

    public function ajax_add(Request $request)
    {
			DB::table('alur')->insert(
                [
                    'alur' => $request->alur,
                    'kategori' => $request->kategori,
                    'sasaran' => $request->sasaran,
                    'indikator' => $request->indikator,
                    'jumlah_output' => $request->jumlah_output,
                    'satuan_id' => $request->satuan
                ]
            );
			return json_encode(array("status" => TRUE));
    }

	 public function ajax_edit($id)
    {
			$data['alur_tugas'] = DB::table('alur')
                            ->where('id', $id)->first();
			echo json_encode($data);
    }   

    public function ajax_update(Request $request)
	 {
        DB::table('alur')
            ->where('id', $request->id)
            ->update(
            [
                'alur' => $request->alur,
                'kategori' => $request->kategori,
                'sasaran' => $request->sasaran,
                'indikator' => $request->indikator,
                'jumlah_output' => $request->jumlah_output,
                'satuan_id' => $request->satuan
            ]
        );

      	return response()->json(['status' => TRUE]);
	 }

    public function ajax_delete($id)
	{
            DB::table('alur')
                ->where('id', $id)
                ->delete();
			return json_encode(array("status" => TRUE));
	}

     public function ajax_update_tugas(Request $request)
     {
        $id = $request->id;
        $tugas = $request->tugas;
        $urutan = $request->urutan;
        $durasi_baku = $request->durasi_baku;
        $output = $request->output;
        $lama_penyelesaian = $request->lama_penyelesaian;
        $tugas = array_combine(range(1, count($tugas)), array_values($tugas));
        DB::table('alur_tugas')
                    ->where('alur_id', $id)
                    ->whereNotIn('urutan', array_keys($tugas))
                    ->delete();
        if(!empty(array_filter($tugas))) {
            foreach ($tugas as $key => $value) {
                DB::table('alur_tugas')->updateOrInsert(
                    [
                        'alur_id'         =>    $id,
                        'urutan'          =>    $key,
                    ],
                    [
                        'nama_tugas'      =>    $value, 
                        'lama_penyelesaian' => $lama_penyelesaian[$key-1],
                        'urutan' => $urutan[$key-1],
                        'durasi_baku' => $durasi_baku[$key-1],
                        'output' => $output[$key-1],
                    ]
               );
            }
        } else {
            DB::table('alur_tugas')
                    ->where('alur_id', $id)
                    ->delete();
        }            

        return json_encode(array("status" => TRUE));
     }

     public function ajax_edit_tugas($id){
        $data['tugas'] = DB::table('alur_tugas')
                    ->where('alur_id', $id)
                    ->get();

        echo json_encode($data);
     }


}
