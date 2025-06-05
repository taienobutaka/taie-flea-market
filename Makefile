init:
	@echo "=== 開発用Dockerコンテナ起動 ==="
	docker-compose up -d

	@echo "=== MySQLの起動待ち ==="
	@until docker-compose exec mysql mysqladmin ping -hlocalhost -uroot --silent; do \
		echo "Waiting for MySQL..."; \
		sleep 2; \
	done

	@echo "=== PHP依存パッケージインストール ==="
	docker-compose exec php composer install

	@echo "=== StripeのPHPライブラリインストール ==="
	docker-compose exec php composer require stripe/stripe-php

	@echo "=== .envファイル作成 ==="
	@if [ ! -f src/.env ]; then cp src/.env.example src/.env; fi

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

	@echo "=== npmパッケージインストール ==="
	docker-compose exec node npm install

	@echo "=== npm run dev 実行 ==="
	docker-compose exec node npm run dev

	@echo "=== 開発環境初期化完了 ==="
