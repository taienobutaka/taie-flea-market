<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品出品</title>
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sell.css') }}">
</head>
<body>
    <header class="toppage-header">
        <a href="/" class="toppage-header-logo">
            <img src="{{ asset('images/logo.png') }}" alt="Fleamarket Logo" class="toppage-header-logo-img">
        </a>

        <div class="search-bar">
            <input type="text" class="search-input" placeholder="商品を検索する">
            <div class="search-container">商品を検索する</div>
        </div>

        <nav class="toppage-header-nav">
            <a href="{{ route('logout') }}" class="nav-item">ログアウト</a>
            <a href="{{ route('mypage') }}" class="nav-item">マイページ</a>
            <a href="{{ route('sell.form') }}" class="nav-item">出品</a>
        </nav>
    </header>

    <main class="sell-container">
        <h1 class="sell-title">商品の出品</h1>
        <!-- ここに商品出品フォームが入ります -->
    </main>
</body>
</html> 