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
<div class="profile-container">
    <header class="toppage-header">
        <div class="toppage-header-logo">
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

    <!-- プロフィール更新フォーム -->
    <form class="profile-form" action="{{ route('profile.store') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
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