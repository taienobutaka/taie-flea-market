<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会員登録</title>
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>
<body>
    <div class="auth-header">
        <img class="logo" src="{{ asset('img/logo.svg') }}" alt="ロゴ">
    </div>
    <div class="auth-content">
        <h1>会員登録</h1>
        <form action="/register" method="POST" novalidate>
            @csrf
            <div class="form-group">
                <label for="username">ユーザー名</label>
                <input type="text" id="username" name="username">
                @error('username')
                    <p style="color: red;">{{ $message }}</p>
                @enderror
            </div>
            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input type="email" id="email" name="email">
                @error('email')
                    <p style="color: red;">{{ $message }}</p>
                @enderror
            </div>
            <div class="form-group">
                <label for="password">パスワード</label>
                <input type="password" id="password" name="password">
                @error('password')
                    <p style="color: red;">{{ $message }}</p>
                @enderror
            </div>
            <div class="form-group">
                <label for="password_confirmation">確認用パスワード</label>
                <input type="password" id="password_confirmation" name="password_confirmation">
                @error('password_confirmation')
                    <p style="color: red;">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="register-button"><span>登録する</span></button>
        </form>
        <a href="/login" class="login-link">ログインはこちら</a>
    </div>
</body>
</html>
