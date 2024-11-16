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
      <form id="attendanceForm" action="{{ route('approve.request', ['attendance_correct_request' => $workRequest->id]) }}" method="post">
        @csrf
        <div class="row">
          <p class="label">名前</p>
          <p class="value">{{ $user->name }}</p>
        </div>

        <div class="row">
          <p class="label">日付</p>
          <p class="value">
            <input class="input-value"  name="year" value="{{ \Carbon\Carbon::parse($workRequest->work_date)->format('Y') }}" readonly>-
            <input class="input-value"  name="date" value="{{ \Carbon\Carbon::parse($workRequest->work_date)->format('m-d') }}" readonly>
          </p>
        </div>

        <div class="row">
          <p class="label">出勤・退勤</p>
          <p class="value">
            <input class="input-value" id="start_time" name="start_time" value="{{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i') }}" readonly>~
            <input class="input-value" id="end_time" name="end_time" value="{{ \Carbon\Carbon::parse($attendance->end_time)->format('H:i') }}" readonly>
          </p>
          
        </div>
        
        @foreach($modifiedBreakTimes as $index => $breakTime)
        <div class="row">
          <p class="label">休憩 {{ $index + 1 }}</p>
          <p class="value">
            <input class="input-value" id="break_start_time[]" name="break_start_time" value="{{ \Carbon\Carbon::parse($breakTime['start'])->format('H:i') }}" readonly>~
            <input class="input-value" id="break_end_time[]" name="break_end_time" value="{{ \Carbon\Carbon::parse($breakTime['end'])->format('H:i') }}" readonly>
          </p> 
        </div>
        @endforeach

        <div class="row-ex">
          <p class="label">備考</p>
          <div class="value">
            <textarea class="input-value-tx" name="reason" readonly>{{ $workRequest->reason }}</textarea>
          </div>
        </div>

        <div class="button">
          @if($workRequest->status === '承認待ち')
            <input type="hidden" name="user_id">
            <button class="btn" type="submit" onclick='return confirm("承認しますか？")'>承認</button>
          @elseif($workRequest->status === '承認済み')
            <button class="btn-nt">承認済み</button>
          @endif
         </div>
        
      </form>
    </div>
</div>
@endsection