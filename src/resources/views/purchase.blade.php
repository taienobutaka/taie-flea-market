<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品購入</title>
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
</head>
<body>
    <div class="purchase">
        <header class="header">
            <div class="header__logo">
                <img src="{{ asset('img/logo.svg') }}" alt="Logo" class="header__logo-img">
            </div>
            <form action="{{ route('items.index') }}" method="GET" class="header__search">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="なにをお探しですか？" class="header__search-input">
                <input type="hidden" name="view" value="recommended">
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

        <main class="purchase__main">
            <section class="purchase__summary">
                <div class="purchase__price-box">
                    <h2 class="purchase__section-title">商品代金</h2>
                    <p class="purchase__price">
                        <span class="purchase__price-symbol">¥</span>
                        <span class="purchase__price-amount">{{ number_format($item->price) }}</span>
                    </p>
                </div>
                <div class="purchase__payment-box">
                    <h2 class="purchase__section-title">支払い方法</h2>
                    <div class="purchase__payment-method">
                        @if($selectedPaymentMethod === 'convenience')
                            コンビニ払い
                        @elseif($selectedPaymentMethod === 'credit_card')
                            カード支払い
                        @else
                            選択してください
                        @endif
                    </div>
                </div>
            </section>

            <section class="purchase__address">
                <div class="purchase__address-header">
                    <h2 class="purchase__section-title">配送先</h2>
                    <a href="{{ route('purchase.address', $item->id) }}" class="purchase__address-edit">変更する</a>
                </div>
                <address class="purchase__address-details">
                    <p class="purchase__address-postcode">〒 {{ $profile->postcode ?? '未設定' }}</p>
                    <p class="purchase__address-street">{{ $profile->address ?? '未設定' }}</p>
                    <p class="purchase__address-building">{{ $profile->building_name ?? '未設定' }}</p>
                </address>
            </section>

            <div class="purchase__product-image">
                <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}" class="purchase__image">
            </div>

            <section class="purchase__product">
                <div class="purchase__product-info">
                    <h1 class="purchase__product-name">{{ $item->name }}</h1>
                    <p class="purchase__product-price">
                        <span class="purchase__price-symbol">¥</span>
                        <span class="purchase__price-amount">{{ number_format($item->price) }}</span>
                    </p>
                </div>
            </section>

            <section class="purchase__payment">
                <h2 class="purchase__section-title">支払い方法</h2>
                <form action="{{ route('purchase.payment-method', $item->id) }}" method="POST" class="purchase__payment-form">
                    @csrf
                    <div class="purchase__payment-select-wrapper">
                        <input type="hidden" name="payment_method" id="selected-payment-method" value="{{ $selectedPaymentMethod }}">
                        <div class="custom-select">
                            <div class="select-trigger" onclick="this.parentElement.classList.toggle('open')">
                                <span class="selected-text">
                                    @if($selectedPaymentMethod === 'convenience')
                                        コンビニ払い
                                    @elseif($selectedPaymentMethod === 'credit_card')
                                        カード支払い
                                    @else
                                        選択してください
                                    @endif
                                </span>
                                <div class="polygon"></div>
                            </div>
                            <div class="select-options">
                                <div class="option {{ $selectedPaymentMethod === 'convenience' ? 'selected' : '' }}" 
                                     data-value="convenience"
                                     onclick="updatePaymentMethod('convenience', 'コンビニ払い', this)">
                                    <span class="option-text">コンビニ払い</span>
                                </div>
                                <div class="option {{ $selectedPaymentMethod === 'credit_card' ? 'selected' : '' }}" 
                                     data-value="credit_card"
                                     onclick="updatePaymentMethod('credit_card', 'カード支払い', this)">
                                    <span class="option-text">カード支払い</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @error('payment_method')
                        <div class="purchase__error-message">支払い方法を選択してください</div>
                    @enderror
                </form>
            </section>

            @if(!$profile || !$profile->postcode || !$profile->address)
                <div class="purchase__validation">
                    <p class="purchase__validation-message">配送先住所が設定されていません。</p>
                    <a href="{{ route('purchase.address', $item->id) }}" class="purchase__validation-link">配送先を設定する</a>
                </div>
            @endif

            <div class="purchase__action">
                @if($profile && $profile->postcode && $profile->address)
                    <form action="{{ route('purchase.confirm', $item->id) }}" method="POST" class="purchase__confirm-form">
                        @csrf
                        <input type="hidden" name="postcode" value="{{ $profile->postcode }}">
                        <input type="hidden" name="address" value="{{ $profile->address }}">
                        <input type="hidden" name="building_name" value="{{ $profile->building_name }}">
                        <input type="hidden" name="payment_method" value="{{ $selectedPaymentMethod }}">
                        <button type="submit" class="purchase__submit-button" {{ empty($selectedPaymentMethod) ? 'disabled' : '' }}>購入する</button>
                    </form>
                @else
                    <button class="purchase__submit-button purchase__submit-button--disabled" disabled>購入する</button>
                @endif
            </div>
        </main>
    </div>

    <script>
    function updatePaymentMethod(value, text, element) {
        // プルダウンの値を更新
        document.getElementById('selected-payment-method').value = value;
        element.closest('.custom-select').querySelector('.selected-text').textContent = text;
        element.closest('.custom-select').classList.remove('open');

        // フォームをサブミット
        element.closest('form').submit();
    }
    </script>
</body>
</html>