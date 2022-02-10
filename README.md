# laravel-chat
A chat library for laravel.

## Prerequisites


- Add These middwares are needed:
- If your project does not have some of these middleware, it is necessary to add them.
```
auth.admin
auth.provider
auth.user
auth.provider_api
auth.user_api
```

## Getting Started

Add in composer.json:

```php
"repositories": [
    {
        "type": "vcs",
        "url": "https://libs:ofImhksJ@git.codificar.com.br/laravel-libs/laravel-chat.git"
    }
]
```

```php
require:{
        "codificar/chat": "0.1.0",
}
```

```php
"autoload": {
    "psr-4": {
        "Codificar\\Chat\\": "vendor/codificar/chat/src/"
    },
}
```
Update project dependencies:

```shell
$ composer update
```

Register the service provider in `config/app.php`:

```php
'providers' => [
  /*
   * Package Service Providers...
   */
  Codificar\Chat\LaravelChatServiceProvider::class,
],
```



Check if has the laravel publishes in composer.json with public_vuejs_libs tag:

```
    "scripts": {
        //...
		"post-autoload-dump": [
			"@php artisan vendor:publish --tag=public_vuejs_libs --force"
		]
	},
```

Or publish by yourself


Publish Js Libs and Tests:

```shell
$ php artisan vendor:publish --tag=public_vuejs_libs --force
```

- Migrate the database tables

```shell
php artisan migrate
```
