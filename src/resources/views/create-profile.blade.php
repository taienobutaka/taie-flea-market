<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>プロフィール設定</title>
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/create-profile.css') }}">
    <style>
        .toppage-header-nav .nav-item {
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <!-- ヘッダーセクション -->
        <header class="toppage-header">
            <div class="toppage-header-icon">
                <img src="{{ asset('img/logo.svg') }}" alt="Logo" class="toppage-header-logo-img">
            </div>
            <form action="{{ route('items.index') }}" method="GET" class="search-bar">
                <input type="text" name="search" value="{{ request('search') }}" class="search-input" placeholder="なにをお探しですか？">
                <input type="hidden" name="page" value="recommended">
            </form>
            <nav class="toppage-header-nav">
                <a href="{{ route('login') }}" class="nav-item">ログアウト</a>
                <a href="{{ route('mypage') }}" class="nav-item">マイページ</a>
                <a href="{{ route('sell.form') }}" class="nav-item">出品</a>
            </nav>
        </header>

        <main class="profile-main">
            <h1 class="profile-title">プロフィール設定</h1>

            <!-- プロフィール画像セクション -->
            <section class="profile-image-section">
                <h2 class="visually-hidden">プロフィール画像</h2>
                <div class="profile-image">
                    @if(session('profile_image'))
                        @php
                            $imagePath = session('profile_image');
                            $fullPath = storage_path('app/public/' . $imagePath);
                            $exists = file_exists($fullPath);
                            $isReadable = $exists && is_readable($fullPath);
                            $imageUrl = '/storage/' . $imagePath;
                        @endphp
                        @if($exists && $isReadable)
                            <img src="{{ $imageUrl }}" alt="プロフィール画像" class="profile-image-preview">
                        @endif
                    @endif
                </div>

                <!-- 画像アップロードフォーム -->
                <form id="image-upload-form" action="{{ route('uploadImage') }}" method="POST" enctype="multipart/form-data" class="image-upload-form">
                    @csrf
                    <div class="image-select-button">
                        <label for="image" class="image-select-label">
                            画像を選択する
                        </label>
                        <input type="file" id="image" name="image" accept="image/*" class="image-input" onchange="this.form.submit()">
                    </div>
                </form>
            </section>

            <!-- プロフィール情報フォーム -->
            <form class="profile-form" action="{{ route('profile.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="image_path" value="{{ session('profile_image') }}">
                
                <fieldset class="profile-fieldset">
                    <legend class="visually-hidden">プロフィール情報</legend>
                    
                    <!-- ユーザー名入力 -->
                    <div class="input-group username-input-group">
                        <div class="input-group-content">
                            <label for="username" class="input-label">ユーザー名</label>
                            <input type="text" id="username" name="username" class="input-field @error('username') is-invalid @enderror" value="{{ old('username') }}">
                        </div>
                        @error('username')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- 郵便番号入力 -->
                    <div class="input-group postcode-input-group">
                        <div class="input-group-content">
                            <label for="postcode" class="input-label">郵便番号</label>
                            <input type="text" id="postcode" name="postcode" class="input-field @error('postcode') is-invalid @enderror" value="{{ old('postcode') }}">
                        </div>
                        @error('postcode')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- 住所入力 -->
                    <div class="input-group address-input-group">
                        <div class="input-group-content">
                            <label for="address" class="input-label">住所</label>
                            <input type="text" id="address" name="address" class="input-field @error('address') is-invalid @enderror" value="{{ old('address') }}">
                        </div>
                        @error('address')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- 建物名入力 -->
                    <div class="input-group building-input-group">
                        <div class="input-group-content">
                            <label for="building_name" class="input-label">建物名</label>
                            <input type="text" id="building_name" name="building_name" class="input-field @error('building_name') is-invalid @enderror" value="{{ old('building_name') }}">
                        </div>
                        @error('building_name')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </fieldset>

                <!-- 更新ボタン -->
                <div class="action-bar">
                    <button type="submit" class="action-bar-text">更新する</button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>