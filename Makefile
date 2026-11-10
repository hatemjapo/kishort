.PHONY: help serve dev migrate fresh seed test tinker cs routes

install:
	composer install && npm install

serve:
	php artisan serv

dev:
	composer run dev

migration:
	php artisan migrate

routes:
	php artisan route:list

controller:
	php artisan make:controller

tinker:
	php artisan tinker
