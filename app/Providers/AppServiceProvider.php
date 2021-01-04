<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Blade::directive('cache', function($expression) {
            return "<?php if ( !App\RussianCaching::setUp({$expression})) { ?>";
        });

        Blade::directive('endcache', function() {
            return "<?php } echo App\RussianCaching::tearDown() ?>";
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        Blade::directive('th', function ($expression) {
            return "<?php echo str_ordinal({$expression}); ?>";
        });

    }
}
