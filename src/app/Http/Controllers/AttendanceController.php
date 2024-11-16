<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Break_time;
use App\Models\Work_request;
use App\Models\User;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    //勤怠ページ(ホーム画面)
    public function index()
    {
        // 現在の日付を取得
       $today = Carbon::today()->format('Y-m-d'); 
      
       // 本日の日付に基づいて出勤記録を取得
       $attendance = Attendance::where('user_id', auth()->id())->whereDate('work_date', $today)->first();

         // デフォルトの状態は「勤務外」
       $status = 'beforeClockIn';
       $workStatus = '勤務外';

       if ($attendance) {
        // 出勤記録がある場合、休憩や退勤の状態を確認
         $status = 'clockedIn';
         $workStatus = '出勤中';

        // 退勤済みのチェック
         if ($attendance->end_time) {
          $status = 'afterend';
          $workStatus = '退勤済み';
        } else  {
        // 休憩中のチェック
         $breakTime = Break_time::where('attendance_id', $attendance->id)
           ->whereNull('break_end_time')
           ->latest('break_start_time')
           ->first();

           if($breakTime) {
             $status = 'onBreak';
             $workStatus = '休憩中';
           }
        }
      }
      // 出勤していない場合
      return view('index', [
        'attendanceStatus' => $status,
        'workStatus' => $workStatus,
        'currentDate' => $today
      ]);
    }

    public function startTime(Request $request)
    {
        $attendance = Attendance::firstOrCreate(
            [
              'user_id' => auth()->id(), 
              'work_date' => Carbon::today(),
            ],
            [
              'start_time' => Carbon::now(), // 新規作成時のみ現在時刻を設定
            ]
       );
        return redirect()->back()->with('status', 'clockedIn')->with('workStatus', '出勤中');
    }

    public function endTime(Request $request)
    {
        $attendance = Attendance::where('user_id', auth()->id())
        ->whereDate('work_date',  Carbon::today())->first();
        

        if ($attendance) {
           $attendance->end_time = Carbon::now();
           $attendance->save();
        }
        return redirect()->back()->with('status','afterend')->with('workStatus', '退勤済み');
    }

    public function breakstart(Request $request)
    {
         // attendance_idを指定して、該当する休憩レコードを取得
        $attendance = Attendance::where('user_id', auth()->id())
        ->whereDate('work_date', Carbon::today())
        ->first();

        if (!$attendance) {
            return redirect()->back()->with('error', '出勤記録がありません。');
        } 
        
            // 休憩記録が存在しない場合は新たに作成
         $breakTime = new Break_time();
         $breakTime->attendance_id = $attendance->id; // 休憩レコードに出勤IDを関連付け
         $breakTime->break_start_time = now(); // 現在時刻を設定
         $breakTime->save();
         
        return redirect()->back()->with('status', 'onBreak')->with('workStatus','休憩中');
    }

    public function breakend(Request $request)
    {
        $attendance = Attendance::where('user_id', auth()->id())
        ->whereDate('work_date',Carbon::today())
        ->first();

        if ($attendance) {
            // 開始時間が設定され、終了時間がない最新の休憩レコードを更新
            $breakTime = Break_time::where('attendance_id', $attendance->id)
            ->whereNull('break_end_time')
            ->latest('break_start_time')
            ->first();

            if ($breakTime) {
                $breakTime->break_end_time = Carbon::now();
                $breakTime->save();
            }
        }

        return redirect()->back()->with('status', 'clockedIn')->with('workStatus','出勤中');
    }
    //勤怠一覧ページ
    public function list(Request $request)
    {
        $currentDate = $request->input('date',now()->format('Y-m'));
        $user = User::find(Auth::id());
        $attendances = Attendance::where('user_id',$user->id)
                                 ->whereYear('work_date', substr($currentDate, 0, 4))
                                 ->whereMonth('work_date', substr($currentDate, 5, 2))
                                 ->orderBy('work_date')
                                 ->with('breakTimes') // リレーションをロード
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

        return view('list',compact('attendances','currentDate','workRequests'));
    }
    public function formatBreakTime($totalMinutes)
    {
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return sprintf('%d:%02d', $hours, $minutes);
    }

}
