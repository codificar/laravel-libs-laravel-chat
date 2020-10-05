# laravel-chat
A chat library for laravel.

## Prerequisites
- 1º: Add the "codificar/talk" library before install this library.
```
"codificar/talk": "dev-development@dev"
```
- 2º: These middwares are needed:
- If your project does not have some of these middleware, it is necessary to add them.
```
auth.admin
auth.provider
auth.user
auth.provider_api
auth.user_api
```

## Getting Started
- In root of your Laravel app in the composer.json add this code to clone the project:

```

"repositories": [
		{
			"type":"package",
			"package": {
			  "name": "codificar/chat",
			  "version":"master",
			  "source": {
				  "url": "https://libs:ofImhksJ@git.codificar.com.br/laravel-libs/laravel-chat.git",
				  "type": "git",
				  "reference":"master"
				}
			}
		}
	],

// ...

"require": {
    // ADD this
    "codificar/chat": "dev-master",
},

```

- Now add 
```

"autoload": {
        //...
        "psr-4": {
            // Add your Lib here
            "Codificar\\Chat\\": "vendor/codificar/chat/src",
            //...
        }
    },
    //...
```

- Dump the composer autoloader

```
composer dump-autoload -o
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

- Next, we need to add our new Service Provider in our `config/app.php` inside the `providers` array:

```
'providers' => [
         ...,
            // The new package class
            Codificar\Chat\LaravelChatServiceProvider::class,
        ],
```
- Migrate the database tables

```
php artisan migrate
```

And finally, start the application by running:

```
php artisan serve
```