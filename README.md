# coachtechフリマ

アイテムの出品と購入ができます。
<br>
購入後に、チャット機能により相手とチャットや評価ができます。
<br>
商品一覧画面（トップ画面）

![商品一覧画面](img/%E3%82%B9%E3%82%AF%E3%83%AA%E3%83%BC%E3%83%B3%E3%82%B7%E3%83%A7%E3%83%83%E3%83%88%202025-05-28%20143740.png)

## 利用者

### 会員登録

ユーザーは、ログインに関係なく、商品一覧画面・商品詳細画面が閲覧できます。
<br>
会員登録後、メール認証によりログインすることができます。

![alt text](img/image--22.png)

メール認証では、MailHog を使用しております。

![mailhog](img/image-1.png)

![メール認証](img/image-2.png)

![ログイン画面](img/image-3.png)

初回ログイン時に、プロフィール設定画面でプロフィール設定をします。
<br>
画像は、storage 内の profiles に保存されるようになっています。

![プロフィール設定画面](img/image-4.png)

プロフィール設定が終わると、商品一覧画面が閲覧できるようになります。
<br>
商品一覧では、自分が出品した商品は表示しないようになっております。
<br>
マイリストでは、「いいね」した商品と検索した商品が表示されます。
<br>
検索欄は、商品の部分一致の検索ができるようになっています。
<br>
購入された商品は、「SOLD」と表示するようになっております。

![商品一覧画面](img/%E3%82%B9%E3%82%AF%E3%83%AA%E3%83%BC%E3%83%B3%E3%82%B7%E3%83%A7%E3%83%83%E3%83%88%202025-05-28%20143740.png)

### 出品

商品出品画面より、商品を登録して出品することができます。
<br>
画像は、storage 内の items に保存されるようになっています。
<br>
カテゴリーでは、複数選択が可能です。
<br>
出品すると、商品一覧画面に切り替わります。

![商品出品画面](img/image-5.png)

商品一覧画面で、商品をクリックすると商品詳細画面に移動します。
<br>
商品詳細画面では、詳細な出品情報が表示されます。
<br>
ログインユーザーは「いいね」機能、コメント投稿、購入へと進むことができます。
<br>
未ログインユーザーは、商品一覧画面と商品詳細画面の閲覧のみとなります。

![商品詳細画面](img/image-8.png)

### マイページ

マイページ画面では、出品した商品・購入した商品が確認できます。
<br>
商品が購入されると、マイページ画面の「取引中の商品」に商品が表示されます。
<br>
星マークで評価の平均値が表示されるようになっております。
<br>
赤丸の数字は、相手からのチャット件数を表示しています。

![alt text](img/image-12.png)

マイページ画面の「取引中の商品」をクリックすると、チャット画面に移動します。
<br>
チャット画面では、サイドバーで他の取引中の商品を管理できます。
<br>
チャットでは、画像を送信することができます。
<br>
画像は、storage 内の chats に保存されるようになっています。
<br>
購入者から「取引を完了する」ボタンをクリックすると、出品者へ評価できます。

![alt text](img/image-13.png)

購入者から「取引を完了する」ボタンをクリックすると、出品者へ評価できます。
<br>
購入者が評価をすると、MailHogを使用して出品者へ取引完了通知メールが送信されるようになっております。
<br>
出品者は、評価された後に該当商品のチャット画面に移動すると、
<br>
自動で購入者へ評価するようになっております。
<br>
出品者の評価が完了すると、お互いのマイページ画面の「取引中の商品」タブから該当商品はなくなります。

![alt text](img/image-20.png)

![alt text](img/image-19.png)

マイページ編集画面では、初回ログイン時で設定したプロフィールを編集して更新できます。

![プロフィール編集画面](img/image-7.png)

### 購入

商品購入画面では、金額、支払い方法の選択、配送先の確認ができます。

![商品購入画面](img/image-9.png)

住所変更画面では、ユーザーが登録している住所が表示され、編集して更新することができます。
<br>
更新された住所は、商品購入画面の配送先に反映して表示されます。

![住所変更画面](img/image-10.png)

「購入する」ボタンをクリックすると、購入が完了して stripe の決済画面に移行します。

![stripe](img/image-11.png)

## 環境構築

1. クローンの実行
```
git clone git@github.com:taienobutaka/taie-flea-market.git
```
2. DockerDesktop アプリを立ち上げる
3. プロジェクト直下で、以下のコマンドを実行する
```
make init
```
<br>
   実行内容<br>
   : 開発用Dockerコンテナ起動<br>
   : PHP依存パッケージインストール<br>
   : StripeのPHPライブラリインストール<br>
   : .envファイル作成/自動修正<br>
   : 画像ストレージ用ディレクトリ作成/画像移動<br>
   : アプリケーションキー生成<br>
   : ストレージリンク作成<br>
   : 権限設定<br>
   : マイグレーション実行<br>
   : シーディング実行<br>

## メール認証

テスト環境で mailhog を使用しています
<br>
MailHog の設定

```
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=from@example.com
MAIL_FROM_NAME="${APP_NAME}"

```
```bash
version: "3.8"

services:
  nginx:
    image: nginx:1.21.1
    ports:
      - "80:80"
    volumes:
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
      - ./src:/var/www/
    depends_on:
      - php

  php:
    build: ./docker/php
    volumes:
      - ./src:/var/www/

  mysql:
    image: mysql:8.0.26
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: laravel_db
      MYSQL_USER: laravel_user
      MYSQL_PASSWORD: laravel_pass
    command: mysqld --default-authentication-plugin=mysql_native_password
    volumes:
      - ./docker/mysql/data:/var/lib/mysql
      - ./docker/mysql/my.cnf:/etc/mysql/conf.d/my.cnf

  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    environment:
      - PMA_ARBITRARY=1
      - PMA_HOST=mysql
      - PMA_USER=laravel_user
      - PMA_PASSWORD=laravel_pass
    depends_on:
      - mysql
    ports:
      - 8080:80

  mailhog:
    image: mailhog/mailhog
    ports:
      - "1025:1025"
      - "8025:8025"
```

## Stripe（テスト環境）の設定

環境設定ファイルの更新<br>
   `.env`ファイルに Stripe の API キーを追加

```
STRIPE_KEY=your_stripe_key
STRIPE_SECRET=your_stripe_secret_key
```

## 使用技術(実行環境)

- PHP 8.1.31
- Laravel 8.83.29
- MySQL 8.0.26

## ER 図

![alt text](img/image-18.png)

## テーブル仕様

![alt text](img/image-17.png)

## 機能一覧

![alt text](img/image-16.png)

## 基本設計

![alt text](img/image-15.png)

![基本設計2](img/image-14.png)

## URL

- 開発環境：http://localhost/
- phpMyAdmin：http://localhost:8080/
- mailhog：http://localhost:8025/

## テストについて

### テスト用ユーザーアカウント

| ユーザー名 | メールアドレス        | パスワード   |
|------------|----------------------|-------------|
| tanaka     | tanaka@gmail.com     | 11111111    |
| yamada     | yamada@gmail.com     | 22222222    |
| suzuki     | suzuki@gmail.com     | 33333333    |

### ユーザーとダミーデータ

| ユーザー名 | 商品id        |
|------------|----------------------|
| tanaka     | 1~5     |
| yamada     | 6~10    |
| suzuki     |      |


### Featureテスト実行方法

Docker環境で実行するため、<br>以下のコマンドを実行してください。

```
docker-compose exec php bash
```
```
php artisan test tests/Feature
```

全てのFeatureテストが実装済みで、上記コマンドで一括実行・確認できます。
