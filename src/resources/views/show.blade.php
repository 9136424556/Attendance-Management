@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/show.css') }}">
@endsection

@section('main')
<div class="content">
    <div class="ht-lg">
      <h1 class="title">| 勤務詳細</h1>
    </div>

    <!--エラーメッセージ表示-->
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="main-content">
      <form id="attendanceForm" action="{{ route('attendance.request', ['id' => $attendance->id]) }}" method="post">
        @csrf
        <div class="row">
          <p class="label">名前</p>
          <p class="value">{{ $user->name }}</p>
        </div>

        <div class="row">
          <p class="label">日付</p>
          <p class="value">
            <input class="input-value"  name="year" value="{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y') }}" readonly>-
            <input class="input-value"  name="date" value="{{ \Carbon\Carbon::parse($attendance->work_date)->format('m-d') }}" >
          </p>
        </div>

        <div class="row">
          <p class="label">出勤・退勤</p>
          <p class="value">
            <input class="input-value" id="start_time" name="start_time" value="{{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i') }}" required>~
            <input class="input-value" id="end_time" name="end_time" value="{{ \Carbon\Carbon::parse($attendance->end_time)->format('H:i') }}" required>
          </p>
        </div>

        <div class="break-rows"> 
         <!-- 休憩時間入力欄 -->
          @foreach($breakTimes as $index => $breakTime)
          <div class="row" data-index="{{ $index }}">
            <p class="label">休憩 {{ $index + 1 }}</p>
            <p class="value">
              <input class="input-value" id="break_start_time_{{ $index }}" name="break_start_time" value="{{ \Carbon\Carbon::parse($breakTime->break_start_time)->format('H:i') }}" required>~
              <input class="input-value" id="break_end_time_{{ $index }}" name="break_end_time" value="{{ \Carbon\Carbon::parse($breakTime->break_end_time)->format('H:i') }}" required>
            </p> 
          </div>
          @endforeach
        </div>

        <!-- 休憩時間追加ボタン -->
       <button type="button" onclick="addBreakTime()">休憩時間を追加</button>

        <div class="row-ex">
          <p class="label">備考</p>
          <div class="value">
            <textarea class="input-value-tx" name="reason" value="{{ old('reason') }}"></textarea>
          </div>
        </div>

        <div class="button">
            <input type="hidden" name="user_id">
            <button class="btn" type="submit"  id="submitBtn" disabled onclick='return confirm("申請を提出しますか？")'>修正</button>
         </div>
        
      </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.getElementById("attendanceForm");
        const submitBtn = document.getElementById("submitBtn");

        // デフォルトの値を取得
        const defaultStartTime = document.getElementById("start_time").value;
        const defaultEndTime = document.getElementById("end_time").value;
        const defaultBreakStartTimes = [];
        const defaultBreakEndTimes = [];
        document.querySelectorAll("[id^='break_start_time']").forEach((input, index) => {
              defaultBreakStartTimes[index] = input.value;
        });
        document.querySelectorAll("[id^='break_end_time']").forEach((input, index) => {
              defaultBreakEndTimes[index] = input.value;
        });

        // 入力フィールドの変更を監視
        form.addEventListener("input", function() {
            const currentStartTime = document.getElementById("start_time").value;
            const currentEndTime = document.getElementById("end_time").value;
            const currentBreakStartTimes = [];
            const currentBreakEndTimes = [];
            document.querySelectorAll("[id^='break_start_time']").forEach((input, index) => {
                  currentBreakStartTimes[index] = input.value;
            });
            document.querySelectorAll("[id^='break_end_time']").forEach((input, index) => {
                  currentBreakEndTimes[index] = input.value;
            });

            // 変更があった場合はボタンを有効化
            const isBreakStartTimeChanged = currentBreakStartTimes.some((value, index) => value !== defaultBreakStartTimes[index]);
            const isBreakEndTimeChanged = currentBreakEndTimes.some((value, index) => value !== defaultBreakEndTimes[index]);

            if (currentStartTime !== defaultStartTime || 
                currentEndTime !== defaultEndTime ||
                isBreakStartTimeChanged ||
                isBreakEndTimeChanged) {
                submitBtn.disabled = false; // ボタンを有効化
            } else {
                submitBtn.disabled = true; // ボタンを無効化
            }
        });

          // 休憩時間の初期インデックス設定
          let breakIndex = {{ count($breakTimes) }}; // 初期インデックスはサーバーサイドで渡された値
          // 休憩時間追加後にも監視対象に追加
          const addBreakTime = () => {
            breakIndex++;
            const breakRows = document.querySelector('.break-rows');
            const newBreakRow = document.createElement('div');
            newBreakRow.classList.add('break-row');
            newBreakRow.innerHTML = `
                <p class="label">休憩 ${breakIndex}</p>
                <p class="value">
                    <input class="input-value" name="break_start_time[]" id="break_start_time_${breakIndex}" required> ~
                    <input class="input-value" name="break_end_time[]" id="break_end_time_${breakIndex}" required>
                    <button type="button" class="delete-break-btn">削除</button>
                </p>
            `;
             
          // 親要素（break-rows）に追加
          const breakRowsContainer = document.querySelector('.break-rows');
            breakRowsContainer.appendChild(newBreakRow);
             // 新しい休憩時間フィールドも監視対象に追加
            document.getElementById(`break_start_time_${breakIndex}`).addEventListener('input', updateButtonState);
            document.getElementById(`break_end_time_${breakIndex}`).addEventListener('input', updateButtonState);
          };

         // 休憩時間削除
         const deleteBreakTime = (index) => {
         const breakRow = document.getElementById(`break_start_time_${index}`).closest('.break-row');
          breakRow.remove();
          breakIndex--; // インデックスを減らす
          // 削除後もボタンの状態を更新
          updateButtonState();
        };
        // 休憩時間追加ボタンにイベントリスナーを追加
        document.querySelector('button[type="button"]').addEventListener('click', addBreakTime);
        // 休憩時間削除ボタンへのイベントリスナーを動的に追加する方法
        form.addEventListener('click', function(event) {
          if (event.target && event.target.classList.contains('delete-break-btn')) {
            const breakRow = event.target.closest('.break-row');
            const breakStartTimeId = breakRow.querySelector('input[name^="break_start_time"]').id;
            const breakIndex = breakStartTimeId.split('_').pop();  // breakIndexを抽出
            deleteBreakTime(breakIndex);
          }
        });
         // ボタンの状態を更新する関数
         function updateButtonState() {
            const currentStartTime = document.getElementById("start_time").value;
            const currentEndTime = document.getElementById("end_time").value;
            const currentBreakStartTimes = [];
            const currentBreakEndTimes = [];
            document.querySelectorAll("[id^='break_start_time']").forEach((input, index) => {
                currentBreakStartTimes[index] = input.value;
            });
            document.querySelectorAll("[id^='break_end_time']").forEach((input, index) => {
                currentBreakEndTimes[index] = input.value;
            });

            // 変更があった場合はボタンを有効化
            const isBreakStartTimeChanged = currentBreakStartTimes.some((value, index) => value !== defaultBreakStartTimes[index]);
            const isBreakEndTimeChanged = currentBreakEndTimes.some((value, index) => value !== defaultBreakEndTimes[index]);

            if (currentStartTime !== defaultStartTime || 
                currentEndTime !== defaultEndTime ||
                isBreakStartTimeChanged ||
                isBreakEndTimeChanged) {
                submitBtn.disabled = false; // ボタンを有効化
            } else {
                submitBtn.disabled = true; // ボタンを無効化
            }
        }
    });
</script>

@endsection