<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品一覧</title>
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/items.css') }}">
</head>
<body>
    <div class="screen">
        <div class="div">
            <div class="toppage-header">
                <div class="toppage-header-icon">
                    <img src="{{ asset('img/logo.svg') }}" alt="Logo" class="toppage-header-logo-img">
                </div>
                <div class="search-bar">
                    <div class="search-container">なにをお探しですか？</div>
                    <input type="text" placeholder="" class="search-input">
                </div>
                <nav class="toppage-header-nav">
                    <a href="{{ route('logout') }}" class="nav-item">ログアウト</a>
                    <a href="{{ route('mypage') }}" class="nav-item">マイページ</a>
                    <a href="{{ route('sell.form') }}" class="nav-item">出品</a>
                </nav>
            </div>

            <div class="toppage-list">
                <img class="line" src="{{ asset('img/line-2.svg') }}" />
                <div class="text-wrapper-7">おすすめ</div>
                <div class="text-wrapper-8">マイリスト</div>
            </div>

            <div class="products-container">
                <div class="products-row">
                    @foreach($items as $item)
                        <div class="item-card">
                            <a href="{{ route('item.show', $item->id) }}" class="item-link">
                                <div class="item-image">
                                    @if($item->image_path)
                                        <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}">
                                    @endif
                                </div>
                                <div class="item-info">
                                    <p class="item-name">{{ $item->name }}</p>
                                    <p class="item-price">¥{{ number_format($item->price) }}</p>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</body>
</html>