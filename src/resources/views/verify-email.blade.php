<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メールアドレスの確認</title>
</head>
<body>
    <p>以下のリンクをクリックして、メールアドレスの確認を完了してください。</p>
    <p>
        <a href="{{ $url }}">メールアドレスを確認する</a>
    </p>
    <hr>
    <p>初回ログインはこちらから:</p>
    <p>
        <a href="{{ url('/create-profile') }}">プロフィール設定画面へ</a>
    </p>
</body>
</html>