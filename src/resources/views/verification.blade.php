<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メール認証</title>
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/verification.css') }}">
</head>
<body>
    <header class="auth-header">
        <img class="logo" src="{{ asset('img/logo.svg') }}" alt="ロゴ">
    </header>
    <main class="auth-content">
        <p class="auth-text">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            <span class="gap"></span>メール認証を完了してください。
        </p>
        <a href="http://localhost:8025" class="auth-button" role="button">認証はこちらから</a>
        <form action="{{ route('verification.send') }}" method="POST" class="resend-form">
            @csrf
            <button type="submit" class="resend-link">認証メールを再送する</button>
        </form>
    </main>
</body>
</html>