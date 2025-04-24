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
    <div class="auth-container">
        <div class="auth-header">
            <img class="logo" src="{{ asset('img/logo.svg') }}" alt="ロゴ">
        </div>
        <div class="auth-content">
            <h1 class="auth-title">会員登録</h1>
            <form method="POST" action="{{ route('register') }}" class="auth-form">
                @csrf
                <div class="form-group">
                    <label for="name">お名前</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
                    @error('name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="email">メールアドレス（任意）</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}">
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="password">パスワード</label>
                    <input type="password" id="password" name="password" required>
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="password_confirmation">パスワード（確認）</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="auth-button">登録する</button>
                </div>
                <div class="form-group">
                    <a href="{{ route('login') }}" class="login-link">ログインはこちら</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 