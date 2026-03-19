<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

class AppServiceProvider extends ServiceProvider
{
    
    protected $prestashop;

    public function register(): void{ }
    
    public function boot(): void{ }
}
