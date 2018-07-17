<?php

namespace Foundry\Framework;

use Foundry\Framework\Console\GeneratePackageCommand;
use Illuminate\Support\ServiceProvider;


class FoundryServiceProvider extends ServiceProvider
{
    /**
     * Boot service provider.
     */
    public function boot()
    {

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfig();
        $this->registerConsoleCommands();
    }


    /**
     * Merge config
     */
    protected function mergeConfig()
    {
        $this->mergeConfigFrom(
            $this->getConfigPath(), 'auth'
        );

    }

    /**
     * @return string
     */
    protected function getConfigPath()
    {
        return __DIR__ . '/../config/auth.php';
    }

    /**
     * Register console commands
     */
    protected function registerConsoleCommands()
    {
        $this->commands([
            GeneratePackageCommand::class,
        ]);
    }

}
