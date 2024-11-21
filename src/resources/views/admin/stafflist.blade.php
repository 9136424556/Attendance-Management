@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('main')
<div class="content">
    <div class="ht-lg">
         <h1 class="title">| スタッフ一覧</h1>
    </div>

    <table class="attendance">
      <tr class="row">
         <th class="username">名前</th>
         <th class="user-email">メールアドレス</th>
         <th class="month-at">月次勤怠</th>
      </tr>
      
      @foreach($users as $user)
      <tr class="row">
         <td class="username">{{ $user->name }}</td>
         <td class="user-email">{{ $user->email }}</td>
         <td class="month-at"><a href="{{ route('admin.staffdetail', ['id' => $user->id]) }}">詳細</a></td>
      </tr>
      @endforeach
    </table>
</div>

@endsection