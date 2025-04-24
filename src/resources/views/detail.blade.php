<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品詳細</title>
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/detail.css') }}">
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

            <div class="detail-container">
                <div class="detail-content">
                    <div class="label">
                        @if($item->image_path)
                            <img src="/storage/{{ $item->image_path }}" alt="{{ $item->name }}" class="product-image">
                        @else
                            <div class="text-wrapper">商品画像</div>
                        @endif
                    </div>

                    <div class="detail-info">
                        <div class="detail-title">
                            <h1 class="item-name">{{ $item->name }}</h1>
                            <p class="item-brand">{{ $item->brand }}</p>
                            <p class="item-price">
                                <span class="currency">¥</span>
                                <span class="price">{{ number_format($item->price) }}</span>
                                <span class="tax-included">(税込)</span>
                            </p>
                            <div class="item-actions">
                                <div class="like-count">
                                    <img src="{{ asset('img/star.png') }}" alt="いいね" class="like-icon">
                                    <span class="count">3</span>
                                </div>
                                <div class="comment-count">
                                    <img src="{{ asset('img/coment.png') }}" alt="コメント" class="comment-icon">
                                    <span class="count">1</span>
                                </div>
                            </div>
                        </div>

                        <div class="purchase-area">
                            <a href="{{ route('purchase', $item->id) }}" class="purchase-button">
                                <span class="purchase-button-text">購入手続きへ</span>
                            </a>
                        </div>

                        <div class="detail-description">
                            <h2 class="section-title">商品説明</h2>
                            <p class="description-text">{{ $item->description }}</p>
                        </div>

                        <div class="detail-meta">
                            <h2 class="section-title">商品の情報</h2>
                            <div class="meta-item">
                                <span class="meta-label">カテゴリー</span>
                                <div class="meta-tags">
                                    <span class="tag">洋服</span>
                                    <span class="tag">メンズ</span>
                                </div>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">商品の状態</span>
                                <span class="meta-value">{{ $item->condition }}</span>
                            </div>
                        </div>

                        <div class="comments-section">
                            <h2 class="section-title">コメント(1)</h2>
                            <div class="comments-list">
                                <div class="comment-item">
                                    <div class="comment-user">
                                        <div class="user-avatar">
                                            <img src="/img/default-avatar.png" alt="ユーザーアバター">
                                        </div>
                                        <p class="user-name">admin</p>
                                    </div>
                                    <div class="comment-text">
                                        <p>こちらにコメントが入ります。</p>
                                    </div>
                                </div>
                            </div>

                            <div class="comment-form">
                                <h3 class="form-title">商品へのコメント</h3>
                                <textarea class="comment-input" placeholder="コメントを入力してください"></textarea>
                                <button class="submit-button">コメントを送信する</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 