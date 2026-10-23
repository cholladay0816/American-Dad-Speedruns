<?php

namespace App\Providers;

use App\Actions\Jetstream\DeleteUser;
use Illuminate\Support\ServiceProvider;
use Laravel\Jetstream\Jetstream;

class JetstreamServiceProvider extends ServiceProvider
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
        // Preserve the application's published Jetstream UI instead of replacing its design.
        $this->loadViewsFrom(resource_path('views/vendor/jetstream'), 'jetstream');
        foreach (glob(resource_path('views/vendor/jetstream/components/*.blade.php')) as $view) {
            $component = basename($view, '.blade.php');
            \Illuminate\Support\Facades\Blade::component('jetstream::components.'.$component, 'jet-'.$component);
        }
        \Livewire\Livewire::component('navigation-dropdown', \App\Http\Livewire\NavigationDropdown::class);
        $this->configurePermissions();

        Jetstream::deleteUsersUsing(DeleteUser::class);
    }

    /**
     * Configure the permissions that are available within the application.
     *
     * @return void
     */
    protected function configurePermissions()
    {
        Jetstream::defaultApiTokenPermissions(['read']);

        Jetstream::permissions([
            'create',
            'read',
            'update',
            'delete',
        ]);
    }
}
