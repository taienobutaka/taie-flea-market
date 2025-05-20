<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
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
     * @param  \App\Http\Requests\RegisterRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(RegisterRequest $request)
    {
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

        // メール認証済みの場合はプロフィール設定画面へ
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('profile.create');
        }

        // メール認証誘導画面にリダイレクト
        return redirect('/verification');
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

        // ログイン画面にリダイレクト
        return redirect()->route('login');
    }

    /**
     * 認証確認画面を表示
     *
     * @return \Illuminate\View\View
     */
    public function showVerificationForm()
    {
        $user = Auth::user();

        // メール認証済みの場合はプロフィール設定画面へ
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('profile.create');
        }

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
        $credentials = $request->validated();

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $request->session()->regenerate();

            // 全てのログインユーザーを商品一覧画面へリダイレクト
            return redirect('/');
        }

        return back()->withErrors([
            'login' => 'メールアドレスまたはパスワードが間違っています',
        ])->withInput();
    }

    /**
     * ログアウト処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
