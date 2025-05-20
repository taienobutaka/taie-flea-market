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
                    <form action="{{ route('logout') }}" method="POST" class="logout-form">
                        @csrf
                        <button type="submit" class="nav-item logout-button">ログアウト</button>
                    </form>
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
                    <a href="{{ route('profile.edit') }}" class="overlap-group-2" role="button" aria-label="プロフィールを編集">
                        <div class="text-wrapper-5">プロフィールを編集</div>
                        <div class="rectangle-2"></div>
                    </a>
                </div>
            </div>

            <div class="toppage-list">
                <a href="{{ route('mypage', ['tab' => 'selling']) }}" 
                   class="tab-link {{ $activeTab === 'selling' ? 'active' : '' }}">
                    <div class="text-wrapper-3">出品した商品</div>
                </a>
                <img class="line" src="{{ asset('img/line-2.svg') }}" />
                <a href="{{ route('mypage', ['tab' => 'purchased']) }}" 
                   class="tab-link {{ $activeTab === 'purchased' ? 'active' : '' }}">
                    <div class="text-wrapper-4">購入した商品</div>
                </a>
            </div>

            <div class="product-sample-data">
                @if($items->isEmpty())
                    <div class="no-items">
                        @if($activeTab === 'selling')
                            <p class="no-items-message">出品した商品はありません</p>
                        @else
                            <p class="no-items-message">購入した商品はありません</p>
                        @endif
                    </div>
                @else
                    <section class="products-container" aria-label="商品一覧">
                        <ul class="products-row">
                            @foreach($items as $item)
                                <li class="product-card {{ $item->status === 'sold' ? 'sold' : '' }}">
                                    <a href="{{ route('item.show', $item->id) }}" class="product-link">
                                        <div class="product-image">
                                            @if($item->image_path)
                                                <img src="{{ asset('storage/' . $item->image_path) }}" 
                                                     alt="{{ $item->name }}" 
                                                     class="product-img"
                                                     onerror="this.onerror=null; this.src='{{ asset('img/no-image.png') }}';">
                                            @else
                                                <img src="{{ asset('img/no-image.png') }}" 
                                                     alt="No Image" 
                                                     class="product-img">
                                            @endif
                                            @if($activeTab === 'selling' && $item->status === 'sold')
                                                <div class="sold-label" aria-label="売り切れ">SOLD</div>
                                            @elseif($activeTab === 'purchased')
                                                <div class="sold-label" aria-label="購入済み">購入済み</div>
                                            @endif
                                        </div>
                                        <div class="product-info">
                                            <h3 class="product-name">{{ $item->name }}</h3>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </div>
        </div>
    </div>
</body>
</html> 