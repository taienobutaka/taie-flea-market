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
            <div class="header-logo">
                <img src="{{ asset('img/logo.svg') }}" alt="ロゴ" class="logo-img">
            </div>
            <div class="search-bar">
                <div class="search-container">なにをお探しですか？</div>
                <input type="text" class="search-input">
            </div>
            <nav class="header-nav">
                <a href="{{ route('logout') }}" class="nav-link">ログアウト</a>
                <a href="{{ route('mypage') }}" class="nav-link">マイページ</a>
                <a href="{{ route('sell.form') }}" class="nav-button">出品</a>
            </nav>
        </header>

        <main class="profile-main">
            <h1 class="profile-title">プロフィール設定</h1>

            <div class="profile-image-section">
                <div class="profile-image">
                    @if($profile && $profile->image_path)
                        <img src="{{ asset('storage/' . $profile->image_path) }}" alt="プロフィール画像">
                    @endif
                </div>
                <form action="{{ route('profile.uploadImage') }}" method="POST" enctype="multipart/form-data" class="image-upload-form">
                    @csrf
                    <label for="image" class="image-upload-button">
                        <span class="button-text">画像を選択する</span>
                        <input type="file" id="image" name="image" class="image-input" accept="image/*" onchange="this.form.submit()">
                    </label>
                </form>
            </div>

            <form action="{{ route('profile.update') }}" method="POST" class="profile-form">
                @csrf
                <div class="form-group">
                    <label for="username" class="form-label">ユーザー名</label>
                    <input type="text" id="username" name="username" class="form-input" value="{{ old('username', $profile->username ?? '') }}">
                    @error('username')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="postcode" class="form-label">郵便番号</label>
                    <input type="text" id="postcode" name="postcode" class="form-input" value="{{ old('postcode', $profile->postcode ?? '') }}">
                    @error('postcode')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="address" class="form-label">住所</label>
                    <input type="text" id="address" name="address" class="form-input" value="{{ old('address', $profile->address ?? '') }}">
                    @error('address')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="building_name" class="form-label">建物名</label>
                    <input type="text" id="building_name" name="building_name" class="form-input" value="{{ old('building_name', $profile->building_name ?? '') }}">
                    @error('building_name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="submit-button">更新する</button>
            </form>
        </main>
    </div>
</body>
</html>