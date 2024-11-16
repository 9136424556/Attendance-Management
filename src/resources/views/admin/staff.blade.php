@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('main')
<div class="content">
    <div class="ht-lg">
         <h1 class="title">| {{ $user->name }}さんの勤怠</h1>
    </div>
   
    <div class="main-content">
    {{-- 前月と翌月に移動するボタン --}}
        <a class="page-link" href="{{ route('admin.staffdetail', ['id' => $user->id, 'date' => \Carbon\Carbon::parse($currentDate)->subMonth()->format('Y-m')]) }}"><-前月</a>
        {{-- カレンダーボタン --}}
        <button id="calendar-button" class="calendar-button">
           📅 
        </button>
        <span>{{ \Carbon\Carbon::parse($currentDate)->format('Y年m月') }}</span>
        <a class="page-link2" href="{{ route('admin.staffdetail', ['id' => $user->id, 'date' => \Carbon\Carbon::parse($currentDate)->addMonth()->format('Y-m')]) }}">翌月-></a>

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
         <td><a href="{{ route('admin.show', ['id' => $attendance->id] ) }}">詳細</a></td>
      </tr>
      @endforeach
    </table>

    <!-- CSV出力ボタン -->
    @if($attendances->count() > 0)
    <a href="{{ route('export', ['id' => $user->id, 'date' => $currentDate]) }}" class="btn" onclick='return confirm("勤怠情報をダウンロードしますか？")'>
        CSV出力
    </a>
    @endif
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const userId = "{{ $user->id }}"; // $idをJavaScriptで使用できるようにする

        const datepicker = flatpickr("#datepicker", {
            dateFormat: "Y-m-d",
            defaultDate: "{{ \Carbon\Carbon::parse($currentDate)->format('Y-m-d') }}", // 現在の日付をデフォルトに設定
            onChange: function(selectedDates, dateStr, instance) {
                // 日付が選択されたときにページをリロード
                window.location.href = "/admin/attendance/staff/" + userId + "?date=" + dateStr;
            }
        });
         // カレンダーボタンがクリックされたときにFlatpickrを表示
         document.getElementById("calendar-button").addEventListener("click", function() {
            datepicker.open();
        });
    });
</script>

@endsection