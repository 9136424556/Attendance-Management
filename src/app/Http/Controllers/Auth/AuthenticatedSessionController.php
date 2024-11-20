<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
     //ログインフォーム
    public function create()
    {
        return view('auth.login');
    }

     //ログイン処理
    public function store(LoginRequest $request)
    {
         // 認証の試行
        if (!Auth::attempt($request->only('email', 'password'))) {
           return back()->withErrors(['email' => 'ログイン情報が登録されていません']);
        }
       
        // 認証成功時の処理
        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    //ログアウト処理
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
