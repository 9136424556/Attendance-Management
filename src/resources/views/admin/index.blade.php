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
        <a class="page-link" href="{{ route('admin.index', ['date' => \Carbon\Carbon::parse($currentDate)->subDay()->format('Y-m-d')]) }}">←前日</a>
        {{-- カレンダーボタン --}}
        <button id="calendar-button" class="calendar-button">
           📅 
        </button>
        <span>{{ \Carbon\Carbon::parse($currentDate)->format('Y年m月d日') }}</span>
        <a class="page-link2" href="{{ route('admin.index', ['date' => \Carbon\Carbon::parse($currentDate)->addDay()->format('Y-m-d')]) }}">翌日→</a>

         {{-- カレンダー用の非表示インプット --}}
        <input type="date" id="datepicker" style="display: none;">
    </div>

    <table class="attendance">
      <tr class="row">
         <th>名前</th>
         <th>出勤</th>
         <th>退勤</th>
         <th>休憩</th>
         <th>合計</th>
         <th>詳細</th>
      </tr>
      
      @foreach($attendances as $attendance)
      <tr class="row">
         <td class="username">{{ $attendance->user->name  }}</td>
         <td class="time">{{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i') }}</td>
         <td class="time">{{ \Carbon\Carbon::parse($attendance->end_time)->format('H:i') }}</td>
         <td class="time">{{ $attendance->total_break_time }}</td>
         <td class="time">{{ $attendance->total_work_time }}</td>
         <td><a href="{{ route('admin.show', ['id' => $attendance->id] ) }}">詳細</a></td>
      </tr>
      @endforeach
    </table>
   
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Flatpickrのインスタンスを作成
        const datepicker = flatpickr("#datepicker", {
            dateFormat: "Y-m-d",
            defaultDate: "{{ \Carbon\Carbon::parse($currentDate)->format('Y-m-d') }}", // 現在の日付をデフォルトに設定
            onChange: function(selectedDates, dateStr, instance) {
                // 日付が選択されたときにページをリロード
                window.location.href = "{{ route('admin.index') }}?date=" + dateStr;
            }
        });
         // カレンダーボタンがクリックされたときにFlatpickrを表示
         document.getElementById("calendar-button").addEventListener("click", function() {
            datepicker.open();
        });
    });
</script>

@endsection