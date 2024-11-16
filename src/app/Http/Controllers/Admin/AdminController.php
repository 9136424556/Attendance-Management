<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\AdminLoginRequest;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Admin;
use App\Models\Work_request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;


class AdminController extends Controller
{
    //管理者ログイン画面
    public function adminlogin()
    {
        return view('admin.login');
    }
    //管理者ログイン
    public function login(AdminLoginRequest $request)
    {
        //ログインを試行
        if (Auth::guard('admin')->attempt($request->only('email', 'password'))) {
            return redirect()->route('admin.index');
        }

        return redirect('/admin/login')->withErrors(['email' => 'ログイン情報が正しくありません']);
    }
    //管理者ログアウト
    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }

    //勤怠一覧ページ
    public function index(Request $request)
    {
        $currentDate = $request->input('date',now()->format('Y-m-d'));
        $users = User::all();
        $attendances = Attendance::whereDate('work_date',$currentDate)
                                 ->orderBy('start_time')
                                 ->with('user','breakTimes') // リレーションをロード
                                 ->get();

        // 日付ごとに勤怠データをグループ化
         $attendancesByDate = [];

         // 各勤怠データに休憩時間の合計を追加
         foreach ($attendances as $attendance) {
            $totalBreakMinutes = $attendance->TotalBreaktime();
            $attendance->total_break_time = $this->formatBreakTime($totalBreakMinutes);

            // 勤務時間の合計を計算して追加
            $attendance->total_work_time = $attendance->calculateTotalWorkTime();

            // work_date をキーとして勤怠データをグループ化
            $workDate = Carbon::parse($attendance->work_date)->format('Y-m-d');
            $attendancesByDate[$workDate][] = $attendance;
        }

        return view('admin.index',compact('currentDate','attendances','attendancesByDate','users'));
    }

    public function formatBreakTime($totalMinutes)
    {
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return sprintf('%d:%02d', $hours, $minutes);
    }

    //スタッフ一覧ページ
    public function stafflist(Request $request)
    {
        $users = User::all();
       

        return view('admin.stafflist',compact('users'));
    }

    //スタッフ別勤怠一覧ページ
    public function staffdetail(Request $request,$id)
    {
        $currentDate = $request->input('date',now()->format('Y-m'));
        $user = User::find($id);
        $attendances = Attendance::where('user_id',$user->id)
                                 ->whereYear('work_date', substr($currentDate, 0, 4))
                                 ->whereMonth('work_date', substr($currentDate, 5, 2))
                                 ->orderBy('work_date')
                                 ->with('user','breakTimes') // リレーションをロード
                                 ->get();

        $workRequests = Work_request::where('user_id', $user->id)
                                    ->get()->keyBy('attendance_id');

        // 各勤怠データに休憩時間の合計を追加
        foreach ($attendances as $attendance) {
            $totalBreakMinutes = $attendance->TotalBreaktime();
            $attendance->total_break_time = $this->formatBreakTime($totalBreakMinutes);

            // 勤務時間の合計を計算して追加
            $attendance->total_work_time = $attendance->calculateTotalWorkTime();
        }

        return view('admin.staff',compact('currentDate','user','attendances','workRequests'));
    }

    //勤怠情報をエクスポート
    public function export(Request $request, $id)
    {
        $currentDate = $request->input('date', now()->format('Y-m'));

        $user = User::findOrFail($id);
        $attendances = Attendance::where('user_id', $user->id)
                                 ->whereYear('work_date', substr($currentDate, 0, 4))
                                 ->whereMonth('work_date', substr($currentDate, 5, 2))
                                 ->orderBy('work_date')
                                 ->with('user', 'breakTimes')
                                 ->get();
    
        // 各勤怠データに休憩時間の合計と勤務時間の合計を追加
        foreach ($attendances as $attendance) {
           $totalBreakMinutes = $attendance->TotalBreaktime();
           $attendance->total_break_time = $this->formatBreakTime($totalBreakMinutes);
           $attendance->total_work_time = $attendance->calculateTotalWorkTime();
        }

        //CSV用のデータ準備
        $csvData = [
            ['日付', '出勤', '退勤', '休憩', '合計'] // ヘッダー行
        ];

        foreach ($attendances as $attendance) {
            $csvDate[] = [
                $attendance->work_date,
                $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '',
                $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '',
                $attendance->total_break_time,
                $attendance->total_work_time,
            ];
        }

        //csvファイルを作成
        $filename = $user->name . "_attendance_" . $currentDate . ".csv";
        $handle = fopen('php://temp', 'r+');
        foreach ($csvDate as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);

        $contents = stream_get_contents($handle);
        fclose($handle);

        //csvファイルをダウンロード
        return response($contents, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
