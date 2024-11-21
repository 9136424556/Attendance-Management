@extends('layouts.app')

@section('main')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('メール認証済み') }}</div>

                <div class="card-body">
                    {{ __('あなたのメールアドレスは正常に認証されました。') }}

                    <a href="{{ route('home') }}" class="btn btn-primary">{{ __('ホーム画面へ') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection