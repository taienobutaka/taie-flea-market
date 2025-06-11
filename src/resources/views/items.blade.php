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
        <div class="container">
            <header class="header">
                <div class="header__logo">
                    <img src="{{ asset('img/logo.svg') }}" alt="Logo" class="header__logo-img">
                </div>
                <div class="header__search">
                    <form action="{{ route('items.index') }}" method="GET" class="header__search-form">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="なにをお探しですか？" class="header__search-input">
                        <input type="hidden" name="page" value="{{ $page }}">
                        <button type="submit" class="header__search-button">検索</button>
                    </form>
                </div>
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
                <h1 class="visually-hidden">商品一覧</h1>

                <nav class="category-nav" aria-label="商品表示切り替え">
                    <img class="category-nav__line" src="{{ asset('img/line-2.svg') }}" alt="" />
                    <a href="{{ route('items.index', ['page' => 'recommended']) }}" 
                       class="category-nav__tab {{ $page === 'recommended' ? 'category-nav__tab--active' : '' }}"
                       aria-current="{{ $page === 'recommended' ? 'page' : 'false' }}">おすすめ</a>
                    <a href="{{ route('items.index', ['page' => 'mylist']) }}" 
                       class="category-nav__tab category-nav__tab--mylist {{ $page === 'mylist' ? 'category-nav__tab--active' : '' }}"
                       aria-current="{{ $page === 'mylist' ? 'page' : 'false' }}">マイリスト</a>
                </nav>

                @if(request('search'))
                    <div class="search-results" role="status">
                        @if($items->isEmpty())
                            <p>「<span class="search-results__query">{{ request('search') }}</span>」に一致する商品が見つかりませんでした。</p>
                        @else
                            <p>「<span class="search-results__query">{{ request('search') }}</span>」の検索結果: <span class="results-count">{{ $items->count() }}</span>件</p>
                            @if($page === 'recommended' && Auth::check())
                                <p class="search-results__note">※検索結果の商品は自動的にお気に入りに追加されました。</p>
                            @endif
                        @endif
                    </div>
                @endif

                <section class="products" aria-label="商品一覧">
                    @if($items->isEmpty())
                        <div class="no-items">
                            <h2 class="no-items__title">表示できる商品がありません</h2>
                            <p class="no-items__description">
                                @if($page === 'mylist')
                                    お気に入り商品はまだありません。
                                @elseif(Auth::check())
                                    商品を出品するには<a href="{{ route('sell.form') }}" class="no-items__link">こちら</a>をクリックしてください。
                                @else
                                    商品を購入するには<a href="{{ route('login') }}" class="no-items__link">ログイン</a>してください。
                                @endif
                            </p>
                        </div>
                    @else
                        <ul class="product-list">
                            @foreach($items as $item)
                                <li class="product-card {{ $item->status === 'sold' ? 'sold' : '' }}">
                                    <div class="product-card__header">
                                        <a href="{{ route('item.show', $item->id) }}" class="product-card__link">
                                            <div class="product-card__image">
                                                @if($item->image_path)
                                                    <img src="@imageUrl($item->image_path)" 
                                                         alt="{{ $item->name }}" 
                                                         class="product-card__img"
                                                         onerror="this.onerror=null; this.src='{{ asset('img/no-image.png') }}';">
                                                @else
                                                    <img src="{{ asset('img/no-image.png') }}" 
                                                         alt="No Image" 
                                                         class="product-card__img">
                                                @endif
                                                @if($item->status === 'sold')
                                                    <div class="product-card__sold" aria-label="売り切れ">SOLD</div>
                                                @endif
                                            </div>
                                            <div class="product-card__info">
                                                <h2 class="product-card__name">{{ $item->name }}</h2>
                                            </div>
                                        </a>
                                        @auth
                                            <form action="{{ route('favorites.toggle', $item->id) }}" method="POST" class="product-card__favorite">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ $page }}">
                                                <input type="hidden" name="search" value="{{ request('search') }}">
                                                <button type="submit" 
                                                        class="product-card__favorite-button {{ $item->isFavoritedBy(Auth::user()) ? 'product-card__favorite-button--active' : '' }}" 
                                                        aria-label="{{ $item->isFavoritedBy(Auth::user()) ? 'お気に入りから削除' : 'お気に入りに追加' }}">
                                                    <i class="product-card__favorite-icon {{ $item->isFavoritedBy(Auth::user()) ? 'fas' : 'far' }} fa-heart"></i>
                                                </button>
                                            </form>
                                        @endauth
                                    </div>
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