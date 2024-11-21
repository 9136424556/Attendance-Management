@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request.css') }}">
@endsection

@section('main')
<div class="content">
    <div class="ht-lg">
        <h1 class="title">| 申請一覧</h1>
    </div>
    
    <div class="main-content">
      <input type="radio" name="tab" id="check1" checked><label class="tab" for="check1">承認待ち</label>
      <input type="radio" name="tab" id="check2"><label class="tab" for="check2">承認済み</label>
      <div class="tab-content" id="tabcontent1">
        <table class="attendance">
            <tr class="row">
                <th class="th-lg">状態</th>
                <th class="th-lg">名前</th>
                <th class="th-lg">対象日時</th>
                <th class="th-lg">申請理由</th>
                <th class="th-lg">申請日時</th>
                <th class="th-lg">詳細</th>
            </tr>
            @foreach($workRequests as $workRequest)
            <tr class="row">
                <td>{{ $workRequest->status }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $workRequest->work_date }}</td>
                <td>{{ $workRequest->reason }}</td>
                <td>{{ $workRequest->requested_at }}</td>
                <td><a href="{{ route('requested.show', ['id' => $workRequest->id] ) }}">詳細</a></td>
            </tr>
            @endforeach
        </table>
      </div>
      <div class="tab-content" id="tabcontent2">
       <table class="attendance">
            <tr class="row">
                <th class="th-lg">状態</th>
                <th class="th-lg">名前</th>
                <th class="th-lg">対象日時</th>
                <th class="th-lg">申請理由</th>
                <th class="th-lg">申請日時</th>
                <th class="th-lg">詳細</th>
            </tr>
            @foreach ($approvedRequests as $request)
            <tr class="row">
                <td>{{ $request->status }}</td>
                <td>{{ $request->user->name }}</td>
                <td>{{ $request->work_date }}</td>
                <td>{{ $request->reason }}</td>
                <td>{{ $request->requested_at }}</td>
                <td><a href="{{ route('requested.show', ['id' => $request->id] ) }}">詳細</a></td>
            </tr>
            @endforeach
        </table>
      </div>

    </div>
</div>
@endsection