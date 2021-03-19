<?php

namespace Tino\Plugins;

use Illuminate\Support\ServiceProvider;

abstract class TinoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $instances = [];

        foreach ($this->plugins() as $plugin) {
            $instances[$plugin] = $this->app->register($plugin);
        }

        Tino::plugins($instances);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $widgets = collect($this->widgets())->map(function ($class) {
            return $this->app->make($class);
        })->toArray();

        Tino::widgets($widgets);

        \Blade::directive('hook', function ($name) {
            return "<?php if (\Tino\Plugins\Tino::hasHook($name)) { 
                collect(\Tino\Plugins\Tino::getHookHandlers($name))
                    ->each(function (\$hook) {
                        echo resolve(\$hook)->handle();
                    });
            } ?>";
        });
    }

    /**
     * Dashboard widgets.
     *
     * @return array
     */
    abstract protected function widgets();

    /**
     * List of registered plugins.
     *
     * @return array
     */
    abstract protected function plugins();
}
