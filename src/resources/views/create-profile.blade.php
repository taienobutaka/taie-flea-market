<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>プロフィール設定</title>
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/create-profile.css') }}">
</head>
<body>
<div class="profile-container">
    <header class="toppage-header">
        <div class="toppage-header-icon">
            <img src="{{ asset('img/logo.svg') }}" alt="Logo" class="toppage-header-logo-img">
        </div>
        <div class="search-bar">
            <div class="search-container">なにをお探しですか？</div>
            <input type="text" placeholder="" class="search-input">
        </div>
        <nav class="toppage-header-nav">
            <div class="nav-item">ログアウト</div>
            <div class="nav-item">マイページ</div>
            <div class="nav-item">出品</div>
        </nav>
    </header>
    <h1 class="profile-title">プロフィール設定</h1>

    <div class="profile-image">
        @if (session('imagePath'))
            <img src="{{ asset('storage/' . session('imagePath')) }}" alt="プロフィール画像" style="max-width: 100%; height: auto;">
        @endif
    </div>

    <form id="image-upload-form" action="{{ route('uploadImage') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="image-select-button">
            <label for="image" id="image-button-label" style="cursor: pointer;">
                画像を選択する
            </label>
            <input type="file" id="image" name="image" accept="image/*" style="display: none;" onchange="document.getElementById('image-upload-form').submit();">
        </div>
    </form>

    <!-- プロフィール更新フォーム -->
    <form class="profile-form" action="{{ route('profile.store') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        <input type="hidden" name="image_path" value="{{ session('imagePath') }}">
        <div class="input-group">
            <label for="username" class="input-label">ユーザー名</label>
            <input type="text" id="username" name="username" class="input-field" value="{{ old('username') }}">
            @error('username')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>
        <div class="input-group">
            <label for="postcode" class="input-label">郵便番号</label>
            <input type="text" id="postcode" name="postcode" class="input-field" value="{{ old('postcode') }}">
            @error('postcode')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>
        <div class="input-group">
            <label for="address" class="input-label">住所</label>
            <input type="text" id="address" name="address" class="input-field" value="{{ old('address') }}">
            @error('address')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>
        <div class="input-group">
            <label for="building_name" class="input-label">建物名</label>
            <input type="text" id="building_name" name="building_name" class="input-field" value="{{ old('building_name') }}">
            @error('building_name')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>
        <div class="action-bar">
            <button type="submit" class="action-bar-text">更新する</button>
        </div>
    </form>
</div>
</body>
</html>