{{-- resources/views/purchaser.blade.php --}}
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1512, height=982, initial-scale=1.0">
    <title>購入者チャット画面</title>
    <link rel="stylesheet" href="{{ asset('css/purchaser.css') }}">
</head>
<body>
    <div class="screen">
      <header class="header">
        <div class="header__logo">
          <img src="{{ asset('img/logo.svg') }}" alt="Logo" class="header__logo-img">
        </div>
      </header>
      <div class="div">
        <div class="overlap">
          <div class="text-wrapper">「{{ $seller ? $seller->username : 'ユーザー名' }}」さんとの取引画面</div>
          <!-- 取引完了ボタンとモーダルのトリガー要素をoverlap直下で絶対配置 -->
          <input type="checkbox" id="trade-complete-modal-toggle" style="display:none;">
          <label for="trade-complete-modal-toggle" class="trade-complete-btn" style="position:absolute;top:26px;left:1190px;z-index:20;">
            <span class="text-wrapper-13">取引を完了する</span>
          </label>
          <div id="trade-complete-modal-content-area" class="trade-complete-modal">
            <label for="trade-complete-modal-toggle" class="trade-complete-modal-bg"></label>
            <form id="trade-complete-rating-form" method="POST" action="{{ route('purchaser.rate', ['item_id' => $item->id]) }}" style="margin:0;padding:0;width:100%;height:100%;">
              @csrf
              <div class="trade-complete-modal-content">
                <p class="trade-complete-title">取引が完了しました。</p>
                <hr class="trade-complete-hr" />
                <div class="trade-complete-question">今回の取引相手はどうでしたか？</div>
                <div class="trade-complete-stars">
                  <input type="radio" name="rating" id="star1" value="1">
                  <input type="radio" name="rating" id="star2" value="2">
                  <input type="radio" name="rating" id="star3" value="3">
                  <input type="radio" name="rating" id="star4" value="4">
                  <input type="radio" name="rating" id="star5" value="5">
                  <label for="star1" class="star">&#9733;</label>
                  <label for="star2" class="star">&#9733;</label>
                  <label for="star3" class="star">&#9733;</label>
                  <label for="star4" class="star">&#9733;</label>
                  <label for="star5" class="star">&#9733;</label>
                </div>
                <hr class="trade-complete-stars-hr">
                <button type="submit" class="trade-complete-submit" style="display:inline-block;text-align:center;cursor:pointer;">送信する</button>
              </div>
            </form>
          </div>
          <div class="line" style="background:#ccc;height:4px;width:1508px;position:absolute;top:103px;left:0;"></div>
          <div class="img" style="background:#ccc;height:4px;width:1502px;position:absolute;top:338px;left:0;"></div>
          <div class="rectangle">
            <div class="sidebar-inner">
                <div class="sidebar-title">その他の取引</div>
                <ul class="sidebar-trades">
                  @if(isset($sidebarItems) && $sidebarItems->count())
                    @foreach($sidebarItems as $sidebarItem)
                      @php
                        $isSeller = isset($sidebarItem->user_id) && $sidebarItem->user_id === auth()->id();
                        $link = $isSeller
                          ? route('seller.chat', ['item_id' => $sidebarItem->id])
                          : route('purchaser.chat', ['item_id' => $sidebarItem->id]);
                        $isActive = $sidebarItem->id == ($item->id ?? null);
                      @endphp
                      <li class="sidebar-trade-item">
                        <a href="{{ $link }}" class="sidebar-trade-link{{ $isActive ? ' active' : '' }}">
                          {{ $sidebarItem->name }}
                        </a>
                      </li>
                    @endforeach
                  @else
                    <li class="sidebar-trade-item" style="color:#ccc;text-align:center;">取引中の商品はありません</li>
                  @endif
                </ul>
              </div>
          </div>
          <div class="ellipse">
            @if($sellerProfile && $sellerProfile->image_path)
                <img src="{{ asset('storage/' . $sellerProfile->image_path) }}" alt="プロフィール画像" style="width:79px;height:79px;border-radius:50%;object-fit:cover;" />
            @else
                <div style="width:79px;height:79px;border-radius:50%;background:#eee;display:flex;align-items:center;justify-content:center;">No Image</div>
            @endif
          </div>
          <div class="overlap-wrapper">
            @if($item && $item->image_path)
              <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}" style="width:203px;height:206px;object-fit:cover;border-radius:0;" />
            @else
              <div class="text-wrapper-9">商品画像</div>
            @endif
          </div>
          <div class="text-wrapper-3">{{ $item ? '¥' . number_format($item->price) : '商品価格' }}</div>
          <div class="text-wrapper-4">{{ $item ? $item->name : '商品名' }}</div>

          <div class="chat-area">
            <ul class="chat-list">
              @foreach($chats as $chat)
                <li class="chat-message {{ $chat->user_id == auth()->id() ? 'my-message' : 'other-message' }}">
                  <div class="chat-user-info">
                    @if($chat->user_id == auth()->id())
                      <span class="chat-username">{{ $chat->user ? $chat->user->username : 'ユーザー' }}</span>
                      @if($chat->user && $chat->user->profile && $chat->user->profile->image_path)
                        <img src="{{ asset('storage/' . $chat->user->profile->image_path) }}" alt="{{ $chat->user->username }}" class="chat-user-img">
                      @else
                        <div class="chat-user-img chat-user-img--noimg">No Image</div>
                      @endif
                    @else
                      @if($chat->user && $chat->user->profile && $chat->user->profile->image_path)
                        <img src="{{ asset('storage/' . $chat->user->profile->image_path) }}" alt="{{ $chat->user->username }}" class="chat-user-img">
                      @else
                        <div class="chat-user-img chat-user-img--noimg">No Image</div>
                      @endif
                      <span class="chat-username">{{ $chat->user ? $chat->user->username : 'ユーザー' }}</span>
                    @endif
                  </div>
                  <div class="chat-comment">{{ $chat->comment }}</div>
                  @if($chat->image_path)
                    <div class="chat-image" style="margin-top:8px;">
                      <img src="{{ asset('storage/' . $chat->image_path) }}" alt="チャット画像" style="max-width:220px;max-height:220px;border-radius:4px;object-fit:contain;box-shadow:0 1px 6px #ccc;" />
                    </div>
                  @endif
                  <div class="chat-actions">
                    @if($chat->user_id == auth()->id())
                      <a href="{{ route('chat.edit.get', $chat->id) }}" class="chat-edit-btn" style="display:inline-block;">編集</a>
                      <form action="{{ route('chat.delete', $chat->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="chat-delete-btn">削除</button>
                      </form>
                    @endif
                  </div>
                </li>
              @endforeach
            </ul>
            <form action="{{ route('chat.send', ['item_id' => $item->id]) }}" method="POST" class="chat-form" enctype="multipart/form-data" novalidate>
              @csrf
              <div style="display:flex;flex-direction:column;width:100%;">
                @if($errors->any())
                  <div class="chat-error-message-row" style="display:flex;flex-direction:row;gap:16px;margin-bottom:8px;flex-wrap:nowrap;align-items:center;width:100%;">
                    @foreach($errors->all() as $error)
                      <div style="color:#d32f2f;white-space:nowrap;">{{ $error }}</div>
                    @endforeach
                  </div>
                @endif
                <div style="display:flex;flex-direction:row;align-items:flex-end;gap:10px;width:100%;">
                  <textarea name="comment" class="chat-input" placeholder="取引メッセージを記入してください" style="flex:1;margin-top:3px;">{{ old('comment', isset($editChat) ? $editChat->comment : null) }}</textarea>
                  <label class="chat-add-image-btn" for="chat-image-input" style="margin-bottom:0;">
                    <span class="chat-add-image-btn-text">画像を追加</span>
                  </label>
                  <input type="file" id="chat-image-input" name="image" accept="image/*" style="display:none;">
                  <button type="submit" style="background:none;border:none;padding:0;min-width:80px;min-height:61px;display:flex;align-items:center;justify-content:center;">
                    <img src="/img/send.jpg" alt="送信" style="width:80px;height:61px;flex-shrink:0;aspect-ratio:80/61;object-fit:contain;display:block;margin-left:3px;margin-top:10px;" />
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
        <div class="line-wrapper"><div class="line-2"></div></div>
      </div>
    </div>
  </body>
</html>
