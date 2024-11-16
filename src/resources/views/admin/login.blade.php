@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('main')
<div class="content">
    <div class="title">
        <h1>管理者ログイン</h1>
    </div>

    <div class="main-content">
        <form method="post" action="/admin/login">
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
                    {{ __('管理者ログインする') }}
                </button>
            </div>
        
        </form>
    </div>
</div>
@endsection