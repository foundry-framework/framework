<?php

namespace Foundry\Framework\Console\Migrations\Console;

use Doctrine\DBAL\Migrations\MigrationException;
use Foundry\Framework\Migration\Configuration\ConfigurationProvider;
use Foundry\Framework\Migration\Migrator;
use Illuminate\Console\ConfirmableTrait;

/**
 * Class ExecuteCommand
 * refer to Laravel doctrine
 *
 * @package Foundry\Framework\Console\Migrations\Console
 *
 * @author Medard Ilunga
 */
class ExecuteCommand extends MigrationCommand
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'foundry:migrations:execute
    {plugin : Name of the plugin for which a migration is to be executed} 
    {version : The version to execute }
    {--connection= : For a specific connection.}
    {--write-sql : The path to output the migration SQL file instead of executing it. }
    {--dry-run : Execute the migration as a dry run. }
    {--up : Execute the migration up. }
    {--down : Execute the migration down. }
    {--query-time : Time all the queries individually.}
    {--force : Force the operation to run when in production. }';

    /**
     * @var string
     */
    protected $description = 'Execute a single migration version for a given plugin up or down manually.';

    /**
     * Execute the console command.
     *
     * @param ConfigurationProvider $provider
     * @param Migrator $migrator
     * @throws MigrationException
     * @throws \Exception
     */
    public function handle(ConfigurationProvider $provider, Migrator $migrator)
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        $plugin = $this->argument('plugin');

        if($this->isPlugin($plugin)){
            $configuration = $provider->getForConnection(
                $plugin,
                $this->option('connection')
            );

            $version   = $this->argument('version');
            $direction = $this->option('down') ? 'down' : 'up';

            try {
                $version = $configuration->getVersion($version);
            } catch (MigrationException $e) {}

            if ($path = $this->option('write-sql')) {
                $migrator->executeToFile($version, $direction, $path);
            } else {
                $migrator->execute($version, $direction, $this->option('dry-run'), $this->option('query-time'));
            }

            foreach ($migrator->getNotes() as $note) {
                $this->line($note);
            }
        }else{
            $this->line(sprintf('No "<info>%s </info>" plugin found!', camel_case(strtolower($plugin))));
        }

    }
}
