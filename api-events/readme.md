# Events API

API for managing events.

## Official Documentation

Coming Soon

## Server Requirements

* PHP >= 7.0
* OpenSSL PHP Extension
* PDO PHP Extension
* Mbstring PHP Extension

Same as lumen. See http://lumen.laravel.com/

## Setup

`git clone path/to/project`

Make sure to copy the `.env.example` file and rename to `.env` You will need to enter your DB credentials, make your own APP_KEY (Lumen does not have an artisan command to generate one).

*Your DB needs to be an instance of core's database.*

`composer install`

`php -S localhost:5000 -t public`

Go to `localhost:5000` in your browser of choice.
