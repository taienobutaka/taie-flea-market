<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>住所の変更</title>
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/address.css') }}">
</head>
<body>
    <div class="address">
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
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="header__nav-link header__nav-link--logout">ログアウト</a>
                    </li>
                    <li class="header__nav-item"><a href="{{ route('mypage') }}" class="header__nav-link">マイページ</a></li>
                    <li class="header__nav-item"><a href="{{ route('sell.form') }}" class="header__nav-link header__nav-link--sell">出品</a></li>
                </ul>
            </nav>
        </header>

        <main class="main">
            <div class="address-form">
                <h1 class="address-form__title">住所の変更</h1>

                @if (session('error'))
                    <div class="alert alert--danger">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('purchase.address.update', ['item_id' => $item->id]) }}" method="POST" class="address-form__form">
                    @csrf
                    @method('PUT')
                    
                    <fieldset class="address-form__fieldset">                        
                        <div class="address-form__group">
                            <label for="postcode" class="address-form__label">郵便番号</label>
                            <input type="text" id="postcode" name="postcode" value="{{ old('postcode', $profile->postcode ?? '') }}" class="address-form__input @error('postcode') address-form__input--error @enderror" placeholder="例: 123-4567">
                            @error('postcode')
                                <span class="address-form__error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="address-form__group">
                            <label for="address" class="address-form__label">住所</label>
                            <input type="text" id="address" name="address" value="{{ old('address', $profile->address ?? '') }}" class="address-form__input @error('address') address-form__input--error @enderror">
                            @error('address')
                                <span class="address-form__error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="address-form__group">
                            <label for="building_name" class="address-form__label">建物名</label>
                            <input type="text" id="building_name" name="building_name" value="{{ old('building_name', $profile->building_name ?? '') }}" class="address-form__input @error('building_name') address-form__input--error @enderror">
                            @error('building_name')
                                <span class="address-form__error">{{ $message }}</span>
                            @enderror
                        </div>
                    </fieldset>

                    <div class="address-form__actions">
                        <button type="submit" class="address-form__submit"><span>更新する</span></button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html> 