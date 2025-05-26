<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品詳細</title>
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/detail.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="screen">
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
                    @auth
                        <li class="header__nav-item">
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="header__nav-link header__nav-link--logout">ログアウト</a>
                        </li>
                        <li class="header__nav-item">
                            <a href="{{ route('mypage') }}" class="header__nav-link">マイページ</a>
                        </li>
                        <li class="header__nav-item">
                            <a href="{{ route('sell.form') }}" class="header__nav-link header__nav-link--sell">出品</a>
                        </li>
                    @else
                        <li class="header__nav-item">
                            <a href="{{ route('login') }}" class="header__nav-link header__nav-link--logout">ログアウト</a>
                        </li>
                        <li class="header__nav-item">
                            <a href="{{ route('login') }}" class="header__nav-link">マイページ</a>
                        </li>
                        <li class="header__nav-item">
                            <a href="{{ route('login') }}" class="header__nav-link header__nav-link--sell">出品</a>
                        </li>
                    @endauth
                </ul>
            </nav>
        </header>

        <main class="detail">
            @if($guestMessage)
                <div class="detail__message">
                    <p>{{ $guestMessage }}</p>
                </div>
            @endif

            <article class="detail__content">
                <section class="detail__image">
                    @if($item->image_path)
                        <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}" class="detail__product-image">
                    @else
                        <div class="detail__no-image">商品画像</div>
                    @endif
                </section>

                <section class="detail__info">
                    <h1 class="detail__title">{{ $item->name }}</h1>
                    <p class="detail__brand">{{ $item->brand ?? 'ブランド名' }}</p>
                    <div class="detail__price-container">
                        <span class="detail__price-symbol">¥</span>
                        <span class="detail__price-amount">
                            @php
                                $formatted = number_format($item->price);
                                $parts = explode(',', $formatted);
                                echo $parts[0];
                                for ($i = 1; $i < count($parts); $i++) {
                                    echo '<span class="comma">,</span>' . $parts[$i];
                                }
                            @endphp
                        </span>
                        <span class="detail__price-tax">(税込)</span>
                    </div>

                    <div class="detail__actions">
                        @auth
                            <form action="{{ route('favorites.toggle', $item->id) }}" method="POST" class="detail__favorite-form">
                                @csrf
                                <button type="submit" class="detail__favorite-button {{ $item->isFavoritedBy(Auth::user()) ? 'detail__favorite-button--active' : '' }}">
                                    <img src="{{ asset('img/star.png') }}" alt="お気に入り" class="detail__favorite-icon">
                                </button>
                                <span class="detail__favorite-count {{ $item->isFavoritedBy(Auth::user()) ? 'detail__favorite-count--active' : '' }}">
                                    {{ $item->favorites->count() }}
                                </span>
                            </form>
                        @else
                            <div class="detail__favorite-form">
                                <a href="{{ route('login') }}" class="detail__favorite-button" title="お気に入り登録するにはログインが必要です">
                                    <img src="{{ asset('img/star.png') }}" alt="お気に入り" class="detail__favorite-icon">
                                </a>
                                <span class="detail__favorite-count">{{ $item->favorites->count() }}</span>
                            </div>
                        @endauth
                        <div class="detail__comment-count">
                            <img src="{{ asset('img/comment.png') }}" alt="コメント" class="detail__comment-icon">
                            <span class="detail__count">{{ $item->comments->count() }}</span>
                        </div>
                    </div>

                    <section class="detail__purchase">
                        @auth
                            <form action="{{ route('purchase', $item->id) }}" method="GET" class="detail__purchase-form">
                                <button type="submit" class="detail__purchase-button">
                                    <span class="detail__purchase-text">購入手続きへ</span>
                                </button>
                            </form>
                            @if(session('error'))
                                <div class="detail__error">{{ session('error') }}</div>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="detail__purchase-button">
                                <span class="detail__purchase-text">購入手続きへ</span>
                            </a>
                        @endauth
                    </section>

                    <section class="detail__description">
                        <h2 class="detail__section-title">商品説明</h2>
                        <p class="detail__description-text">{{ $item->description }}</p>
                    </section>

                    <section class="detail__meta">
                        <h2 class="detail__section-title">商品の情報</h2>
                        <dl class="detail__meta-list">
                            <div class="detail__meta-item">
                                <dt class="detail__meta-label">カテゴリー</dt>
                                <dd class="detail__meta-tags">
                                    @foreach($categories as $category)
                                        <span class="detail__tag">{{ $category }}</span>
                                    @endforeach
                                </dd>
                            </div>
                            <div class="detail__meta-item">
                                <dt class="detail__meta-label">商品の状態</dt>
                                <dd class="detail__meta-value">{{ $item->condition }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="detail__comments">
                        <h2 class="detail__section-title">コメント({{ $item->comments->count() }})</h2>
                        
                        <ul class="detail__comments-list">
                            @foreach($item->comments as $comment)
                            <li class="detail__comment-item">
                                <div class="detail__comment-user">
                                    <div class="detail__user-avatar">
                                        @if($comment->user->profile && $comment->user->profile->image_path)
                                            <img src="{{ asset('storage/' . $comment->user->profile->image_path) }}" alt="{{ $comment->user->profile->username }}">
                                        @else
                                            <img src="{{ asset('img/no-image.png') }}" alt="{{ $comment->user->profile->username ?? 'ユーザー' }}">
                                        @endif
                                    </div>
                                    <div class="detail__user-name">{{ $comment->user->profile->username ?? 'ユーザー' }}</div>
                                </div>
                                <p class="detail__comment-text">{{ $comment->content }}</p>
                            </li>
                            @endforeach
                        </ul>

                        @auth
                            <section class="detail__comment-form">
                                <h3 class="detail__form-title">商品へのコメント</h3>
                                <form action="{{ route('comment.store', ['item_id' => $item->id]) }}" method="POST" novalidate>
                                    @csrf
                                    <textarea name="content" class="detail__comment-input {{ $errors->has('content') ? 'detail__comment-input--error' : '' }}">{{ old('content') }}</textarea>
                                    @error('content')
                                        <div class="detail__error detail__error--above-button">{{ $message }}</div>
                                    @enderror
                                    <button type="submit" class="detail__submit-button">コメントを送信する</button>
                                </form>
                            </section>
                        @else
                            <section class="detail__comment-form">
                                <h3 class="detail__form-title">商品へのコメント</h3>
                                <p class="detail__login-message">
                                    コメントを投稿するには<a href="{{ route('login') }}" class="detail__action-link">ログイン</a>が必要です。
                                </p>
                            </section>
                        @endauth
                    </section>
                </section>
            </article>
        </main>
    </div>
</body>
</html> 