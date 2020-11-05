<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use NumberFormatter;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Blade::directive('th', function ($expression) {
            return "<?php echo (new NumberFormatter('en_US', NumberFormatter::ORDINAL))->format({$expression}); ?>";
        });
    }
}
