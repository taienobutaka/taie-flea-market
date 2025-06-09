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
      <div class="div">
        <header class="header">
          <div class="header__logo">
            <img src="{{ asset('img/logo.svg') }}" alt="Logo" class="header__logo-img">
          </div>
          <!-- 必要に応じて検索やナビゲーションを追加可能 -->
        </header>
        <div class="overlap">
          <div class="text-wrapper">「{{ $seller ? $seller->username : 'ユーザー名' }}」さんとの取引画面</div>
          <div class="overlap-group">
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
          </div>
          <div class="ellipse">
            @if($sellerProfile && $sellerProfile->image_path)
                <img src="{{ asset('storage/' . $sellerProfile->image_path) }}" alt="プロフィール画像" style="width:79px;height:79px;border-radius:50%;object-fit:cover;" />
            @else
                <div style="width:79px;height:79px;border-radius:50%;background:#eee;display:flex;align-items:center;justify-content:center;">No Image</div>
            @endif
          </div>
          <div class="overlap-wrapper">
            <div class="overlap-6">
              @if($item && $item->image_path)
                <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}" style="width:203px;height:206px;object-fit:cover;border-radius:0;position:relative;top:-50px;" />
              @else
                <div class="text-wrapper-9">商品画像</div>
              @endif
            </div>
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
              <script>
                document.addEventListener('DOMContentLoaded', function() {
                  const addImageBtn = document.querySelector('.chat-add-image-btn');
                  const fileInput = document.getElementById('chat-image-input');
                  if (addImageBtn && fileInput) {
                    addImageBtn.addEventListener('click', function(e) {
                      fileInput.click();
                    });
                  }
                });
              </script>
            </form>
          </div>
        </div>
        <div class="line-wrapper"><div class="line-2"></div></div>
      </div>
    </div>
</body>
</html>
