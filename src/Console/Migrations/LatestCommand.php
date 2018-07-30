<?php

namespace Foundry\Framework\Console\Migrations\Console;

use Foundry\Framework\Migrations\Configuration\ConfigurationProvider;

/**
 * Class LatestCommand
 * refer to Laravel doctrine
 *
 * @package Foundry\Framework\Console\Migrations\Console
 *
 * @author Medard Ilunga
 */
class LatestCommand extends MigrationCommand
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'foundry:migrations:latest
    {plugin : Name of the plugin}
    {--connection= : For a specific connection.}';

    /**
     * @var string
     */
    protected $description = 'Outputs the latest version number';

    /**
     * Execute the console command.
     *
     * @param ConfigurationProvider $provider
     */
    public function handle(ConfigurationProvider $provider)
    {
        $plugin = $this->argument('plugin');

        if($this->isPlugin($plugin)){
            $configuration = $provider->getForConnection(
                $plugin,
                $this->option('connection')
            );

            $this->line('<info>Latest version:</info> ' . $configuration->getLatestVersion());
        }else{
            $this->line(sprintf('No "<info>%s </info>" plugin found!', camel_case(strtolower($plugin))));
        }


    }
}
