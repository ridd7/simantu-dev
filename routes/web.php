<?php

use App\Http\Controllers\AssessmentTugasController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ListPekerjaanController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PekerjaanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RankingUserController;
use App\Http\Controllers\RealisasiPekerjaanController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\RekapitulasiOutputController;
use App\Http\Controllers\RekapitulasiTugasController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\SupervisiController;
use App\Http\Controllers\TugasController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('pages.auth.login');
})->middleware('guest');

Route::get('/login', [LoginController::class, 'index'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'authenticate']);
Route::get('/logout', [LoginController::class, 'logout']);

Route::get('/register', [RegisterController::class, 'index'])->name('register')->middleware('guest');
Route::post('/register', [RegisterController::class, 'store']);

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth']);

Route::get('/pekerjaan', [PekerjaanController::class, 'index'])->middleware('auth');
Route::post('/pekerjaan/ajax-list', [PekerjaanController::class, 'ajax_list'])->middleware('auth');
Route::post('/pekerjaan/ajax-add', [PekerjaanController::class, 'ajax_add'])->middleware('auth');
Route::get('/pekerjaan/ajax-edit/{pekerjaan:id}', [PekerjaanController::class, 'ajax_edit'])->middleware('auth');
Route::put('/pekerjaan/ajax-update', [PekerjaanController::class, 'ajax_update']);
Route::delete('/pekerjaan/ajax-delete/{pekerjaan:id}', [PekerjaanController::class, 'ajax_delete'])->middleware('auth');
Route::put('/pekerjaan/ajax-add-proses-pekerjaan', [PekerjaanController::class, 'ajax_add_proses_pekerjaan'])->middleware('auth');
Route::get('/pekerjaan/get-pesan-penugasan/{id}', [PekerjaanController::class, 'get_pesan_penugasan'])->middleware('auth');

Route::get('/users', [UsersController::class, 'index'])->middleware('auth');
Route::post('/users/ajax-list', [UsersController::class, 'ajax_list'])->middleware('auth');
Route::post('/users/ajax-add', [UsersController::class, 'ajax_add'])->middleware('auth');
Route::get('/users/ajax-edit/{user:id}', [UsersController::class, 'ajax_edit'])->middleware('auth');
Route::put('/users/ajax-update', [UsersController::class, 'ajax_update']);
Route::delete('/users/ajax-delete/{user:id}', [UsersController::class, 'ajax_delete'])->middleware('auth');
Route::put('/users/change-password', [UsersController::class, 'ajax_update_password'])->middleware('auth');

Route::get('/satuan', [SatuanController::class, 'index'])->middleware('auth');
Route::post('/satuan/ajax-list', [SatuanController::class, 'ajax_list'])->middleware('auth');
Route::post('/satuan/ajax-add', [SatuanController::class, 'ajax_add'])->middleware('auth');
Route::get('/satuan/ajax-edit/{satuan:id}', [SatuanController::class, 'ajax_edit'])->middleware('auth');
Route::put('/satuan/ajax-update', [SatuanController::class, 'ajax_update']);
Route::delete('/satuan/ajax-delete/{satuan:id}', [SatuanController::class, 'ajax_delete'])->middleware('auth');

Route::get('/selectKoordinator', [PekerjaanController::class, 'get_koordinator'])->name('pekerjaan.koordinator.index');
Route::get('/selectAlur', [PekerjaanController::class, 'get_alur'])->name('pekerjaan.alur.index');
Route::get('/selectPekerjaanDetail/{id}', [PekerjaanController::class, 'get_pekerjaan_detail_by_id'])->name('pekerjaan.detail.getbyid');
Route::get('/selectTugas/{id_alur}/{id_pekerjaan}', [PekerjaanController::class, 'get_tugas'])->name('pekerjaan.tugas.index');

Route::get('/selectPetugas', [PekerjaanController::class, 'get_petugas'])->name('pekerjaan.petugas.index');
Route::get('/selectAssessment', [PekerjaanController::class, 'get_assessment'])->name('pekerjaan.assessment.index');
Route::post('/pekerjaan/ajax-add-tugas', [PekerjaanController::class, 'ajax_add_tugas']);

Route::get('/rangkaian-tugas', [TugasController::class, 'index'])->middleware('auth');
Route::post('/alur-tugas/ajax-list', [TugasController::class, 'ajax_list'])->middleware('auth');
Route::post('/alur-tugas/ajax-add', [TugasController::class, 'ajax_add'])->middleware('auth');
Route::get('/alur-tugas/ajax-edit/{id}', [TugasController::class, 'ajax_edit'])->middleware('auth');
Route::put('/alur-tugas/ajax-update', [TugasController::class, 'ajax_update']);
Route::delete('/alur-tugas/ajax-delete/{id}', [TugasController::class, 'ajax_delete'])->middleware('auth');

