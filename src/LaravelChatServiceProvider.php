<?php

namespace Codificar\Chat;

use Illuminate\Support\ServiceProvider;

class LaravelChatServiceProvider extends ServiceProvider 
{
    public function boot()
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');

        // Load blade templates
        $this->loadViewsFrom(__DIR__.'/resources/views', 'chat');

        $this->publishes([
            __DIR__.'/../public/js' => public_path('vendor/codificar/chat'),
        ], 'public_vuejs_libs');

    }

    public function register()
    {

    }
}