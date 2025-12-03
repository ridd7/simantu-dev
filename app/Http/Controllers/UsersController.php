<?php

namespace App\Http\Controllers;

use App\Models\Pekerjaan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
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
            ->with('title','Users')
            ->with('content', 'pages/users/users_view')
            ->with('after_page', 'pages/users/users_after_page')
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

        $query = DB::table('users')->where('level_user', 'user');
        $recordsCount = $query->count();
        $columns = ['name', 'username', 'pangkat', 'jabatan'];

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
            $query->orderBy('name', 'asc');
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
                              onclick="edit_user(\'' . $r->id . '\')">
                              <i class="fas fa-edit"></i> </a>
                              <a href="javascript:void(0)" class="btn btn-sm btn-info" style="margin-right:0px !important" title="Ubah Password"
                              onclick="change_password(\'' . $r->id . '\')">
                              <i class="fas fa-key"></i> </a>
                              <a href="javascript:void(0)" class="btn btn-sm btn-danger" style="margin-right:0px !important" title="Delete"
                              onclick="delete_user(\'' . $r->id . '\')">
                              <i class="fas fa-trash "></i> </a>
                          </div>';

            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $r->nip;
            $row[] = $r->name;
            $row[] = $r->username; 
            $row[] = $r->pangkat;
            $row[] = $r->jabatan;
            $row[] = $r->koordinator_akses;
            $row[] = $r->manager_akses;
            $row[] = $r->level;
            if($user->level_user == 'admin') {
                $row[] = $html;
            }
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

    public function ajax_add(Request $request)
    {
        $this->_validate($request);
        $file_photo = $request->file('file_photo');
        $user = new User;
        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->nip = $request->nip;
        $user->pangkat = $request->pangkat;
        $user->jabatan = $request->jabatan;
        $user->koordinator_akses = $request->koordinator_akses;
        $user->manager_akses = $request->manager_akses;
        $user->level = $request->level;
        if($file_photo) {
            $path = $file_photo->store('file/photo_profile');
            $user->photo_profile = $path;
        }
        $user->level_user = 'user';
        $user->password = bcrypt($request->password);
        $user->save();
        return json_encode(array("status" => TRUE));
    }

    public function ajax_edit(User $user) {
        $data['user'] = $user;
        echo json_encode($data);
    }

    public function ajax_update(Request $request)
	{
        $user = User::find($request->id);
        $file_photo = $request->file('file_photo');
        if($file_photo) {
            if($user->photo_profile) {
                unlink('storage/'.$user->photo_profile);
            }
            
            $path = $file_photo->store('file/photo_profile');
            $user->photo_profile = $path;
        } 
        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->nip = $request->nip;
        $user->pangkat = $request->pangkat;
        $user->jabatan = $request->jabatan;
        $user->koordinator_akses = $request->koordinator_akses;
        $user->manager_akses = $request->manager_akses;
        $user->level = $request->level;
        
        $user->save();
        
      	return response()->json(['status' => TRUE]);
	}

    public function ajax_delete(User $user)
	{
			$user->delete();
            if($user->photo_profile) {
                unlink('storage/'.$user->photo_profile);
            }
			return json_encode(array("status" => TRUE));
	}

    public function ajax_update_password(Request $request)
    {
        $this->_validate_password($request);
        $user = User::find($request->id_user);
        $user->password = bcrypt($request->password);
        $user->save();
        return json_encode(array("status" => TRUE));
    }

    private function _validate_password($request)
    {
        $data = array();
        $data['error_string'] = array();
        $data['inputerror'] = array();
        $data['status'] = TRUE;
        if ($request->password == '') {
            $data['inputerror'][] = 'password';
            $data['error_string'][] = 'Harus Diisi.';
            $data['status'] = FALSE;
        }

        if ($request->confirm_password == '') {
            $data['inputerror'][] = 'confirm_password';
            $data['error_string'][] = 'Harus Diisi.';
            $data['status'] = FALSE;
        }

        if ($request->password != $request->confirm_password) {
            $data['inputerror'][] = 'confirm_password';
            $data['error_string'][] = 'Konfirmasi password tidak sesuai.';
            $data['status'] = FALSE;
        }

        if ($data['status'] === FALSE) {
            echo json_encode($data);
            exit();
        }
    }

    private function _validate($request)
    {
        $data = array();
        $data['error_string'] = array();
        $data['inputerror'] = array();
        $data['status'] = TRUE;

        if ($request->name == '') {
            $data['inputerror'][] = 'name';
            $data['error_string'][] = 'Harus Diisi.';
            $data['status'] = FALSE;
        }

        if ($request->username == '') {
            $data['inputerror'][] = 'username';
            $data['error_string'][] = 'Harus Diisi.';
            $data['status'] = FALSE;
        }
        
        if ($request->email == '') {
            $data['inputerror'][] = 'email';
            $data['error_string'][] = 'Harus Diisi.';
            $data['status'] = FALSE;
        }

        if ($request->pangkat == '') {
            $data['inputerror'][] = 'pangkat';
            $data['error_string'][] = 'Harus Diisi.';
            $data['status'] = FALSE;
        }

        if ($request->jabatan == '') {
            $data['inputerror'][] = 'jabatan';
            $data['error_string'][] = 'Harus Diisi.';
            $data['status'] = FALSE;
        }

        if ($request->password == '') {
            $data['inputerror'][] = 'password';
            $data['error_string'][] = 'Harus Diisi.';
            $data['status'] = FALSE;
        }

        if ($request->confirm_password == '') {
            $data['inputerror'][] = 'confirm_password';
            $data['error_string'][] = 'Harus Diisi.';
            $data['status'] = FALSE;
        }

        if ($request->level == '') {
            $data['inputerror'][] = 'level';
            $data['error_string'][] = 'Harus Diisi.';
            $data['status'] = FALSE;
        }

        if ($request->password != $request->confirm_password) {
            $data['inputerror'][] = 'confirm_password';
            $data['error_string'][] = 'Konfirmasi password tidak sesuai.';
            $data['status'] = FALSE;
        }

        if ($data['status'] === FALSE) {
            echo json_encode($data);
            exit();
        }
    }

}
