<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン</title>
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>
<body>
    <div class="auth-header">
        <img class="logo" src="{{ asset('img/logo.svg') }}" alt="ロゴ">
    </div>
    <div class="auth-content">
        <h1>ログイン</h1>
        <form action="{{ route('login.post') }}" method="POST" novalidate>
            @csrf
            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}">
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
            @error('login')
                <p style="color: red;">{{ $message }}</p>
            @enderror
            <button type="submit" class="register-button">ログインする</button>
        </form>
        <a href="{{ route('register.form') }}" class="login-link">会員登録はこちら</a>
    </div>
</body>
</html>
