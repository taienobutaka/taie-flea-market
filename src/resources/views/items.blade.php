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
            <!-- ヘッダーセクション -->
            <header class="toppage-header">
                <div class="toppage-header-icon">
                    <img src="{{ asset('img/logo.svg') }}" alt="Logo" class="toppage-header-logo-img">
                </div>
                <div class="search-bar">
                    <form action="{{ route('items.index') }}" method="GET" class="search-form">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="なにをお探しですか？" class="search-input">
                        <input type="hidden" name="page" value="{{ $page }}">
                        <button type="submit" class="search-button">検索</button>
                    </form>
                </div>
                <nav class="toppage-header-nav">
                    <form action="{{ route('logout') }}" method="POST" class="logout-form">
                        @csrf
                        <button type="submit" class="nav-item">ログアウト</button>
                    </form>
                    <a href="{{ route('mypage') }}" class="nav-item">マイページ</a>
                    <a href="{{ route('sell.form') }}" class="nav-item">出品</a>
                </nav>
            </header>

            <!-- メインコンテンツ -->
            <main class="main-content">
                <h1 class="visually-hidden">商品一覧</h1>

                <!-- ナビゲーションタブ -->
                <nav class="toppage-list" aria-label="商品表示切り替え">
                    <img class="line" src="{{ asset('img/line-2.svg') }}" alt="" />
                    <a href="{{ route('items.index', ['page' => 'recommended']) }}" 
                       class="tab-link {{ $page === 'recommended' ? 'active' : '' }}"
                       aria-current="{{ $page === 'recommended' ? 'page' : 'false' }}">おすすめ</a>
                    <a href="{{ route('items.index', ['page' => 'mylist']) }}" 
                       class="tab-link mylist-tab {{ $page === 'mylist' ? 'active' : '' }}"
                       aria-current="{{ $page === 'mylist' ? 'page' : 'false' }}">マイリスト</a>
                    <div class="tab-underline"></div>
                </nav>

                <!-- 検索結果メッセージ -->
                @if(request('search'))
                    <div class="search-results-message" role="status">
                        @if($items->isEmpty())
                            <p>「<span class="search-query">{{ request('search') }}</span>」に一致する商品が見つかりませんでした。</p>
                        @else
                            <p>「<span class="search-query">{{ request('search') }}</span>」の検索結果: <span class="results-count">{{ $items->count() }}</span>件</p>
                            @if($page === 'recommended' && Auth::check())
                                <p class="search-note">※検索結果の商品は自動的にお気に入りに追加されました。</p>
                            @endif
                        @endif
                    </div>
                @endif

                <!-- 商品一覧 -->
                <section class="products-container" aria-label="商品一覧">
                    @if($items->isEmpty())
                        <div class="no-items-message">
                            <h2 class="no-items-title">表示できる商品がありません</h2>
                            <p class="no-items-description">
                                @if($page === 'mylist')
                                    お気に入り商品はまだありません。
                                @elseif(Auth::check())
                                    商品を出品するには<a href="{{ route('sell.form') }}" class="action-link">こちら</a>をクリックしてください。
                                @else
                                    商品を購入するには<a href="{{ route('login') }}" class="action-link">ログイン</a>してください。
                                @endif
                            </p>
                        </div>
                    @else
                        <ul class="products-row">
                            @foreach($items as $item)
                                <li class="product-card {{ $item->status === 'sold' ? 'sold' : '' }}">
                                    <div class="product-header">
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
                                                @if($item->status === 'sold')
                                                    <div class="sold-label" aria-label="売り切れ">SOLD</div>
                                                @endif
                                            </div>
                                            <div class="product-info">
                                                <h3 class="product-name">{{ $item->name }}</h3>
                                            </div>
                                        </a>
                                        @auth
                                            <form action="{{ route('favorites.toggle', $item->id) }}" method="POST" class="favorite-form">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ $page }}">
                                                <input type="hidden" name="search" value="{{ request('search') }}">
                                                <button type="submit" class="favorite-button {{ $item->isFavoritedBy(Auth::user()) ? 'favorited' : '' }}" 
                                                        aria-label="{{ $item->isFavoritedBy(Auth::user()) ? 'お気に入りから削除' : 'お気に入りに追加' }}">
                                                    <i class="favorite-icon {{ $item->isFavoritedBy(Auth::user()) ? 'fas' : 'far' }} fa-heart"></i>
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

    <style>
    .product-header {
        position: relative;
        display: flex;
        flex-direction: column;
    }

    .favorite-form {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 1;
    }

    .favorite-button {
        background: none;
        border: none;
        padding: 8px;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .favorite-button:hover {
        transform: scale(1.1);
    }

    .favorite-icon {
        font-size: 24px;
        color: #ff4b4b;
    }

    .favorite-button.favorited .favorite-icon {
        color: #ff4b4b;
    }

    .favorite-button:not(.favorited) .favorite-icon {
        color: #ccc;
    }

    .search-note {
        font-size: 0.9em;
        color: #666;
        margin-top: 0.5em;
    }
    </style>
</body>
</html>