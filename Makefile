init:
	@echo "=== 開発用Dockerコンテナ起動 ==="
	docker-compose up -d --build

	@echo "=== MySQLの起動待ち ==="
	@until docker-compose exec mysql mysqladmin ping -hmysql -uroot -proot --silent; do \
		echo "Waiting for MySQL..."; \
		sleep 2; \
	done
	@sleep 3

	@echo "=== PHP依存パッケージインストール ==="
	docker-compose exec php composer install

	@echo "=== StripeのPHPライブラリインストール ==="
	docker-compose exec php composer require stripe/stripe-php

	@echo "=== .envファイル作成 ==="
	@if [ ! -f src/.env ]; then cp src/.env.example src/.env; fi

	@echo "=== .envファイル自動修正 ==="
	sed -i 's/^DB_DATABASE=.*/DB_DATABASE=laravel_db/' src/.env
	sed -i 's/^DB_USERNAME=.*/DB_USERNAME=laravel_user/' src/.env
	sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=laravel_pass/' src/.env
	sed -i 's/^DB_HOST=.*/DB_HOST=mysql/' src/.env

	@echo "=== 画像ストレージ用ディレクトリ作成 ==="
	@mkdir -p ./src/storage/app/public/img

	@echo "=== 画像ストレージ用ディレクトリに画像移動 ==="
	@if [ -d ./src/public/img/copy_storage_img ]; then mv ./src/public/img/copy_storage_img/*.jpg ./src/storage/app/public/img || true; fi

	@echo "=== アプリケーションキー生成 ==="
	docker-compose exec php php artisan key:generate

	@echo "=== ストレージリンク作成 ==="
	docker-compose exec php php artisan storage:link

	@echo "=== 権限設定 ==="
	docker-compose exec php chmod -R 777 storage bootstrap/cache

	@echo "=== マイグレーション実行 ==="
	docker-compose exec php php artisan migrate

	@echo "=== シーディング実行 ==="
	docker-compose exec php php artisan db:seed

	@echo "=== 開発環境初期化完了 ==="

fresh:
	docker-compose exec php php artisan migrate:fresh --seed

restart:
	@make down
	@make up

up:
	docker-compose up -d

down:
	docker-compose down --remove-orphans

cache:
	docker-compose exec php php artisan cache:clear
	docker-compose exec php php artisan config:cache

stop:
	docker-compose stop
