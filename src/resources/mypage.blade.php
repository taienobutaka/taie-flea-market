<a href="{{ route('profile.edit') }}" class="edit-button">
    <span>編集</span>
</a> 

@if($items->isEmpty())
    <div class="empty-state">
        @if($page === 'sell')
            <p class="empty-state__message">出品した商品はありません</p>
        @else
            <p class="empty-state__message">購入した商品はありません</p>
        @endif
    </div>
@else
    <h2 class="visually-hidden">{{ $page === 'sell' ? '出品した商品一覧' : '購入した商品一覧' }}</h2>
    <ul class="product-grid">
        @foreach($items as $item)
            <li class="product-card {{ $item->status === 'sold' ? 'sold' : '' }}">
                <div class="product-card__header">
                    <a href="{{ route('item.show', $item->id) }}" class="product-card__link">
                        <div class="product-card__image">
                            @if($item->image_path)
                                <img src="{{ asset('storage/' . $item->image_path) }}" 
                                     alt="{{ $item->name }}" 
                                     class="product-card__img"
                                     onerror="this.onerror=null; this.src='{{ asset('img/no-image.png') }}';">
                            @else
                                <img src="{{ asset('img/no-image.png') }}" 
                                     alt="No Image" 
                                     class="product-card__img">
                            @endif
                            @if($page === 'sell' && $item->status === 'sold')
                                <div class="product-card__sold" aria-label="売り切れ">SOLD</div>
                            @endif
                        </div>
                        <div class="product-card__info">
                            <h3 class="product-card__name">{{ $item->name }}</h3>
                        </div>
                    </a>
                </div>
            </li>
        @endforeach
    </ul>
@endif 