<?php

namespace JeroenNoten\LaravelAdminLte;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use JeroenNoten\LaravelAdminLte\Console\AdminLteInstallCommand;
use JeroenNoten\LaravelAdminLte\Console\AdminLtePluginCommand;
use JeroenNoten\LaravelAdminLte\Console\AdminLteStatusCommand;
use JeroenNoten\LaravelAdminLte\Console\AdminLteUpdateCommand;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use JeroenNoten\LaravelAdminLte\Http\ViewComposers\AdminLteComposer;

class AdminLteServiceProvider extends BaseServiceProvider
{
    /**
     * Register the package services.
     *
     * @return void
     */
    public function register()
    {
        // Bind a singleton instance of the AdminLte class into the service
        // container.

        $this->app->singleton(AdminLte::class, function (Container $app) {
            return new AdminLte(
                $app['config']['adminlte.filters'],
                $app['events'],
                $app
            );
        });
    }

    /**
     * Bootstrap the package's services.
     *
     * @return void
     */
    public function boot(Factory $view, Dispatcher $events, Repository $config)
    {
        $this->loadViews();
        $this->loadTranslations();
        $this->loadConfig();
        $this->registerCommands();
        $this->registerViewComposers($view);
        $this->registerMenu($events, $config);
        $this->loadComponents();
    }

    /**
     * Load the package views.
     *
     * @return void
     */
    private function loadViews()
    {
        $viewsPath = $this->packagePath('resources/views');
        $this->loadViewsFrom($viewsPath, 'adminlte');
    }

    /**
     * Load the package translations.
     *
     * @return void
     */
    private function loadTranslations()
    {
        $translationsPath = $this->packagePath('resources/lang');
        $this->loadTranslationsFrom($translationsPath, 'adminlte');
    }

    /**
     * Load the package config.
     *
     * @return void
     */
    private function loadConfig()
    {
        $configPath = $this->packagePath('config/adminlte.php');
        $this->mergeConfigFrom($configPath, 'adminlte');
    }

    /**
     * Get the absolute path to some package resource.
     *
     * @param string $path The relative path to the resource
     * @return string
     */
    private function packagePath($path)
    {
        return __DIR__."/../$path";
    }

    /**
     * Register the package's artisan commands.
     *
     * @return void
     */
    private function registerCommands()
    {
        $this->commands([
            AdminLteInstallCommand::class,
            AdminLteStatusCommand::class,
            AdminLteUpdateCommand::class,
            AdminLtePluginCommand::class,
        ]);
    }

    /**
     * Register the package's view composers.
     *
     * @return void
     */
    private function registerViewComposers(Factory $view)
    {
        $view->composer('adminlte::page', AdminLteComposer::class);
    }

    /**
     * Register the menu events handlers.
     *
     * @return void
     */
    private static function registerMenu(Dispatcher $events, Repository $config)
    {
        // Register a handler for the BuildingMenu event, this handler will add
        // the menu defined on the config file to the menu builder instance.

        $events->listen(
            BuildingMenu::class,
            function (BuildingMenu $event) use ($config) {
                $menu = $config->get('adminlte.menu', []);
                $menu = is_array($menu) ? $menu : [];
                $event->menu->add(...$menu);
            }
        );
    }

    /**
     * Load the blade view components.
     *
     * @return void
     */
    private function loadComponents()
    {
        // Support of x-components is only available for Laravel >= 7.x
        // versions. So, we check if we can load components.

        $canLoadComponents = method_exists(
            'Illuminate\Support\ServiceProvider',
            'loadViewComponentsAs'
        );

        if (! $canLoadComponents) {
            return;
        }

        // Form components.

        $this->loadViewComponentsAs('adminlte', [
            Components\Button::class,
            Components\DateRange::class,
            Components\Input::class,
            Components\InputColor::class,
            Components\InputDate::class,
            Components\InputFile::class,
            Components\InputSlider::class,
            Components\InputSwitch::class,
            Components\Select::class,
            Components\Select2::class,
            Components\SelectBs::class,
            Components\Textarea::class,
            Components\TextEditor::class,
        ]);

        // Tool components.

        $this->loadViewComponentsAs('adminlte', [
            Components\Datatable::class,
            Components\Modal::class,
        ]);

        // Widgets components.

        $this->loadViewComponentsAs('adminlte', [
            Components\Alert::class,
            Components\Callout::class,
            Components\Card::class,
            Components\InfoBox::class,
            Components\ProfileColItem::class,
            Components\ProfileRowItem::class,
            Components\ProfileWidget::class,
            Components\Progress::class,
            Components\SmallBox::class,
        ]);
    }
}
