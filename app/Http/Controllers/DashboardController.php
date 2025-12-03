<?php

namespace App\Http\Controllers;

use App\Models\Pekerjaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
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
        $countPekerjaan['countPekerjaanAll'] = Pekerjaan::count();
        $countPekerjaan['countPekerjaanFinish'] = Pekerjaan::where('status_pekerjaan', 'finish')->count();
        $countPekerjaan['countPekerjaanOnProgress'] = Pekerjaan::where('status_pekerjaan', 'on progress')->count();
        $countTugas['countTugasAll'] = DB::table('pekerjaan_detail')->count();
        $countTugas['countTugasOnGoing'] = DB::table('pekerjaan_detail')->where('status_tugas', 'on going')->orWhere('status_tugas', 'stuck')->count();
        $countTugas['countTugasApproved'] = DB::table('pekerjaan_detail')->where('status_tugas', 'approved')->count();

        return view('master_page')           
             ->with('title','Dashboard')
             ->with('content', 'pages/dashboard/dashboard_view')
             ->with('countPekerjaan', $countPekerjaan)
             ->with('countTugas', $countTugas)
             ->with('after_page', 'pages/dashboard/dashboard_after_page')
             ->with('name',$user->name)
             ->with('notification_koordinator', $new_pekerjaan->count())
             ->with('notification_pekerjaan_saya', $new_pekerjaan_saya->count())
             ->with('notification_assessment', $new_assessment->count())
             ->with('notification_supervisi', $new_supervisi->count());
    }
}
