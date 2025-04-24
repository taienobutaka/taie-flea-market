<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン</title>
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <img class="logo" src="{{ asset('img/logo.svg') }}" alt="ロゴ">
        </div>
        <div class="auth-content">
            <h1 class="auth-title">ログイン</h1>
            <form method="POST" action="{{ route('login') }}" class="auth-form">
                @csrf
                <div class="form-group">
                    <label for="email">メールアドレス</label>
                    <input type="email" id="email" name="email" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">パスワード</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="auth-button">ログイン</button>
                </div>
                <div class="form-group">
                    <a href="{{ route('register') }}" class="register-link">新規登録はこちら</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 