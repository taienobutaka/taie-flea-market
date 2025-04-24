<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>マイページ</title>
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mypage.css') }}">
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

            <div class="user-info">
                <div class="ellipse">
                    @if($profile && $profile->image_path)
                        <img src="{{ asset('storage/' . $profile->image_path) }}" alt="プロフィール画像">
                    @endif
                </div>
                <div class="text-wrapper-6">{{ $profile ? $profile->username : 'ユーザー名' }}</div>
                <div class="overlap-group-wrapper">
                    <a href="{{ route('profile.edit') }}" class="overlap-group-2">
                        <div class="text-wrapper-5">プロフィールを編集</div>
                        <div class="rectangle-2"></div>
                    </a>
                </div>
            </div>

            <div class="toppage-list">
                <div class="text-wrapper-3">出品した商品</div>
                <div class="text-wrapper-4">購入した商品</div>
                <img class="line" src="{{ asset('img/line-2.svg') }}" />
            </div>

            <div class="product-sample-data">
                <div class="products-row-2">
                    <div class="product-image-4">
                        <div class="group">
                            <div class="overlap-group">
                                <div class="rectangle"></div>
                                <div class="text-wrapper">商品画像</div>
                            </div>
                        </div>
                        <div class="text-wrapper-2">商品名</div>
                    </div>
                    <div class="product-image">
                        <div class="group">
                            <div class="overlap-group">
                                <div class="rectangle"></div>
                                <div class="text-wrapper">商品画像</div>
                            </div>
                        </div>
                        <div class="text-wrapper-2">商品名</div>
                    </div>
                    <div class="product-image-2">
                        <div class="group">
                            <div class="overlap-group">
                                <div class="rectangle"></div>
                                <div class="text-wrapper">商品画像</div>
                            </div>
                        </div>
                        <div class="text-wrapper-2">商品名</div>
                    </div>
                    <div class="product-image-3">
                        <div class="group">
                            <div class="overlap-group">
                                <div class="rectangle"></div>
                                <div class="text-wrapper">商品画像</div>
                            </div>
                        </div>
                        <div class="text-wrapper-2">商品名</div>
                    </div>
                </div>
                <div class="products-row">
                    <div class="product-image-3">
                        <div class="group">
                            <div class="overlap-group">
                                <div class="rectangle"></div>
                                <div class="text-wrapper">商品画像</div>
                            </div>
                        </div>
                        <div class="text-wrapper-2">商品名</div>
                    </div>
                    <div class="product-image-2">
                        <div class="group">
                            <div class="overlap-group">
                                <div class="rectangle"></div>
                                <div class="text-wrapper">商品画像</div>
                            </div>
                        </div>
                        <div class="text-wrapper-2">商品名</div>
                    </div>
                    <div class="product-image">
                        <div class="group">
                            <div class="overlap-group">
                                <div class="rectangle"></div>
                                <div class="text-wrapper">商品画像</div>
                            </div>
                        </div>
                        <div class="text-wrapper-2">商品名</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 