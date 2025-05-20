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
                        <input type="hidden" name="view" value="{{ $view }}">
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
                    <a href="{{ route('items.index', ['view' => 'recommended', 'search' => request('search')]) }}" 
                       class="tab-link {{ $view === 'recommended' ? 'active' : '' }}"
                       aria-current="{{ $view === 'recommended' ? 'page' : 'false' }}">おすすめ</a>
                    <a href="{{ route('items.index', ['view' => 'favorites', 'search' => request('search')]) }}" 
                       class="tab-link {{ $view === 'favorites' ? 'active' : '' }}"
                       aria-current="{{ $view === 'favorites' ? 'page' : 'false' }}">マイリスト</a>
                    <div class="tab-underline"></div>
                </nav>

                <!-- 検索結果メッセージ -->
                @if(request('search'))
                    <div class="search-results-message" role="status">
                        @if($items->isEmpty())
                            <p>「<span class="search-query">{{ request('search') }}</span>」に一致する商品が見つかりませんでした。</p>
                        @else
                            <p>「<span class="search-query">{{ request('search') }}</span>」の検索結果: <span class="results-count">{{ $items->count() }}</span>件</p>
                        @endif
                    </div>
                @endif

                <!-- 商品一覧 -->
                <section class="products-container" aria-label="商品一覧">
                    @if($items->isEmpty())
                        <div class="no-items-message">
                            <h2 class="no-items-title">表示できる商品がありません</h2>
                            <p class="no-items-description">
                                @if($view === 'favorites')
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
                                <a href="{{ route('item.show', ['id' => $item->id]) }}" class="product-link">
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