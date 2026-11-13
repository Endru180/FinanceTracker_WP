<?php

namespace App\Providers;

use App\Services\FinanceStore;
use App\Support\Format;
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
        $this->registerFormatDirective('rupiah', 'rupiah');
        $this->registerFormatDirective('fdate', 'date');
    }

    private function registerFormatDirective(string $directive, string $method): void
    {
        Blade::directive(
            $directive,
            fn ($expression) => '<?php echo e(\\'.Format::class."::{$method}({$expression})); ?>"
        );
    }
}
