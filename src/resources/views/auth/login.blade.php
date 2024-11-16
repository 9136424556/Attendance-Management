@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('main')
<div class="content">
    <div class="title">
        <h1>ログイン</h1>
    </div>

    <div class="main-content">
        <form method="post" action="{{ route('login') }}">
        @csrf
           <div class="mt-4">
              <p><label class="input-label" for="email">メールアドレス</label></p>
              <p><input id="email" class="input-form" type="email" name="email"></p>
           </div>
           @error('email')
            {{ $message }}
           @enderror
           <div class="mt-4">
              <p><label class="input-label" for="pass">パスワード</label></p>
              <p><input id="pass" class="input-form" type="password" name="password"></p>
           </div>
           @error('password')
            {{ $message }}
           @enderror

           <div class="flex items-center justify-end mt-4">
                <button class="ml-4">
                    {{ __('ログインする') }}
                </button>
            </div>
            <a class="underline text-sm hover:text-blue-900 text-lg " href="{{ route('register') }}">
                    {{ __('会員登録はこちら') }}
            </a>
        </form>
    </div>
</div>
@endsection