<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class ViewServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Blade::component('layouts.app', 'app-layout');
    }
} 