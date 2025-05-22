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
                        @if($profile && $profile->image_path)
                            <img src="{{ asset('storage/' . $profile->image_path) }}" alt="プロフィール画像">
                        @endif
                    </div>
                    <h1 class="user-profile__name">{{ $profile ? $profile->username : 'ユーザー名' }}</h1>
                    <div class="user-profile__edit">
                        <a href="/mypage/profile" class="edit-button" role="button" aria-label="プロフィールを編集">
                            プロフィールを編集
                        </a>
                    </div>
                </section>

                <nav class="content-tabs">
                    <a href="{{ route('mypage', ['page' => 'sell']) }}" 
                       class="content-tabs__link content-tabs__link--sell {{ $page === 'sell' ? 'active' : '' }}">
                        <span class="content-tabs__text">出品した商品</span>
                    </a>
                    <img class="content-tabs__divider" src="{{ asset('img/line-2.svg') }}" alt="" />
                    <a href="{{ route('mypage', ['page' => 'buy']) }}" 
                       class="content-tabs__link content-tabs__link--buy {{ $page === 'buy' ? 'active' : '' }}">
                        <span class="content-tabs__text">購入した商品</span>
                    </a>
                </nav>

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
                        <h2 class="visually-hidden">{{ $page === 'sell' ? '出品した商品一覧' : '購入した商品一覧' }}</h2>
                        <ul class="product-grid">
                            @foreach($items as $item)
                                <li class="product-card {{ $item->status === 'sold' ? 'sold' : '' }}">
                                    <a href="{{ route('item.show', $item->id) }}" class="product-card__link">
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
                                            @if($page === 'sell' && $item->status === 'sold')
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