<?php

namespace App\Providers;

use App\Services\FinanceStore;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FinanceStore::class);
    }

    public function boot(): void
    {
        Blade::directive('rupiah', fn ($expression) => "<?php echo e(\\App\\Support\\Format::rupiah($expression)); ?>");
        Blade::directive('fdate', fn ($expression) => "<?php echo e(\\App\\Support\\Format::date($expression)); ?>");
    }
}