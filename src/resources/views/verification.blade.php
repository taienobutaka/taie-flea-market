<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メール認証誘導</title>
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/verification.css') }}">
</head>
<body>
    <div class="auth-header">
        <img class="logo" src="{{ asset('img/logo.svg') }}" alt="ロゴ">
    </div>
    <div class="auth-content">
        <p class="auth-text">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>
        <a href="http://localhost:8025/#" class="auth-button">認証はこちらから</a>
        <form action="{{ route('verification.resend') }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="resend-link">認証メールを再送する</button>
        </form>
    </div>
</body>
</html>