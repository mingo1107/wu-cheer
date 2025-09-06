## Clone Project

1. Clone or Download the project

2. do the following commands
`$ cd <project-directory>` Navigate to the project directory

## Installation backend

`$ cp .env.example .env`

`$ composer install`  

`$ php artisan key:generate` 

`$ php artisan jwt:secret` 

`$ php artisan migrate:install`

`$ php artisan migrate`

`$ php artisan storage:link`

`$ composer dump-autoload`

`$ php artisan db:seed --class=AuthoritiesSeeder`

`$ php artisan db:seed --class=AuthoritiesToPersonnelSeeder`

### Installation nodejs

`$ nvm install v15.14`

`$ npm install`

### dynamic watching resources (js css)
`$ npm run watch`