<?php

namespace App\Http\Controllers;

use App\Models\Pekerjaan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
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
            ->with('title','Profil')
            ->with('content', 'pages/profile/profile_view')
            ->with('after_page', 'pages/profile/profile_after_page')
            ->with('name',$user->name)
            ->with('notification_koordinator', $new_pekerjaan->count())
            ->with('notification_pekerjaan_saya', $new_pekerjaan_saya->count())
            ->with('notification_assessment', $new_assessment->count())
            ->with('notification_supervisi', $new_supervisi->count())
            ->with('user',$user);
    }

    public function change_password(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:8',
            'confirm_new_password' => 'required|same:new_password',
        ]);

        $user = Auth::user();
        $oldPassword = $request->old_password;
        $newPassword = $request->new_password;

        // Cek apakah password lama sesuai
        if (Hash::check($oldPassword, $user->password)) {
            // Enkripsi password baru
            $hashedPassword = bcrypt($newPassword);

            // Update password baru
            $user->password = $hashedPassword;
            $user->save();

            return redirect()->back()->with('success', 'Password berhasil diubah.');
        }

        return redirect()->back()->with('error', 'Password lama tidak sesuai.');
    }   

    public function update_photo_profile(Request $request) {
        $user = auth()->user();
        $file_photo = $request->file('file_photo');
        if($file_photo) {
            if($user->photo_profile) {
                unlink('storage/'.$user->photo_profile);
            }
            $path = $file_photo->store('file/photo_profile');
            $user = User::find($user->id);
            $user->photo_profile = $path;
            $user->save();

            return redirect()->back()->with('success', 'Foto Profil berhasil diubah.');
        } 

        return redirect()->back()->with('error', 'Foto Profil gagal diubah.');    
    }
}