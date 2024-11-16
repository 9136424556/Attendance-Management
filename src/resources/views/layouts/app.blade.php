<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Document</title>
    <script src="http://localhost/js/app.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <!-- FlatpickrのJavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- FlatpickrのCSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @yield('css')
</head>
<body>
    <header class="head">
       <div class="head-display">
          <div class="head-logo">
             <img class="header-logo-w" src="{{ asset('image/logo.svg') }}" alt="">
          </div>
          
          
          @if(auth('admin')->check())
          <div class="head-list">
            <div class="li-btn">
                <p><a class="link" href="/admin/attendance/list">勤怠一覧</a></p>
            </div>

            <div class="li-btn">
                <p><a class="link" href="/admin/staff/list">スタッフ一覧</a></p>
            </div>

            <div class="li-btn">
                <p><a class="link" href="/admin/stamp_correction_request/list">申請</a></p>
            </div>

             <div class="li-btn">
                <form action="{{ route('admin.logout') }}" name="logout" method="post">
                    @csrf
                    <p ><a class="link" onclick="document.logout.submit();">ログアウト</a></p>
                </form>
             </div>
          </div>
          @elseif(Auth::check())
          <div class="head-list">
            <div class="li-btn">
                <p><a class="link" href="/attendance">勤怠</a></p>
            </div>

            <div class="li-btn">
                <p><a class="link" href="/attendance/list">勤怠一覧</a></p>
            </div>

            <div class="li-btn">
                <p><a class="link" href="/stamp_correction_request/list">申請</a></p>
            </div>

             <div class="li-btn">
                <form action="{{ route('logout') }}" name="logout" method="post">
                    @csrf
                    <p ><a class="link" onclick="document.logout.submit();">ログアウト</a></p>
                </form>
             </div>
          </div>
         
          @endif

         
       </div>
    </header>
    <main class="main">
        @yield('main')
    </main>
</body>
</html>