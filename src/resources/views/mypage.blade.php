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
                    <ul class="header__nav-list">
                        <li class="header__nav-item">
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="header__nav-link header__nav-link--logout">ログアウト</a>
                        </li>
                        <li class="header__nav-item"><a href="{{ route('mypage') }}" class="header__nav-link">マイページ</a></li>
                        <li class="header__nav-item"><a href="{{ route('sell.form') }}" class="header__nav-link header__nav-link--sell">出品</a></li>
                    </ul>
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
                        <div style="display:inline-block;margin-right:16px;">
                            <span style="font-size:14px;color:#888;vertical-align:middle;">出品者としての評価</span><br>
                            @php $avg = $ratingAvg ?? 0; @endphp
                            @for($i = 1; $i <= 5; $i++)
                                <span class="star-html star-html-{{ $i }}" style="color:{{ $i <= $avg ? '#FFF048' : '#D9D9D9' }};font-size:28px;">&#9733;</span>
                            @endfor
                            <span style="font-size:16px;color:#888;margin-left:6px;vertical-align:middle;">({{ $ratingCount ?? 0 }})</span>
                        </div>
                        <div style="display:inline-block;">
                            <span style="font-size:14px;color:#888;vertical-align:middle;">購入者としての評価</span><br>
                            @php $avgBuyer = $ratingAvgBuyer ?? 0; @endphp
                            @for($i = 1; $i <= 5; $i++)
                                <span class="star-html star-html-{{ $i }}" style="color:{{ $i <= $avgBuyer ? '#FFF048' : '#D9D9D9' }};font-size:28px;">&#9733;</span>
                            @endfor
                            <span style="font-size:16px;color:#888;margin-left:6px;vertical-align:middle;">({{ $ratingCountBuyer ?? 0 }})</span>
                        </div>
                    </div>
                    <div class="user-profile__edit">
                        <a href="/mypage/profile" class="edit-button" role="button" aria-label="プロフィールを編集">
                            プロフィールを編集
                        </a>
                    </div>
                </section>

                <div class="trade-label">
                  <span class="trade-label__text">取引中の商品</span>
                </div>

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
                                                     class="product-card__img"
                                                     onerror="this.onerror=null; this.src='{{ asset('img/no-image.png') }}';">
                                            @else
                                                <img src="{{ asset('img/no-image.png') }}" 
                                                     alt="No Image"
                                                     class="product-card__img">
                                            @endif
                                            @if($item->status === 'sold')
                                                <div class="product-card__status" aria-label="売り切れ">SOLD</div>
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