<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CorrectionRequest;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Break_time;
use App\Models\Work_request;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RequestController extends Controller
{
    //勤怠詳細・修正申請ページ
    public function show($id)
    {
        $user = User::find(Auth::id());
        $attendance = Attendance::with('user')->findOrFail($id);
        $breakTimes = Break_time::where('attendance_id', $attendance->id)
                                ->orderBy('break_start_time', 'asc')
                                ->get();
        $workRequest = Work_request::where('attendance_id', $id)
                                   ->where('user_id', $user->id)                 
                                   ->first();

        return view('show',compact('attendance','user','breakTimes','workRequest'));
    }
    //勤怠情報の修正を申請
    public function attendanceRequest(CorrectionRequest $request, $id)
    {
      // バリデーションは自動的にCorrectionRequestで処理される
        $validated = $request->validated();

        $work_date = $request->input('year') . '-' . $request->input('date');

            // 日付が正しいか確認
        if (!strtotime($work_date)) {
          return redirect()->back()->withErrors(['work_date' => '無効な日付です。']);
        }

        $attendance = Attendance::findOrFail($id);


          // 既存の休憩時間を削除
        Break_time::where('attendance_id', $attendance->id)->delete();

          // 各休憩時間を新しく保存
        $breakStartTimes = $request->input('break_start_time');
        $breakEndTimes = $request->input('break_end_time');
        
    
        if ($breakStartTimes && $breakEndTimes) {
          foreach ($breakStartTimes as $index => $startTime) {
              $endTime = $breakEndTimes[$index];
              if ($startTime && $endTime) { // 時間が入力されている場合のみ保存
                  Break_time::create([
                    'attendance_id' => $attendance->id,
                    'break_start_time' => $startTime,
                    'break_end_time' => $endTime,
                  ]);
              }
          }
        }

        //申請情報を保存
        $attendanceRequest = Work_request::create([
            'attendance_id' => $attendance->id,
            'user_id' => auth()->id(),
            'work_date' => $work_date,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'reason' => $validated['reason'],
            'is_submitted' => true, // フラグをtrueに設定
        ]);
       
        return redirect('/attendance/list')->with('status', '申請が送信されました');
    }

    //申請一覧
    public function requestlist()
    {
      //管理者ログインの場合
      if (auth('admin')->check()) {
        // 全ユーザーの承認待ち申請とユーザー情報を取得
        $workRequests = Work_request::with('user')->where('status', '承認待ち')->get();
        $approvedRequests = Work_request::with('user')->where('status', '承認済み')->get();

         // データをフォーマット
         $formattedWorkRequests = $workRequests->map(function ($request) {
          return [
              'id' => $request->id,
              'user_name' => $request->user->name ?? '不明',
              'work_date' => $request->work_date,
              'start_time' => $request->start_time,
              'end_time' => $request->end_time,
              'reason' => $request->reason,
              'status' => $request->status,
          ];
      });

        return view('admin.request',compact('formattedWorkRequests','workRequests','approvedRequests'));
      //一般ログインの場合
      } elseif (auth('web')->check()) {
        // 管理者セッションの無効化
         auth('admin')->logout();

        // 一般ユーザーの場合の処理
        $user = auth('web')->user();
        $attendances = Attendance::where('user_id', $user->id)->orderBy('work_date')->get();
        $workRequests = Work_request::where('user_id', $user->id)
                                    ->where('status', '承認待ち')
                                    ->get();

        $approvedRequests = Work_request::where('user_id', $user->id)
                                         ->where('status', '承認済み')
                                         ->get();

        return view('request', compact('user','attendances','workRequests','approvedRequests'));
      } else {
        return back();
      }
    }

    //申請詳細ページ
    public function requested($id)
    {
        $user = User::find(Auth::id());
        // データベースから該当の申請情報を取得
        $workRequest = Work_request::findOrFail($id);

        $attendance = Attendance::with('breakTimes')->findOrFail($workRequest->attendance_id);
        $breakTimes = [];
        
        if($workRequest) {
            // `attendance`の各休憩時間について修正時間を反映
           foreach ($attendance->breakTimes as $originalBreakTime) {
            $modifiedBreakTime = [
                'start' => $workRequest->break_start_time ? $workRequest->break_start_time : $originalBreakTime->break_start_time,
                'end' => $workRequest->break_end_time ? $workRequest->break_end_time : $originalBreakTime->break_end_time,
            ];
            $breakTimes[] = $modifiedBreakTime;
          }
        } else {
            // 修正申請がない場合は元の休憩時間をそのまま使用
           foreach ($attendance->breakTimes as $breakTime) {
            $breakTimes[] = [
                'start' => $breakTime->break_start_time,
                'end' => $breakTime->break_end_time,
            ];
        }
        }
            // `work_date` を Carbon インスタンスに変換して年と月日に分割
        $workDate = Carbon::parse($workRequest->work_date);
        $year = $workDate->year;          // 年度
        $date = $workDate->format('m-d');  // 月日（例: 05-23）

        return view('requested',compact('user','attendance','breakTimes','workRequest','year','date'));
    }

    //勤怠情報の修正を申請(承認済みのものを再度修正申請)
    public function requestedreturn(CorrectionRequest $request, $id)
    {
        // バリデーションは自動的にCorrectionRequestで処理される
        $validated = $request->validated();

        $work_date = $request->input('year') . '-' . $request->input('date');

            // 日付が正しいか確認
        if (!strtotime($work_date)) {
          return redirect()->back()->withErrors(['work_date' => '無効な日付です。']);
        }

        $workRequest = Work_request::findOrFail($id);
        // 関連する勤怠情報を取得
        $attendance = $workRequest->attendance;
        if (!$attendance) {
          return redirect()->back()->withErrors(['attendance' => '関連する勤怠情報が見つかりません。']);
        }

          // 既存の休憩時間を削除
        Break_time::where('attendance_id', $attendance->id)->delete();

          // 各休憩時間を新しく保存
        $breakStartTimes = $request->input('break_start_time');
        $breakEndTimes = $request->input('break_end_time');
        
    
        if ($breakStartTimes && $breakEndTimes) {
          foreach ($breakStartTimes as $index => $startTime) {
              $endTime = $breakEndTimes[$index];
              if ($startTime && $endTime) { // 時間が入力されている場合のみ保存
                  Break_time::create([
                    'attendance_id' => $attendance->id,
                    'break_start_time' => $startTime,
                    'break_end_time' => $endTime,
                  ]);
              }
          }
        }

        //申請情報を保存
        $attendanceRequest = Work_request::create([
            'attendance_id' => $attendance->id,
            'user_id' => auth()->id(),
            'work_date' => $work_date,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'reason' => $validated['reason'],
            'is_submitted' => true, // フラグをtrueに設定
        ]);
       
        return redirect('/stamp_correction_request/list')->with('status', '申請が送信されました');
    }
}
