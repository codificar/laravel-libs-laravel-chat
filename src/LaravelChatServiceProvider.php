<?php

namespace Codificar\Chat;

use Illuminate\Support\ServiceProvider;

class LaravelChatServiceProvider extends ServiceProvider 
{
    public function boot()
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
    }

    public function register()
    {

    }
}