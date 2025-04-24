<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>住所変更</title>
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/address.css') }}">
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

            <div class="address-form">
                <h1 class="form-title">住所の変更</h1>
                
                <form action="{{ route('purchase.address.update', $item->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group">
                        <label for="postal_code" class="form-label">郵便番号</label>
                        <input type="text" id="postal_code" name="postal_code" class="form-input" value="{{ old('postal_code', $profile->postal_code ?? '') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="address" class="form-label">住所</label>
                        <input type="text" id="address" name="address" class="form-input" value="{{ old('address', $profile->address ?? '') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="building" class="form-label">建物名</label>
                        <input type="text" id="building" name="building" class="form-input" value="{{ old('building', $profile->building ?? '') }}">
                    </div>

                    <button type="submit" class="submit-button">更新する</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 