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
    <div class="register-container">
        <header class="register-header">
            <img class="register-logo" src="{{ asset('img/logo.svg') }}" alt="ロゴ">
        </header>
        <main class="register-main">
            <h1 class="register-title">会員登録</h1>
            <form method="POST" action="{{ route('register') }}" class="register-form">
                @csrf
                <div class="form-group">
                    <label for="name" class="form-label">お名前</label>
                    <input type="text" id="name" name="name" class="form-input" value="{{ old('name') }}" required autofocus>
                    @error('name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="email" class="form-label">メールアドレス</label>
                    <input type="email" id="email" name="email" class="form-input" value="{{ old('email') }}" required>
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="password" class="form-label">パスワード</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">パスワード（確認）</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="register-button">登録する</button>
                </div>
                <div class="form-group">
                    <a href="{{ route('login') }}" class="login-link">ログインはこちら</a>
                </div>
            </form>
        </main>
    </div>
</body>
</html> 