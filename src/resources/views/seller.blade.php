<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=1512, height=982, initial-scale=1">
    <title>出品者チャット画面</title>
    <link rel="stylesheet" href="/css/seller.css">
</head>
<body>
    <div class="screen">
      <div class="div">
        <header class="header">
          <div class="header__logo">
            <img src="{{ asset('img/logo.svg') }}" alt="Logo" class="header__logo-img">
          </div>
        </header>
        <div class="overlap">
          <div class="text-wrapper">「{{ $item && $item->user ? $item->user->username : 'ユーザー名' }}」さんとの取引画面</div>
          <!-- ヘッダー下の円形枠に出品者プロフィール画像 -->
          <div class="ellipse">
            @if($item && $item->user && $item->user->profile && $item->user->profile->image_path)
                <img src="{{ asset('storage/' . $item->user->profile->image_path) }}" alt="出品者画像" style="width:79px;height:79px;border-radius:50%;object-fit:cover;" />
            @else
                <div style="width:79px;height:79px;border-radius:50%;background:#eee;display:flex;align-items:center;justify-content:center;">No Image</div>
            @endif
          </div>
          <!-- ユーザー名表示 -->
          <div class="text-wrapper-6">{{ $item && $item->user ? $item->user->username : 'ユーザー名' }}</div>
          <!-- ここから下は修正前のダミー・レイアウトを復元 -->
          <div class="overlap-group">
            <div class="line"></div>
            <div class="img"></div>
            <div class="rectangle">
              <div class="sidebar-inner">
                <div class="text-wrapper-2">取引中の商品</div>
                <ul class="sidebar-trades">
                  @if(isset($sidebarItems) && $sidebarItems->count())
                    @foreach($sidebarItems as $sidebarItem)
                      @php
                        $isSeller = $item && $item->user && $sidebarItem->user_id === $item->user->id;
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
                    <li class="sidebar-trade-item" style="color:#ccc;">取引中の商品はありません</li>
                  @endif
                </ul>
              </div>
            </div>
          </div>
          <div class="text-wrapper-4">{{ $item ? '¥' . number_format($item->price) : '商品価格' }}</div>
          <div class="text-wrapper-5">{{ $item ? $item->name : '商品名' }}</div>
          <!-- チャット欄（purchaser.blade.phpと同じ構造・UI） -->
          <div class="chat-area">
            <ul class="chat-list">
              @foreach($chats as $chat)
                <li class="chat-message {{ $chat->user_id == auth()->id() ? 'my-message' : 'other-message' }}">
                  <div class="chat-user-info">
                    @if($chat->user && $chat->user->profile && $chat->user->profile->image_path)
                      <img src="{{ asset('storage/' . $chat->user->profile->image_path) }}" alt="{{ $chat->user->username }}" class="chat-user-img">
                    @else
                      <div class="chat-user-img chat-user-img--noimg">No Image</div>
                    @endif
                    <span class="chat-username">{{ $chat->user ? $chat->user->username : 'ユーザー' }}</span>
                  </div>
                  <div class="chat-comment">{{ $chat->comment }}</div>
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
            <form action="{{ route('chat.send', ['item_id' => $item->id]) }}" method="POST" class="chat-form">
              @csrf
              @if(isset($editChat))
                {{-- バリデーションエラー表示 --}}
                @if($errors->any())
                  <div class="chat-error-message" style="color:#d32f2f;margin-bottom:8px;">
                    @foreach($errors->all() as $error)
                      <div>{{ $error }}</div>
                    @endforeach
                  </div>
                @endif
                <textarea name="comment" class="chat-input" required>{{ old('comment', $editChat->comment) }}</textarea>
                <button type="submit" class="chat-send-btn">更新</button>
                <a href="{{ route('seller.chat', ['item_id' => $item->id]) }}" class="chat-send-btn" style="background:#aaa;">キャンセル</a>
              @else
                <textarea name="comment" class="chat-input" required placeholder="メッセージを入力">{{ old('comment') }}</textarea>
                <button type="submit" class="chat-send-btn">送信</button>
              @endif
            </form>
          </div>
          <!-- /チャット欄 -->
          <div class="overlap-wrapper">
            <div class="overlap-7">
              @if($item && $item->image_path)
                <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}" class="item-img" />
              @else
                <div class="text-wrapper-13">商品画像</div>
              @endif
            </div>
          </div>
          <div class="line-wrapper"><div class="line-2"></div></div>
      </div>
    </div>
</body>
</html>
