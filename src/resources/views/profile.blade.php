<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>プロフィール編集</title>
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
</head>
<body>
    <div class="profile-container">
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
                        <form id="header-logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                        <a href="#" onclick="event.preventDefault(); document.getElementById('header-logout-form').submit();" class="header__nav-link header__nav-link--logout">ログアウト</a>
                    </li>
                    <li class="header__nav-item">
                        <a href="{{ route('mypage') }}" class="header__nav-link">マイページ</a>
                    </li>
                    <li class="header__nav-item">
                        <a href="{{ route('sell.form') }}" class="header__nav-link header__nav-link--sell">出品</a>
                    </li>
                </ul>
            </nav>
        </header>

        <main class="profile-main">
            <h1 class="profile-title">プロフィール設定</h1>

            <section class="profile-image-section">
                <h2 class="profile-image-title visually-hidden">プロフィール画像</h2>
                <div class="profile-image">
                    @if($profile && $profile->image_path)
                        <img src="{{ asset('storage/' . $profile->image_path) }}" alt="プロフィール画像" class="profile-image-preview">
                    @endif
                </div>

                <form action="{{ route('profile.uploadImage') }}" method="POST" enctype="multipart/form-data" class="image-upload-form">
                    @csrf
                    <div class="image-select-button">
                        <label for="profile-image-input" class="image-select-label">
                            画像を選択する
                        </label>
                        <input type="file" id="profile-image-input" name="image" accept="image/*" class="image-input" onchange="this.form.submit()">
                    </div>
                </form>
            </section>

            <form action="{{ route('profile.update') }}" method="POST" class="profile-form">
                @csrf
                <fieldset class="profile-fieldset">
                    <legend class="profile-fieldset-legend visually-hidden">プロフィール情報</legend>
                    
                    <div class="input-group username-input-group">
                        <div class="input-group-content">
                            <label for="username" class="input-label">ユーザー名</label>
                            <input type="text" id="username" name="username" class="input-field @error('username') is-invalid @enderror" value="{{ old('username', $profile->username ?? '') }}">
                        </div>
                        @error('username')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="input-group postcode-input-group">
                        <div class="input-group-content">
                            <label for="postcode" class="input-label">郵便番号</label>
                            <input type="text" id="postcode" name="postcode" class="input-field @error('postcode') is-invalid @enderror" value="{{ old('postcode', $profile->postcode ?? '') }}">
                        </div>
                        @error('postcode')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="input-group address-input-group">
                        <div class="input-group-content">
                            <label for="address" class="input-label">住所</label>
                            <input type="text" id="address" name="address" class="input-field @error('address') is-invalid @enderror" value="{{ old('address', $profile->address ?? '') }}">
                        </div>
                        @error('address')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="input-group building-input-group">
                        <div class="input-group-content">
                            <label for="building_name" class="input-label">建物名</label>
                            <input type="text" id="building_name" name="building_name" class="input-field @error('building_name') is-invalid @enderror" value="{{ old('building_name', $profile->building_name ?? '') }}">
                        </div>
                        @error('building_name')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </fieldset>

                <div class="action-bar">
                    <button type="submit" class="action-bar-text">更新する</button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>