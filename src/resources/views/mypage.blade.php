<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>マイページ</title>
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mypage.css') }}?v={{ time() }}">
</head>
<body>
    <div class="screen">
        <div class="container">
            <header class="header">
                <div class="header__logo">
                    <img src="{{ asset('img/logo.svg') }}" alt="Logo" class="header__logo-img">
                </div>
                <form action="{{ route('items.index') }}" method="GET" class="header__search">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="なにをお探しですか？" class="header__search-input">
                    <input type="hidden" name="page" value="recommended">
                </form>
                <nav class="header__nav">
                    <form action="{{ route('logout') }}" method="POST" class="header__nav-form">
                        @csrf
                        <button type="submit" class="header__nav-button">ログアウト</button>
                    </form>
                    <a href="{{ route('mypage') }}" class="header__nav-item">マイページ</a>
                    <a href="{{ route('sell.form') }}" class="header__nav-item header__nav-item--sell">出品</a>
                </nav>
            </header>

            <main class="main-content">
                <section class="user-profile">
                    <div class="user-profile__image">
                        @if(isset($profile) && $profile && $profile->image_path)
                            <img src="{{ asset('storage/' . $profile->image_path) }}" alt="プロフィール画像">
                        @endif
                    </div>
                    <h1 class="user-profile__name">{{ $profile->username ?? 'ユーザー名' }}</h1>
                    <div class="user-profile__stars-html">
                        <div>
                            @php 
                                // ユーザーが出品者か購入者かを判定
                                $isSeller = isset($profile) && $profile && isset($profile->user_id);
                                $hasSoldItems = \App\Models\Item::where('user_id', $profile->user_id ?? 0)->exists();
                                $hasBoughtItems = \App\Models\Chat::where('user_id', $profile->user_id ?? 0)->whereNotNull('rating')->exists();
                                
                                // 評価を決定
                                if ($hasSoldItems && $hasBoughtItems) {
                                    // 両方の場合は、より多くの評価がある方を表示
                                    $sellerRatingCount = $ratingCount ?? 0;
                                    $buyerRatingCount = $ratingCountBuyer ?? 0;
                                    if ($sellerRatingCount >= $buyerRatingCount) {
                                        $avg = $ratingAvg ?? 0;
                                    } else {
                                        $avg = $ratingAvgBuyer ?? 0;
                                    }
                                } elseif ($hasSoldItems) {
                                    // 出品者のみの場合
                                    $avg = $ratingAvg ?? 0;
                                } elseif ($hasBoughtItems) {
                                    // 購入者のみの場合
                                    $avg = $ratingAvgBuyer ?? 0;
                                } else {
                                    // どちらでもない場合
                                    $avg = 0;
                                }
                                
                                // デバッグ情報をログに記録
                                \Log::info('マイページ星マーク表示', [
                                    'page' => $page,
                                    'user_id' => $profile->user_id ?? 0,
                                    'has_sold_items' => $hasSoldItems,
                                    'has_bought_items' => $hasBoughtItems,
                                    'rating_avg' => $ratingAvg ?? 0,
                                    'rating_avg_buyer' => $ratingAvgBuyer ?? 0,
                                    'display_avg' => $avg
                                ]);
                            @endphp
                            @for($i = 1; $i <= 5; $i++)
                                <span class="star-html{{ $i <= $avg ? ' selected' : '' }}">&#9733;</span>
                            @endfor
                        </div>
                    </div>
                    <div class="user-profile__edit">
                        <a href="/mypage/profile" class="edit-button" role="button" aria-label="プロフィールを編集">
                            プロフィールを編集
                        </a>
                    </div>
                </section>

                @if($page === 'trade')
                <div class="trade-label">
                  <span class="trade-label__text"></span>
                </div>
                @endif

                <nav class="content-tabs">
                    <a href="{{ route('mypage', ['page' => 'sell']) }}" 
                       class="content-tabs__link content-tabs__link--sell {{ $page === 'sell' ? 'active' : '' }}">
                        <span class="content-tabs__text">出品した商品</span>
                    </a>
                    <a href="{{ route('mypage', ['page' => 'buy']) }}" 
                       class="content-tabs__link content-tabs__link--buy {{ $page === 'buy' ? 'active' : '' }}">
                        <span class="content-tabs__text">購入した商品</span>
                    </a>
                    <a href="{{ route('mypage.trade') }}"
                       class="content-tabs__link content-tabs__link--trade {{ $page === 'trade' ? 'active' : '' }}">
                        <span class="content-tabs__text">取引中の商品</span>
                        @if($page === 'trade')
                        <span class="trade-message-badge">
                            <span class="trade-message-badge__count">{{ $totalReceived ?? 0 }}</span>
                        </span>
                        @endif
                    </a>
                </nav>
                <div class="content-tabs-underline"></div>

                <section class="product-list">
                    @if($items->isEmpty())
                        <div class="empty-state">
                            @if($page === 'sell')
                                <p class="empty-state__message">出品した商品はありません</p>
                            @else
                                <p class="empty-state__message">購入した商品はありません</p>
                            @endif
                        </div>
                    @else
                        <h2 class="visually-hidden">{{ $page === 'sell' ? '出品した商品一覧' : ($page === 'buy' ? '購入した商品一覧' : '取引中の商品一覧') }}</h2>
                        <ul class="product-grid">
                            @foreach($items as $item)
                                <li class="product-card {{ $item->status === 'sold' ? 'sold' : '' }}">
                                    @php
                                        $isTradeTab = ($page === 'trade');
                                        $isSeller = isset($profile) && $profile && $item->user_id === $profile->user_id;
                                        if ($isTradeTab) {
                                            $link = $isSeller
                                                ? route('seller.chat', ['item_id' => $item->id])
                                                : route('purchaser.chat', ['item_id' => $item->id]);
                                        } else {
                                            $link = route('item.show', $item->id);
                                        }
                                    @endphp
                                    <a href="{{ $link }}" class="product-card__link">
                                        <div class="product-card__image">
                                            @if($item->image_path)
                                                <img src="{{ asset('storage/' . $item->image_path) }}" 
                                                     alt="{{ $item->name }}" 
                                                     class="product-card__img">
                                            @else
                                                <img src="{{ asset('img/no-image.png') }}" 
                                                     alt="No Image"
                                                     class="product-card__img">
                                            @endif
                                            @if($item->status === 'sold')
                                                <div class="product-card__status" aria-label="売り切れ">SOLD</div>
                                            @endif
                                            @if($isTradeTab && isset($messageCounts[$item->id]))
                                                <span class="product-message-badge">
                                                    <span class="product-message-badge__count">{{ $messageCounts[$item->id] }}</span>
                                                </span>
                                            @endif
                                        </div>
                                        <div class="product-card__info">
                                            <h3 class="product-card__name">{{ $item->name }}</h3>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            </main>
        </div>
    </div>
</body>
</html>