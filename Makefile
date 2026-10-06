.PHONY: help serve dev migrate fresh seed test tinker cs routes

serve:
	php artisan serv

dev:
	composer run dev

migrate:
	php artisan migrate

routes:
	php artisan route:list
