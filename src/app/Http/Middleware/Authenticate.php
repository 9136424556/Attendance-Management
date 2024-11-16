<?php

namespace App\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
             // リクエストが admin 用のパスにアクセスしているかを確認
             if ($request->is('admin/*')) {
                return route('admin.login'); // 管理者用のログインページへリダイレクト
             }

             // それ以外は一般ユーザーのログインページへリダイレクト
             return route('login');
        }
    }

    protected function authenticate($request, array $guards)
    {
      if (empty($guards)) {
        $guards = ['web']; // デフォルトガードとして 'web' を指定
      }

      foreach ($guards as $guard) {
        if (Auth::guard($guard)->check()) {
            return $this->auth->shouldUse($guard);
        }
      }

      throw new AuthenticationException('Unauthenticated.', $guards);
    }
}
