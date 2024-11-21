@extends('layouts.app')


@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('main')
<div class="content">
    <div class="title">
        <h1>会員登録</h1>
    </div>

    <div class="main-content">
        <form method="post" action="{{ route('register') }}">
        @csrf
           <div class="mt-4">
               <p><label for="name">名前</label></p>
               <p><input id="name" class="input-form" type="text" name="name" value="{{ old('name') }}"></p>
           </div>
           @error('name')
           {{ $message }}
           @enderror
           <div class="mt-4">
              <p><label for="email">メールアドレス</label></p>
              <p><input id="email" class="input-form" type="email" name="email" value="{{ old('email') }}"></p>
           </div>
           @error('email')
            {{ $message }}
           @enderror
           <div class="mt-4">
              <p><label for="pass">パスワード</label></p>
              <p><input id="pass" class="input-form" type="password" name="password" autocomplete="new-password"></p>
           </div>
           @error('password')
            {{ $message }}
           @enderror
           <div class="mt-4">
              <p><label for="pass-confirm">パスワード確認</label></p>
              <p><input id="pass-confirm" class="input-form" type="password" name="password_confirmation"  autocomplete="new-password" pattern=".{8,}"></p>
           </div>
           <div class="flex items-center justify-end mt-4">
                <button class="ml-4" type="submit">
                    {{ __('登録する') }}
                </button>
            </div>
            <a class="underline text-sm text-gray-600 hover:text-blue-900" href="{{ route('login') }}">
                    {{ __('ログインはこちら') }}
            </a>
        </form>
    </div>
</div>
@endsection