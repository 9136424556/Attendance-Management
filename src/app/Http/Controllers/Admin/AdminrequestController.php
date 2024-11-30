<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\AdminCorrectRequest;
use App\Models\Work_request;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Break_time;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminrequestController extends Controller
{
    public function show($id)
    {
        // Attendance ID に基づいて勤怠データを取得
        $attendance = Attendance::with('user')->findOrFail($id);
        // 勤怠データに関連するユーザー情報を取得
        $user = $attendance->user;

        // 勤怠データに関連する Work_request を取得
        $workRequest = Work_request::where('attendance_id', $attendance->id)
                                   ->where('user_id', $user->id)                 
                                   ->first();

         // 承認済みの勤怠変更がある場合に反映
        if ($workRequest && $workRequest->status === '承認済み') {
           // 承認後の出勤時刻が存在する場合に反映
           if ($workRequest->start_time) {
            $attendance->start_time = $workRequest->start_time;
           }

           // 承認後の退勤時刻が存在する場合に反映
           if ($workRequest->end_time) {
            $attendance->end_time = $workRequest->end_time;
           }

           // 承認後の勤務日が存在する場合に反映
           if ($workRequest->work_date) {
            $attendance->work_date = $workRequest->work_date;
           }
        }

        // 休憩時間データを取得し、承認済みのものがあれば反映
        $breakTimes = Break_time::where('attendance_id', $attendance->id)->get()->map(function ($breakTime) use ($workRequest) {
        // 承認後の休憩開始時間がある場合に反映
        if ($workRequest && $workRequest->status === '承認済み' && $workRequest->break_start_time) {
            $breakTime->break_start_time = $workRequest->break_start_time;
        }

        // 承認後の休憩終了時間がある場合に反映
        if ($workRequest && $workRequest->status === '承認済み' && $workRequest->break_end_time) {
            $breakTime->break_end_time = $workRequest->break_end_time;
        }
        return $breakTime;
    });
        return view('admin.show',compact('user','attendance','breakTimes','workRequest'));
    }

    public function update(AdminCorrectRequest $request,$id)
    {
        $attendance = Attendance::findOrFail($id);

        // 勤怠データの更新
        $attendance->update([
          'start_time' => $request->start_time,
          'end_time' => $request->end_time,
          'reason' => $request->reason ?? null,
        ]);

        // 休憩時間の更新
        foreach ($request->break_start_time as $index => $start) {
           $attendance->breakTimes()->updateOrCreate(
              ['id' => $request->break_id[$index] ?? null], // IDがある場合は更新
              [
                  'break_start_time' => $start,
                  'break_end_time' => $request->break_end_time[$index],
              ]
           );
        }

        return redirect()->back()->with('success', '勤怠情報を更新しました。');
    }

    //申請承認ページ
    public function approve($id)
    {
        
        $workRequest = Work_request::with('user','attendance')->findOrFail($id);
        $user = $workRequest->user;             
        $attendance = Attendance::with('user')->findOrFail($workRequest->attendance_id);
        
        $breakTimes = Break_time::where('attendance_id', $attendance->id)->get();
        
         // 出勤・退勤時間を修正申請に基づいて上書き
        if ($workRequest) {
            if ($workRequest->start_time) {
                $attendance->start_time = $workRequest->start_time;
            }
            if ($workRequest->end_time) {
                $attendance->end_time = $workRequest->end_time;
            }
        }                    

        $modifiedBreakTimes = [];
        if($workRequest) {
            // `attendance`の各休憩時間について修正時間を反映
           foreach ($breakTimes as $originalBreakTime) {
            $modifiedBreakTime = [
                'start' => $workRequest->break_start_time ?? $originalBreakTime->break_start_time,
                'end' => $workRequest->break_end_time ?? $originalBreakTime->break_end_time,
            ];
            $modifiedBreakTimes[] = $modifiedBreakTime;
          }
        } else {
            // 修正申請がない場合は元の休憩時間をそのまま使用
           foreach ($breakTimes as $breakTime) {
            $modifiedBreakTimes[] = [
                'start' => $breakTime->break_start_time,
                'end' => $breakTime->break_end_time,
            ];
          }
        }
         // `work_date` を取得する変数の初期化
        $year = null;
        $date = null;
        if ($workRequest) {
            // `work_date` を Carbon インスタンスに変換して年と月日に分割
            $workDate = Carbon::parse($workRequest->work_date);
            $year = $workDate->year;          // 年度
            $date = $workDate->format('m-d');  // 月日（例: 05-23）
        }

        return view('admin.approve',compact('user','attendance','modifiedBreakTimes','workRequest','year', 'date'));
    }
  
    //承認処理
    public function approveRequest($id)
    {
        $request = Work_request::findOrFail($id);

       // 承認ステータスを更新し、承認日時を記録
        $request->status = '承認済み';
        $request->approval_at = now();
        $request->save();

        // Work_request から attendance_id を取得
        $attendanceId = $request->attendance_id;

        // 勤怠情報に変更を反映
        $attendance = Attendance::findOrFail($attendanceId);
        $attendance->work_date = $request->work_date;
        $attendance->start_time = $request->start_time;
        $attendance->end_time = $request->end_time;
        $attendance->save();

        // 休憩時間を取得 Carbon インスタンスに変換
        $breakStartTime = $request->break_start_time ? Carbon::parse($request->break_start_time) :null;
        $breakEndTime = $request->break_end_time ? Carbon::parse($request->break_end_time) :null;

        // $breakStartTime と $breakEndTime が null でないことを確認して休憩時間を計算
        $breakMinutes = 0; // デフォルト値を設定
        if ($breakStartTime && $breakEndTime) {
            $breakMinutes = $breakStartTime->diffInMinutes($breakEndTime);
        } 

        if ($breakStartTime && $breakEndTime) {
         // 既存の休憩データを取得
        $breakTime = Break_time::where('attendance_id', $attendanceId)
             ->where('break_start_time', '<=', $breakEndTime)
             ->where('break_end_time', '>=', $breakStartTime)
             ->first();

        if ($breakTime) {
            // 既存の休憩データがある場合は更新
            $breakTime->break_start_time = $request->break_start_time;
            $breakTime->break_end_time = $request->break_end_time;
            $breakTime->save();
        } else {
            Break_time::create([
                'attendance_id' => $attendanceId,
                'break_start_time' => $request->break_start_time,
                'break_end_time' => $request->break_end_time,
            ]);
        }
    }  
        return redirect()->back()->with('message', 'Request approved successfully.');
    }

    public function rejectRequest($id)
    {
        $request = AttendanceRequest::findOrFail($id);
        $request->status = 'rejected';
        $request->approved_by = auth()->id();
        $request->save();

        return redirect()->back()->with('message', 'Request rejected.');
    }
}
