<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品出品</title>
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sell.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-logo">
                <img src="{{ asset('img/logo.svg') }}" alt="Logo" class="logo-image">
            </div>
            <div class="search-container">
                <form action="{{ route('items.index') }}" method="GET" class="search-form">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="なにをお探しですか？" class="search-input">
                    <input type="hidden" name="page" value="recommended">
                    <button type="submit" class="search-button" style="display: none;">検索</button>
                </form>
            </div>
            <nav class="header-nav">
                <form action="{{ route('logout') }}" method="POST" class="logout-form">
                    @csrf
                    <button type="submit" class="nav-button logout-button">ログアウト</button>
                </form>
                <a href="{{ route('mypage') }}" class="nav-link">マイページ</a>
                <a href="{{ route('sell.form') }}" class="nav-link">出品</a>
            </nav>
        </header>

        <main class="main-content">
            <h1 class="page-title">商品の出品</h1>

            <section class="image-section">
                <h2 class="section-title">商品画像</h2>
                <div class="image-upload-container">
                    @if (session('imagePath'))
                        <div class="preview-container">
                            <img src="{{ asset('storage/' . session('imagePath')) }}" alt="商品画像" class="preview-image">
                            <form action="{{ route('sell.remove-image') }}" method="POST" class="remove-form">
                                @csrf
                                <button type="submit" class="remove-button">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    @else
                        <form action="{{ route('sell.upload-image') }}" method="POST" enctype="multipart/form-data" class="upload-form">
                            @csrf
                            <label for="imageInput" class="upload-label">
                                <div class="upload-button">
                                    <span class="upload-text">画像を選択する</span>
                                </div>
                                <input type="file" name="image" id="imageInput" class="file-input" accept="image/*" onchange="this.form.submit()">
                            </label>
                        </form>
                    @endif
                </div>
            </section>

            <form action="{{ route('item.store') }}" method="POST" enctype="multipart/form-data" class="item-form" novalidate>
                @csrf
                @if ($errors->any())
                    <div class="error-container">
                        <ul class="error-list">
                            @foreach ($errors->all() as $error)
                                <li class="error-item">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <section class="item-details">
                    <h2 class="section-title">商品の詳細</h2>
                    <img class="section-divider" src="{{ asset('img/line-7.svg') }}" alt="">

                    <div class="category-section">
                        <h3 class="subsection-title">カテゴリー</h3>
                        <ul class="category-list">
                            @php
                                $selectedCategories = old('category', []);
                            @endphp
                            @foreach([
                                'fashion' => 'ファッション',
                                'electronics' => '家電',
                                'interior' => 'インテリア',
                                'ladies' => 'レディース',
                                'mens' => 'メンズ',
                                'cosmetics' => 'コスメ',
                                'books' => '本',
                                'games' => 'ゲーム',
                                'sports' => 'スポーツ',
                                'kitchen' => 'キッチン',
                                'handmade' => 'ハンドメイド',
                                'accessories' => 'アクセサリー',
                                'toys' => 'おもちゃ',
                                'baby' => 'ベビー・キッズ'
                            ] as $value => $label)
                                <li class="category-item">
                                    <label class="category-label {{ in_array($value, $selectedCategories) ? 'selected' : '' }}">
                                        <input type="checkbox" name="category[]" value="{{ $value }}" class="category-checkbox" {{ in_array($value, $selectedCategories) ? 'checked' : '' }}>
                                        <span class="category-text">{{ $label }}</span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="condition-section">
                        <h3 class="subsection-title">商品の状態</h3>
                        <div class="select-container">
                            <input type="hidden" name="condition" id="selected-condition" value="{{ old('condition', '') }}">
                            <div class="custom-select">
                                <div class="select-trigger">
                                    <span class="selected-text">{{ old('condition', '選択してください') }}</span>
                                    <div class="select-arrow"></div>
                                </div>
                                <ul class="select-options">
                                    @foreach([
                                        '良好' => '良好',
                                        '目立った傷や汚れなし' => '目立った傷や汚れなし',
                                        'やや傷や汚れあり' => 'やや傷や汚れあり',
                                        '状態が悪い' => '状態が悪い'
                                    ] as $value => $label)
                                        <li class="option {{ old('condition') === $value ? 'selected' : '' }}">
                                            <button type="submit" name="condition" value="{{ $value }}" class="option-link">
                                                {{ $label }}
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="item-description">
                    <h2 class="section-title">商品名と説明</h2>
                    <img class="section-divider" src="{{ asset('img/line-7.svg') }}" alt="">

                    <div class="form-group">
                        <label for="name" class="form-label">商品名</label>
                        <input type="text" name="name" id="name" class="form-input" value="{{ old('name') }}">
                    </div>

                    <div class="form-group">
                        <label for="brand" class="form-label">ブランド名</label>
                        <input type="text" name="brand" id="brand" class="form-input" value="{{ old('brand') }}">
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">商品の説明</label>
                        <textarea name="description" id="description" class="form-textarea">{{ old('description') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="price" class="form-label">販売価格</label>
                        <div class="price-input-container">
                            <span class="price-symbol">¥</span>
                            <input type="number" name="price" id="price" class="form-input price-input" value="{{ old('price') }}">
                        </div>
                    </div>
                </section>

                <div class="form-actions">
                    <button type="submit" class="submit-button">出品する</button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>