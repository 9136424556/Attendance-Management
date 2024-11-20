@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('main')
<div class="content" id="app">
    <div class="main-content">
     <p class="status" id="workStatus">勤務状況: {{ $workStatus }}</p>
     <p class="date" id="currentDate">{{ \Carbon\Carbon::now()->locale('ja')->translatedFormat('Y-m-d (D)') }}</p>
     <p class="time" id="current-time"> --</p>
    </div>

    <div class="button-a">
    <!-- 出勤 -->
    @if($attendanceStatus === 'beforeClockIn')
     <form action="{{ route('attendance.start') }}" method="post">
      @csrf
         <button class="btn" type="submit">出勤</button>
     </form>
    <!-- 退勤と休憩 -->
    @elseif($attendanceStatus === 'clockedIn')
     <form action="{{ route('attendance.end') }}" method="post">
      @csrf
         <button class="btn" type="submit">退勤</button>
     </form>
     <form action="{{ route('attendance.breakstart') }}" method="post">
      @csrf
         <button class="btn-2" type="submit">休憩</button>
     </form>
    <!-- 休憩戻 -->
    @elseif($attendanceStatus === 'onBreak')
    <form action="{{ route('attendance.breakend') }}" method="post">
     @csrf
        <button class="btn-2" type="submit">休憩戻</button>
    </form>
    <!-- 退勤済みメッセージ -->
    @elseif($attendanceStatus === 'afterend')
      <p class="message">お疲れ様でした。</p>
    @endif
  </div>
</div>

<script src="https://unpkg.com/vue@3"></script>
<script>
    function updateTime() {
        const now = new Date();
        const time = now.toLocaleTimeString(); // 「時:分:秒」の形式で表示

        document.getElementById('current-time').innerText = ` ${time}`;
    }

    // 1秒ごとに現在の時刻を更新
    setInterval(updateTime, 1000);
    // 初期表示
    updateTime();

    const currentDate = @json($currentDate); // Laravelからの受け取り

     // 曜日を日本語で表示
    const date = new Date(currentDate);
    const options = { weekday: 'short' }; // 曜日の短縮形を取得
    const weekday = date.toLocaleDateString('ja-JP', options); // 日本語の曜日

    // 曜日を <span> に追加
    document.getElementById('weekday').innerText = `(${weekday})`;

    watch(() => currentDate, (newDate) => {
     if (newDate !== previousDate) {
        // ここでボタンをリセットする処理を入れる
        attendanceStatus.value = 'beforeClockIn';
     }
    });
</script>
@endsection