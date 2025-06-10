<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品評価のお知らせ</title>
    <style>
        body {
            font-family: 'Hiragino Sans', 'Hiragino Kaku Gothic ProN', Meiryo, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #ffffff;
            padding: 30px;
            border: 1px solid #e9ecef;
            border-radius: 0 0 8px 8px;
        }
        .rating {
            font-size: 24px;
            color: #ffc107;
            margin: 20px 0;
        }
        .item-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            font-size: 14px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>商品評価のお知らせ</h1>
    </div>
    
    <div class="content">
        <p>{{ $seller->profile->username ?? $seller->name }}さん、</p>
        
        <p>{{ $purchaser->profile->username ?? $purchaser->name }}さんが、あなたの商品に評価を付けました。</p>
        
        <div class="item-info">
            <h3>商品情報</h3>
            <p><strong>商品名：</strong>{{ $item->name }}</p>
            <p><strong>価格：</strong>¥{{ number_format($item->price) }}</p>
            <p><strong>出品者：</strong>{{ $seller->profile->username ?? $seller->name }}</p>
            <p><strong>評価者：</strong>{{ $purchaser->profile->username ?? $purchaser->name }}</p>
        </div>
        
        <div class="rating">
            <strong>評価：</strong>
            @for($i = 1; $i <= 5; $i++)
                <span style="color: {{ $i <= $rating ? '#ffc107' : '#e9ecef' }};">★</span>
            @endfor
            <span style="margin-left: 10px;">({{ $rating }}/5)</span>
        </div>
        
        <p>評価を確認するには、以下のリンクからログインしてマイページにアクセスしてください。</p>
        
        <p style="margin-top: 30px;">
            <a href="{{ url('/login') }}" style="background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">
                ログインしてマイページを確認する
            </a>
        </p>
    </div>
    
    <div class="footer">
        <p>このメールは自動送信されています。返信はできませんのでご了承ください。</p>
        <p>coachtechフリマ</p>
    </div>
</body>
</html> 