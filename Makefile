up:
	cp .env.prod .env
	docker compose up -d --build

down:
	docker compose down

restart: down up

shell:
	docker compose exec app bash

migrate:
	docker compose exec app php artisan migrate

seed:
	docker compose exec app php artisan db:seed

fresh:
	docker compose exec app php artisan migrate:fresh --seed

tinker:
	docker compose exec app php artisan tinker

test:
	docker compose exec app php artisan test

logs:
	docker compose logs -f
