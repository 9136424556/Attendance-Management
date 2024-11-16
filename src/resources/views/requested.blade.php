@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/show.css') }}">
@endsection

@section('main')
<div class="content">
    <div class="ht-lg">
      <h1 class="title">| 勤務詳細</h1>
    </div>

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
            <input class="input-value"  name="year" value="{{ $year }}" readonly>-
            <input class="input-value"  name="date" value="{{ $date }}">
          </p>
        </div>

        <div class="row">
          <p class="label">出勤・退勤</p>
          <p class="value">
            <input class="input-value" value="{{ \Carbon\Carbon::createFromFormat('H:i:s', $workRequest->start_time ?? $attendance->start_time)->format('H:i') }}" >~
            <input class="input-value" value="{{ \Carbon\Carbon::createFromFormat('H:i:s', $workRequest->end_time ?? $attendance->end_time)->format('H:i') }}">
          </p>
          
        </div>
        
        @foreach($breakTimes as $index => $breakTime)
        <div class="row">
          <p class="label">休憩 {{ $index + 1 }}</p>
          <p class="value">
            <input class="input-value"  value="{{ \Carbon\Carbon::parse($breakTime['start'])->format('H:i') }}" >~
            <input class="input-value"  value="{{ \Carbon\Carbon::parse($breakTime['end'])->format('H:i') }}">
          </p> 
        </div>
        @endforeach

        <div class="row-ex">
          <p class="label">備考</p>
          <div class="value">
            <textarea class="input-value-tx" name="reason">{{ $workRequest->reason }}</textarea>
          </div>
        </div>

       @if($workRequest->status === '承認済み')
       <input type="hidden" name="user_id">
       <button class="btn" type="submit"  id="submitBtn" onclick='return confirm("申請を提出しますか？")'>修正</button>
       @else
        <p class="n-correction">承認待ちのため修正はできません。</p>
       @endif
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
    });
</script>
@endsection