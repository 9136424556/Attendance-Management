@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('main')
<div class="content">
    <div class="ht-lg">
         <h1 class="title">| 勤怠一覧</h1>
    </div>
   
    <div class="main-content">
    {{-- 前月と翌月に移動するボタン --}}
        <a class="page-link" href="{{ route('attendance.list', ['date' => \Carbon\Carbon::parse($currentDate)->subMonth()->format('Y-m')]) }}"><-前月</a>
        {{-- カレンダーボタン --}}
        <button id="calendar-button" class="calendar-button">
           📅 
        </button>
        <span>{{ \Carbon\Carbon::parse($currentDate)->format('Y年m月') }}</span>
        <a class="page-link2" href="{{ route('attendance.list', ['date' => \Carbon\Carbon::parse($currentDate)->addMonth()->format('Y-m')]) }}">翌月-></a>

         {{-- カレンダー用の非表示インプット --}}
        <input type="date" id="datepicker" style="display: none;">
    </div>

    <table class="attendance">
      <tr class="row">
         <th>日付</th>
         <th>出勤</th>
         <th>退勤</th>
         <th>休憩</th>
         <th>合計</th>
         <th>詳細</th>
      </tr>
      
      @foreach($attendances as $attendance)
      <tr class="row">
         <td>{{ $attendance->work_date }}</td>
         <td>{{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i') }}</td>
         <td>{{ \Carbon\Carbon::parse($attendance->end_time)->format('H:i') }}</td>
         <td>{{ $attendance->total_break_time }}</td>
         <td>{{ $attendance->total_work_time }}</td>
         @if($workRequests->has($attendance->id) && $workRequests[$attendance->id]->is_submitted)
         <td><a href="{{ route('requested.show', ['id' => $workRequests[$attendance->id]->id]) }}">詳細</a></td>
         @else
         <td><a href="{{ route('attendance.show', ['id' => $attendance->id] ) }}">詳細</a></td>
         @endif
      </tr>
      @endforeach
    </table>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const datepicker = flatpickr("#datepicker", {
            dateFormat: "Y-m-d",
            defaultDate: "{{ \Carbon\Carbon::parse($currentDate)->format('Y-m-d') }}", // 現在の日付をデフォルトに設定
            onChange: function(selectedDates, dateStr, instance) {
                // 日付が選択されたときにページをリロード
                window.location.href = "{{ route('attendance.list') }}?date=" + dateStr;
            }
        });
         // カレンダーボタンがクリックされたときにFlatpickrを表示
         document.getElementById("calendar-button").addEventListener("click", function() {
            datepicker.open();
        });
    });
</script>

@endsection