<?php

namespace Foundry\Framework\Console\Migrations\Console;

use Foundry\Framework\Migration\Configuration\ConfigurationProvider;
use Foundry\Framework\Migration\MigrationFileGenerator;


/**
 * Class GenerateCommand
 * refer to Laravel doctrine
 *
 * @package Foundry\Framework\Console\Migrations\Console
 *
 * @author Medard Ilunga
 */
class GenerateCommand extends MigrationCommand
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'foundry:migrations:generate
    {plugin : Name of the plugin for which migrations need to be created}
    {--connection= : The entity manager connection to generate the migration for.}
    {--create= : The table to be created.}
    {--table= : The table to migrate.}';

    /**
     * @var string
     */
    protected $description = 'Generate a blank migration class for a given plugin.';

    /**
     * Execute the console command.
     *
     * @param ConfigurationProvider  $provider
     * @param MigrationFileGenerator $generator
     */
    public function handle(ConfigurationProvider $provider, MigrationFileGenerator $generator)
    {
        $plugin = $this->argument('plugin');

        if($this->isPlugin($plugin)){
            $configuration = $provider->getForConnection($plugin, $this->option('connection'));

            $filename = $generator->foundryGenerate(
                $configuration,
                $this->option('create'),
                $this->option('table')
            );

            $this->line(sprintf('<info>Created Migration:</info> %s', $filename));
        }else{
            $this->line(sprintf('No "<info>%s </info>" plugin found!', camel_case(strtolower($plugin))));
        }

    }
}
