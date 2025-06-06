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
          <div class="text-wrapper">「ユーザー名」さんとの取引画面</div>
          <div class="overlap-group">
            <div class="line" style="background:#ccc;height:4px;width:1508px;position:absolute;top:103px;left:0;"></div>
            <div class="img" style="background:#ccc;height:4px;width:1502px;position:absolute;top:338px;left:0;"></div>
            <div class="rectangle"></div>
            <div class="text-wrapper-2">その他の取引</div>
          </div>
          <div class="ellipse"></div>
          <div class="text-wrapper-3">商品価格</div>
          <div class="text-wrapper-4">商品名</div>
          <div class="ellipse-2"></div>
          <div class="overlap-2">
            <div class="text-wrapper-5">ユーザー名</div>
            <div class="rectangle-2"></div>
          </div>
          <div class="overlap-3">
            <div class="rectangle-3"></div>
            <div class="ellipse-3"></div>
            <div class="text-wrapper-6">ユーザー名</div>
          </div>
          <div class="div-wrapper"><div class="text-wrapper-7">取引メッセージを記入してください</div></div>
          <div class="overlap-5">
            <div class="group">
              <div class="overlap-6">
                <div class="text-wrapper-10">画像を追加</div>
                <div class="rectangle-3"></div>
                <img class="send-image" src="/img/send.jpg" alt="送信画像" />
              </div>
            </div>
          </div>
          <div class="overlap-wrapper">
            <div class="overlap-6"><div class="text-wrapper-9">商品画像</div></div>
          </div>
          <div class="text-wrapper-11">編集</div>
          <div class="text-wrapper-12">削除</div>
          <div class="overlap-7"><div class="text-wrapper-13">取引を完了する</div></div>
        </div>
        <div class="line-wrapper"><div class="line-2"></div></div>
      </div>
    </div>
</body>
</html>
