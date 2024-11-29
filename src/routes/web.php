<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminrequestController;

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
    return view('welcome');
});

Route::middleware('auth', 'verified')->group(function () {
  //勤怠・打刻ページ
  Route::get('/attendance',[AttendanceController::class,'index']);
  //勤怠開始
  Route::post('/attendance/start', [AttendanceController::class, 'startTime'])->name('attendance.start');
  //退勤処理
  Route::post('/attendance/end', [AttendanceController::class, 'endTime'])->name('attendance.end');
  //休憩開始
  Route::post('/attendance/breakstart', [AttendanceController::class, 'breakstart'])->name('attendance.breakstart');
  //休憩終了
  Route::post('/attendance/breakend', [AttendanceController::class, 'breakend'])->name('attendance.breakend');
  
  //勤怠一覧ページ
  Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
  //勤怠詳細ページ
  Route::get('/attendance/{id}', [RequestController::class, 'show'])->name('attendance.show');
  //勤怠情報の修正を申請
  Route::post('/attendance/{id}/request', [RequestController::class, 'attendanceRequest'])->name('attendance.request');
  //申請一覧ページ
  Route::get('/stamp_correction_request/list', [RequestController::class,'requestlist'])->name('stamp_correction_request.list.user');
  //申請詳細ページ
  Route::get('/stamp_correction_request/{id}', [RequestController::class, 'requested'])->name('requested.show');
  //勤怠情報の修正を申請(承認済みのものを再度修正申請する場合)
  Route::post('/stamp_correction_request/{id}/request',[RequestController::class, 'requestedreturn'])->name('requested.return');
});



Route::middleware('guest:admin')->group(function () {
  //管理者ログインページ
  Route::get('/admin/login', [AdminController::class, 'adminlogin'])->name('admin.login');
  //管理者ログイン処理
  Route::post('/admin/login', [AdminController::class, 'login']);
});
//管理者ページ
Route::middleware('auth:admin')->group(function () {
   //管理者ログアウト
  Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');
  //勤怠一覧画面（管理者）
  Route::get('/admin/attendance/list', [AdminController::class, 'index'])->name('admin.index');


  //勤怠詳細画面（管理者）
  Route::get('/admin/attendance/{id}', [AdminrequestController::class, 'show'])->name('admin.show');
  //勤怠情報修正処理
  Route::post('/admin/attendance/{id}/update',[AdminrequestController::class, 'update'])->name('admin.attendance.update');

  //スタッフ一覧ページ
  Route::get('/admin/staff/list',[AdminController::class, 'stafflist'])->name('admin.stafflist');
  //スタッフ別勤怠一覧ページ
  Route::get('/admin/attendance/staff/{id}',[AdminController::class, 'staffdetail'])->name('admin.staffdetail');
  //エクスポート
  Route::get('/admin/attendance/staff/{id}/export',[AdminController::class, 'export'])->name('export');

  //申請一覧ページ
  Route::get('/admin/stamp_correction_request/list', [RequestController::class,'requestlist'])->name('stamp_correction_request.list.admin');
  //申請承認ページ
  Route::get('/stamp_correction_request/approve/{attendance_correct_request}', [AdminrequestController::class, 'approve'])->name('approve');
  //申請承認処理
  Route::post('/stamp_correction_request/approve/{attendance_correct_request}/approve', [AdminrequestController::class, 'approveRequest'])->name('approve.request');
});


require __DIR__.'/auth.php';