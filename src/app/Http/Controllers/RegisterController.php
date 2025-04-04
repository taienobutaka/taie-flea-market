<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    /**
     * 会員登録フォームを表示
     *
     * @return \Illuminate\View\View
     */
    public function showRegisterForm()
    {
        return view('register');
    }

    /**
     * 会員登録処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // ユーザーを作成
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // ログイン処理
        Auth::login($user);

        // 認証メール送信
        $user->sendEmailVerificationNotification();

        // 認証確認画面にリダイレクト
        return redirect()->route('verification');
    }

    /**
     * メール認証処理
     *
     * @param  \Illuminate\Foundation\Auth\EmailVerificationRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(EmailVerificationRequest $request)
    {
        // メールアドレスを認証済みにする
        $request->fulfill();

        // プロフィール設定画面にリダイレクト
        return redirect('/create-profile');
    }

    /**
     * 認証確認画面を表示
     *
     * @return \Illuminate\View\View
     */
    public function showVerificationForm()
    {
        return view('verification');
    }

    /**
     * 認証メール再送信
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended('/home'); // 既に認証済みの場合
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent'); // ステータスメッセージを返す
    }

    /**
     * ログインフォームを表示
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('login');
    }

    /**
     * ログイン処理
     *
     * @param  \App\Http\Requests\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // ログイン成功時、商品一覧画面にリダイレクト
            return redirect('/');
        }

        // ログイン失敗時、エラーメッセージを返す
        return back()->withErrors([
            'login' => 'ログイン情報が登録されていません',
        ])->withInput();
    }
}
