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
                    <div class="search-container">なにをお探しですか？</div>
                    <input type="text" placeholder="" class="search-input">
                </div>
                <nav class="toppage-header-nav">
                    <a href="{{ route('logout') }}" class="nav-item">ログアウト</a>
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
                            @if (!session('imagePath'))
                                <form action="{{ route('item.uploadImage') }}" method="POST" enctype="multipart/form-data" class="upload-form">
                                    @csrf
                                    <input type="file" name="image" id="imageInput" class="file-input" accept="image/*" style="display: none;" onchange="this.form.submit()">
                                    <label for="imageInput" class="group-6">
                                        <div class="overlap-group-3">
                                            <div class="rectangle-4"></div>
                                            <div class="text-wrapper-20">画像を選択する</div>
                                        </div>
                                    </label>
                                </form>
                            @else
                                <div class="image-item" style="display: flex; align-items: center; justify-content: space-between; background-color: #f5f5f5; padding: 10px; border-radius: 4px; margin: 10px 0; border: 1px solid #ddd;">
                                    <span style="font-size: 14px; color: #333;">{{ basename(session('imagePath')) }}</span>
                                    <form action="{{ route('item.removeImage') }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="remove-button" style="background: none; border: none; cursor: pointer; color: #ff4444; font-size: 18px; padding: 0 5px;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('item.store') }}" method="POST" enctype="multipart/form-data">
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
                                $selectedCategory = old('category', session('selected_category'));
                            @endphp
                            <label class="category-label {{ $selectedCategory === 'fashion' ? 'selected' : '' }}">
                                <input type="radio" name="category" value="fashion" class="category-checkbox" {{ $selectedCategory === 'fashion' ? 'checked' : '' }} required>
                                <span class="category-text">ファッション</span>
                            </label>
                            <label class="category-label {{ $selectedCategory === 'electronics' ? 'selected' : '' }}">
                                <input type="radio" name="category" value="electronics" class="category-checkbox" {{ $selectedCategory === 'electronics' ? 'checked' : '' }}>
                                <span class="category-text">家電</span>
                            </label>
                            <label class="category-label {{ $selectedCategory === 'interior' ? 'selected' : '' }}">
                                <input type="radio" name="category" value="interior" class="category-checkbox" {{ $selectedCategory === 'interior' ? 'checked' : '' }}>
                                <span class="category-text">インテリア</span>
                            </label>
                            <label class="category-label {{ $selectedCategory === 'ladies' ? 'selected' : '' }}">
                                <input type="radio" name="category" value="ladies" class="category-checkbox" {{ $selectedCategory === 'ladies' ? 'checked' : '' }}>
                                <span class="category-text">レディース</span>
                            </label>
                            <label class="category-label {{ $selectedCategory === 'mens' ? 'selected' : '' }}">
                                <input type="radio" name="category" value="mens" class="category-checkbox" {{ $selectedCategory === 'mens' ? 'checked' : '' }}>
                                <span class="category-text">メンズ</span>
                            </label>
                            <label class="category-label {{ $selectedCategory === 'cosmetics' ? 'selected' : '' }}">
                                <input type="radio" name="category" value="cosmetics" class="category-checkbox" {{ $selectedCategory === 'cosmetics' ? 'checked' : '' }}>
                                <span class="category-text">コスメ</span>
                            </label>
                            <label class="category-label {{ $selectedCategory === 'books' ? 'selected' : '' }}">
                                <input type="radio" name="category" value="books" class="category-checkbox" {{ $selectedCategory === 'books' ? 'checked' : '' }}>
                                <span class="category-text">本</span>
                            </label>
                            <label class="category-label {{ $selectedCategory === 'games' ? 'selected' : '' }}">
                                <input type="radio" name="category" value="games" class="category-checkbox" {{ $selectedCategory === 'games' ? 'checked' : '' }}>
                                <span class="category-text">ゲーム</span>
                            </label>
                            <label class="category-label {{ $selectedCategory === 'sports' ? 'selected' : '' }}">
                                <input type="radio" name="category" value="sports" class="category-checkbox" {{ $selectedCategory === 'sports' ? 'checked' : '' }}>
                                <span class="category-text">スポーツ</span>
                            </label>
                            <label class="category-label {{ $selectedCategory === 'kitchen' ? 'selected' : '' }}">
                                <input type="radio" name="category" value="kitchen" class="category-checkbox" {{ $selectedCategory === 'kitchen' ? 'checked' : '' }}>
                                <span class="category-text">キッチン</span>
                            </label>
                            <label class="category-label {{ $selectedCategory === 'handmade' ? 'selected' : '' }}">
                                <input type="radio" name="category" value="handmade" class="category-checkbox" {{ $selectedCategory === 'handmade' ? 'checked' : '' }}>
                                <span class="category-text">ハンドメイド</span>
                            </label>
                            <label class="category-label {{ $selectedCategory === 'accessories' ? 'selected' : '' }}">
                                <input type="radio" name="category" value="accessories" class="category-checkbox" {{ $selectedCategory === 'accessories' ? 'checked' : '' }}>
                                <span class="category-text">アクセサリー</span>
                            </label>
                            <label class="category-label {{ $selectedCategory === 'toys' ? 'selected' : '' }}">
                                <input type="radio" name="category" value="toys" class="category-checkbox" {{ $selectedCategory === 'toys' ? 'checked' : '' }}>
                                <span class="category-text">おもちゃ</span>
                            </label>
                            <label class="category-label {{ $selectedCategory === 'baby' ? 'selected' : '' }}">
                                <input type="radio" name="category" value="baby" class="category-checkbox" {{ $selectedCategory === 'baby' ? 'checked' : '' }}>
                                <span class="category-text">ベビー・キッズ</span>
                            </label>
                        </div>
                    </div>

                    <div class="exhibited-product-2">
                        <div class="text-wrapper-3">商品の状態</div>
                        <div class="overlap-group-wrapper">
                            <div class="overlap-group-2">
                                <select name="condition" class="rectangle condition-select" required>
                                    <option value="" disabled selected>選択してください</option>
                                    <option value="良好">良好</option>
                                    <option value="目立った傷や汚れなし">目立った傷や汚れなし</option>
                                    <option value="やや傷や汚れあり">やや傷や汚れあり</option>
                                    <option value="状態が悪い">状態が悪い</option>
                                </select>
                                <div class="polygon"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="group-5">
                    <div class="text-wrapper-4">商品名と説明</div>
                    <img class="line" src="{{ asset('img/line-8.svg') }}" />
                </div>

                <div class="group-4">
                    <div class="text-wrapper-3">ブランド名</div>
                    <input type="text" name="brand" class="rectangle-3" required>
                </div>

                <div class="group-3">
                    <div class="text-wrapper-3">商品名</div>
                    <input type="text" name="name" class="rectangle-3" required>
                </div>

                <div class="group-2">
                    <div class="text-wrapper-3">商品の説明</div>
                    <textarea name="description" class="rectangle-2" required></textarea>
                </div>

                <div class="group">
                    <div class="text-wrapper-3">販売価格</div>
                    <div class="overlap-group">
                        <div class="text-wrapper-2">¥</div>
                        <input type="number" name="price" class="rectangle" required>
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