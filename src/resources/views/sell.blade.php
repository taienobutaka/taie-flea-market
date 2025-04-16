<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品出品</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sell.css') }}">
</head>
<body>
    <header class="toppage-header">
        <div class="toppage-header-icon">
            <img src="{{ asset('img/logo.svg') }}" alt="Logo" class="toppage-header-logo-img">
        </div>
        <div class="search-bar">
            <div class="search-container">なにをお探しですか？</div>
            <input type="text" placeholder="" class="search-input">
        </div>
        <nav class="toppage-header-nav">
            <div class="nav-item">ログアウト</div>
            <div class="nav-item">マイページ</div>
            <div class="nav-item">出品</div>
        </nav>
    </header>

    <main class="sell-container">
        <h1 class="sell-title">商品の出品</h1>
        <!-- ここに商品出品フォームが入ります -->
    </main>
</body>
</html>