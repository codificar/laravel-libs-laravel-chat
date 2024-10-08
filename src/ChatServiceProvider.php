<?php

namespace Codificar\Chat;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Codificar\Chat\Middleware\CheckUserSystem;

class ChatServiceProvider extends ServiceProvider 
{
    public function boot()
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');

        // Load blade templates
        $this->loadViewsFrom(__DIR__.'/resources/views', 'chat');

        // Load trans files
        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'laravelchat');

        // Load Migrations
        $this->loadMigrationsFrom(__DIR__.'/Database/migrations');

        // Load seeds
        $this->publishes([
            __DIR__.'/Database/seeders' => database_path('seeders')
        ], 'public_vuejs_libs');


        $this->publishes([
            __DIR__.'/../public/js' => public_path('vendor/codificar/chat'),
            __DIR__.'/../public/files' => public_path('vendor/codificar/chat'),
        ], 'public_vuejs_libs');

        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('chatapp', CheckUserSystem::class);
        $router->aliasMiddleware('talk',  \Nahid\Talk\Middleware\TalkMiddleware::class);
    }

    public function register()
    {

    }
}