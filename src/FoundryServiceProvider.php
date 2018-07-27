<?php

namespace Foundry\Framework;

use Foundry\Framework\Console\GenerateEntityCommand;
use Foundry\Framework\Console\GeneratePluginCommand;
use Foundry\Framework\Console\Migrations\Console\DiffCommand;
use Foundry\Framework\Console\Migrations\Console\ExecuteCommand;
use Foundry\Framework\Console\Migrations\Console\GenerateCommand;
use Foundry\Framework\Console\Migrations\Console\LatestCommand;
use Foundry\Framework\Console\Migrations\Console\MigrateCommand;
use Foundry\Framework\Console\Migrations\Console\ResetCommand;
use Foundry\Framework\Console\Migrations\Console\StatusCommand;
use Illuminate\Support\ServiceProvider;

/**
 * Class FoundryServiceProvider
 * @package Foundry\Framework
 *
 * @author Medard Ilunga
 */
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
    private function mergeConfig()
    {
        $this->mergeConfigFrom(
            $this->getConfigPath(), 'auth'
        );

    }

    /**
     * @return string
     */
    private function getConfigPath()
    {
        return __DIR__ . '/../config/auth.php';
    }

    /**
     * Register console commands
     */
    private function registerConsoleCommands()
    {
        $this->commands([
            GeneratePluginCommand::class,
            GenerateEntityCommand::class,
            DiffCommand::class,
            ExecuteCommand::class,
            GenerateCommand::class,
            LatestCommand::class,
            MigrateCommand::class,
            ResetCommand::class,
            StatusCommand::class
        ]);
    }


}
