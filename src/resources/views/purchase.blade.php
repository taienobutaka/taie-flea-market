<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品購入</title>
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
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

            <div class="confirm-surface">
                <div class="overlap-group">
                    <div class="text-wrapper">商品代金</div>
                    <p class="div-2">
                        <span class="span">¥</span>
                        <span class="text-wrapper-2">&nbsp;</span>
                        <span class="text-wrapper-3">{{ number_format($item->price) }}</span>
                    </p>
                </div>
                <div class="overlap">
                    <div class="div-2">コンビニ払い</div>
                    <div class="text-wrapper">支払い方法</div>
                </div>
            </div>
            <div class="flexcontainer">
                <p class="text">
                    <span class="text-wrapper-4">〒 {{ $profile->postal_code ?? '未設定' }}<br /></span>
                </p>
                <p class="text"><span class="text-wrapper-4">{{ $profile->address ?? '未設定' }}</span></p>
                <p class="text"><span class="text-wrapper-4">{{ $profile->building ?? '未設定' }}</span></p>
            </div>
            <div class="overlap-2">
                <div class="text-wrapper-5">配送先</div>
                <a href="{{ route('purchase.address', $item->id) }}" class="text-wrapper-6">変更する</a>
                <img class="line" src="{{ asset('img/line-18.svg') }}" />
            </div>
            <div class="flexcontainer-2">
                <p class="p">
                    <span class="text-wrapper-7">{{ $item->name }}<br /></span>
                </p>
                <p class="p"><span class="text-wrapper-8">¥ </span> <span class="text-wrapper-9">{{ number_format($item->price) }}</span></p>
            </div>
            <div class="overlap-3">
                <div class="text-wrapper-5">支払い方法</div>
                <img class="line" src="{{ asset('img/line-19.svg') }}" />
            </div>
            <div class="group">
                <div class="div-wrapper">
                    <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}" class="product-image">
                </div>
            </div>
            <div class="overlap-wrapper">
                <div class="overlap-4">
                    <div class="text-wrapper-11">選択してください</div>
                    <img class="polygon" src="{{ asset('img/polygon-1.svg') }}" />
                    <div class="rectangle"></div>
                </div>
            </div>
            <img class="img" src="{{ asset('img/line-21.svg') }}" />
            <div class="action-bar">
                <form action="{{ route('purchase.confirm', $item->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="text-wrapper-12">購入する</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 