Route::get('/tugas/ajax-edit/{id}', [TugasController::class, 'ajax_edit_tugas'])->middleware('auth');
Route::post('/tugas/ajax-update/', [TugasController::class, 'ajax_update_tugas'])->middleware('auth');

Route::get('/realisasi-pekerjaan', [RealisasiPekerjaanController::class, 'index'])->middleware(['auth', 'role:user']);
Route::post('/realisasi-pekerjaan/ajax-list', [RealisasiPekerjaanController::class, 'ajax_list'])->middleware(['auth', 'role:user']);
Route::get('/realisasi-pekerjaan/detail/{id}', [RealisasiPekerjaanController::class, 'detail'])->middleware('auth');
Route::post('/realisasi-pekerjaan/detail-ajax-list/{id}', [RealisasiPekerjaanController::class, 'detail_ajax_list'])->middleware('auth');
Route::post('/realisasi-pekerjaan/proses-ajax-list/{id}', [RealisasiPekerjaanController::class, 'proses_ajax_list'])->middleware('auth');
Route::get('/realisasi-pekerjaan/timeline-ajax/{id}', [RealisasiPekerjaanController::class, 'timeline_ajax'])->middleware('auth');
Route::post('/realisasi-pekerjaan/add-laporan/', [RealisasiPekerjaanController::class, 'add_laporan'])->middleware(['auth', 'role:user']);
Route::post('/realisasi-pekerjaan/feedback-laporan/', [RealisasiPekerjaanController::class, 'feedback_laporan'])->middleware(['auth', 'role:user']);
Route::get('/realisasi-pekerjaan/ajax-edit-progress/{id}', [RealisasiPekerjaanController::class, 'ajax_edit_progress'])->middleware(['auth', 'role:user']);
Route::post('/realisasi-pekerjaan/ajax-update-progress/', [RealisasiPekerjaanController::class, 'ajax_update_progress'])->middleware(['auth', 'role:user']);
Route::get('/selectPetugasByAssessment', [RealisasiPekerjaanController::class, 'get_petugas_by_assessment'])->middleware(['auth', 'role:user']);

Route::get('/supervisi', [SupervisiController::class, 'index'])->middleware('auth');
Route::post('/supervisi/ajax-list', [SupervisiController::class, 'ajax_list'])->middleware(['auth', 'role:user']);

Route::get('/assessment-tugas', [AssessmentTugasController::class, 'index'])->middleware('auth');
Route::post('/assessment-tugas/ajax-list', [AssessmentTugasController::class, 'ajax_list'])->middleware('auth');

Route::get('/list-pekerjaan', [ListPekerjaanController::class, 'index'])->middleware('auth');
Route::post('/list-pekerjaan/ajax-list', [ListPekerjaanController::class, 'ajax_list'])->middleware('auth');

Route::get('/profile', [ProfileController::class, 'index'])->middleware('auth');
Route::post('/profile/change-password', [ProfileController::class, 'change_password'])->middleware('auth');
Route::post('/profile/update-photo-profile', [ProfileController::class, 'update_photo_profile'])->middleware('auth');

Route::get('/ranking-user', [RankingUserController::class, 'index'])->middleware('auth');
Route::post('/ranking-user/ajax-list-ranking-koordinator', [RankingUserController::class, 'ajax_list_ranking_koordinator'])->middleware('auth');
Route::post('/ranking-user/ajax-list-ranking-petugas', [RankingUserController::class, 'ajax_list_ranking_petugas'])->middleware('auth');

Route::get('/rekapitulasi-tugas', [RekapitulasiTugasController::class, 'index'])->middleware('auth');
Route::post('/rekapitulasi-tugas/ajax-list', [RekapitulasiTugasController::class, 'ajax_list'])->middleware('auth');

Route::get('/rekapitulasi-output', [RekapitulasiOutputController::class, 'index'])->middleware('auth');
Route::post('/rekapitulasi-output/ajax-list', [RekapitulasiOutputController::class, 'ajax_list'])->middleware('auth');

Route::get('/test-asset', function () {
    return [
        'APP_URL' => config('app.url'),
        'ASSET_URL' => config('app.asset_url'),
        'asset()' => asset('assets/css/style.css'),
        'url()' => url('assets/css/style.css'),
        'public_path' => public_path('assets/css/style.css'),
        'file_exists' => file_exists(public_path('assets/css/style.css')),
    ];
});
