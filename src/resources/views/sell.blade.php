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
    <div class="screen">
        <div class="div">
            <div class="toppage-header">
                <div class="toppage-header-icon">
                    <img src="{{ asset('img/logo.svg') }}" alt="Logo" class="toppage-header-logo-img">
                </div>
                <div class="search-bar">
                    <form action="{{ route('items.index') }}" method="GET" class="search-form">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="なにをお探しですか？" class="search-input">
                        <input type="hidden" name="page" value="recommended">
                        <button type="submit" class="search-button" style="display: none;">検索</button>
                    </form>
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

            <div class="text-wrapper-22">商品の出品</div>

            <div class="exhibited-products">
                <div class="text-wrapper-21">商品画像</div>
                <div class="overlap">
                    <div class="rectangle-5">
                        <div class="upload-container">
                            @if (session('imagePath'))
                                <div class="preview-container">
                                    <img src="{{ asset('storage/' . session('imagePath')) }}" alt="商品画像" class="preview-image">
                                    <form action="{{ route('sell.remove-image') }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="remove-button">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <form action="{{ route('sell.upload-image') }}" method="POST" enctype="multipart/form-data" class="upload-form">
                                    @csrf
                                    <label for="imageInput" class="group-6">
                                        <div class="overlap-group-3">
                                            <div class="rectangle-4"></div>
                                            <div class="text-wrapper-20">画像を選択する</div>
                                        </div>
                                        <input type="file" name="image" id="imageInput" class="file-input" accept="image/*" onchange="this.form.submit()">
                                    </label>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('item.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="exhibited-product">
                    <div class="exhibited-product-4">
                        <div class="text-wrapper-4">商品の詳細</div>
                        <img class="img" src="{{ asset('img/line-7.svg') }}" />
                    </div>

                    <div class="exhibited-product-3">
                        <div class="text-wrapper-19">カテゴリー</div>
                        <div class="category-buttons">
                            @php
                                $selectedCategories = old('category', []);
                            @endphp
                            <label class="category-label {{ in_array('fashion', $selectedCategories) ? 'selected' : '' }}">
                                <input type="checkbox" name="category[]" value="fashion" class="category-checkbox" {{ in_array('fashion', $selectedCategories) ? 'checked' : '' }}>
                                <span class="category-text">ファッション</span>
                            </label>
                            <label class="category-label {{ in_array('electronics', $selectedCategories) ? 'selected' : '' }}">
                                <input type="checkbox" name="category[]" value="electronics" class="category-checkbox" {{ in_array('electronics', $selectedCategories) ? 'checked' : '' }}>
                                <span class="category-text">家電</span>
                            </label>
                            <label class="category-label {{ in_array('interior', $selectedCategories) ? 'selected' : '' }}">
                                <input type="checkbox" name="category[]" value="interior" class="category-checkbox" {{ in_array('interior', $selectedCategories) ? 'checked' : '' }}>
                                <span class="category-text">インテリア</span>
                            </label>
                            <label class="category-label {{ in_array('ladies', $selectedCategories) ? 'selected' : '' }}">
                                <input type="checkbox" name="category[]" value="ladies" class="category-checkbox" {{ in_array('ladies', $selectedCategories) ? 'checked' : '' }}>
                                <span class="category-text">レディース</span>
                            </label>
                            <label class="category-label {{ in_array('mens', $selectedCategories) ? 'selected' : '' }}">
                                <input type="checkbox" name="category[]" value="mens" class="category-checkbox" {{ in_array('mens', $selectedCategories) ? 'checked' : '' }}>
                                <span class="category-text">メンズ</span>
                            </label>
                            <label class="category-label {{ in_array('cosmetics', $selectedCategories) ? 'selected' : '' }}">
                                <input type="checkbox" name="category[]" value="cosmetics" class="category-checkbox" {{ in_array('cosmetics', $selectedCategories) ? 'checked' : '' }}>
                                <span class="category-text">コスメ</span>
                            </label>
                            <label class="category-label {{ in_array('books', $selectedCategories) ? 'selected' : '' }}">
                                <input type="checkbox" name="category[]" value="books" class="category-checkbox" {{ in_array('books', $selectedCategories) ? 'checked' : '' }}>
                                <span class="category-text">本</span>
                            </label>
                            <label class="category-label {{ in_array('games', $selectedCategories) ? 'selected' : '' }}">
                                <input type="checkbox" name="category[]" value="games" class="category-checkbox" {{ in_array('games', $selectedCategories) ? 'checked' : '' }}>
                                <span class="category-text">ゲーム</span>
                            </label>
                            <label class="category-label {{ in_array('sports', $selectedCategories) ? 'selected' : '' }}">
                                <input type="checkbox" name="category[]" value="sports" class="category-checkbox" {{ in_array('sports', $selectedCategories) ? 'checked' : '' }}>
                                <span class="category-text">スポーツ</span>
                            </label>
                            <label class="category-label {{ in_array('kitchen', $selectedCategories) ? 'selected' : '' }}">
                                <input type="checkbox" name="category[]" value="kitchen" class="category-checkbox" {{ in_array('kitchen', $selectedCategories) ? 'checked' : '' }}>
                                <span class="category-text">キッチン</span>
                            </label>
                            <label class="category-label {{ in_array('handmade', $selectedCategories) ? 'selected' : '' }}">
                                <input type="checkbox" name="category[]" value="handmade" class="category-checkbox" {{ in_array('handmade', $selectedCategories) ? 'checked' : '' }}>
                                <span class="category-text">ハンドメイド</span>
                            </label>
                            <label class="category-label {{ in_array('accessories', $selectedCategories) ? 'selected' : '' }}">
                                <input type="checkbox" name="category[]" value="accessories" class="category-checkbox" {{ in_array('accessories', $selectedCategories) ? 'checked' : '' }}>
                                <span class="category-text">アクセサリー</span>
                            </label>
                            <label class="category-label {{ in_array('toys', $selectedCategories) ? 'selected' : '' }}">
                                <input type="checkbox" name="category[]" value="toys" class="category-checkbox" {{ in_array('toys', $selectedCategories) ? 'checked' : '' }}>
                                <span class="category-text">おもちゃ</span>
                            </label>
                            <label class="category-label {{ in_array('baby', $selectedCategories) ? 'selected' : '' }}">
                                <input type="checkbox" name="category[]" value="baby" class="category-checkbox" {{ in_array('baby', $selectedCategories) ? 'checked' : '' }}>
                                <span class="category-text">ベビー・キッズ</span>
                            </label>
                        </div>
                    </div>

                    <div class="exhibited-product-2">
                        <div class="text-wrapper-3">商品の状態</div>
                        <div class="overlap-group-wrapper">
                            <div class="overlap-group-2">
                                <input type="hidden" name="condition" id="selected-condition" value="{{ old('condition', '') }}">
                                <div class="custom-select">
                                    <div class="select-trigger" onclick="this.parentElement.classList.toggle('open')">
                                        <span class="selected-text">{{ old('condition', '選択してください') }}</span>
                                        <div class="polygon"></div>
                                    </div>
                                    <div class="select-options">
                                        @php
                                            $conditions = [
                                                '良好' => '良好',
                                                '目立った傷や汚れなし' => '目立った傷や汚れなし',
                                                'やや傷や汚れあり' => 'やや傷や汚れあり',
                                                '状態が悪い' => '状態が悪い'
                                            ];
                                        @endphp
                                        @foreach($conditions as $value => $label)
                                            <div class="option {{ old('condition') === $value ? 'selected' : '' }}" 
                                                 data-value="{{ $value }}"
                                                 onclick="document.getElementById('selected-condition').value = '{{ $value }}'; this.closest('.custom-select').querySelector('.selected-text').textContent = '{{ $label }}'; this.closest('.custom-select').classList.remove('open');">
                                                <span class="option-text">{{ $label }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="group-5">
                    <div class="text-wrapper-4">商品名と説明</div>
                    <img class="img" src="{{ asset('img/line-7.svg') }}" />
                </div>

                <div class="group-4">
                    <div class="text-wrapper-3">ブランド名</div>
                    <input type="text" name="brand" class="rectangle-3">
                </div>

                <div class="group-3">
                    <div class="text-wrapper-3">商品名</div>
                    <input type="text" name="name" class="rectangle-3">
                </div>

                <div class="group-2">
                    <div class="text-wrapper-3">商品の説明</div>
                    <textarea name="description" class="rectangle-2"></textarea>
                </div>

                <div class="group">
                    <div class="text-wrapper-3">販売価格</div>
                    <div class="overlap-group">
                        <div class="text-wrapper-2">¥</div>
                        <input type="number" name="price" class="rectangle">
                    </div>
                </div>

                <div class="action-bar">
                    <button type="submit" class="submit-button">出品する</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>