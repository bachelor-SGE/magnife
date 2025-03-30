<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Передаём переменную $u во все шаблоны
        View::composer('*', function ($view) {
            if (!isset($view->getData()['u'])) {
                $view->with('u', auth()->user());
            }

            // Передаём переменную $page по умолчанию
            if (!isset($view->getData()['page'])) {
                $view->with('page', 'Magnife'); // Можно заменить на любое название
            }
        });
    }
}
