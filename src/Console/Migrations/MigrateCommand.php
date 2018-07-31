<?php

namespace Foundry\Framework\Console\Migrations\Console;

use Foundry\Framework\Migration\Configuration\Configuration;
use Foundry\Framework\Migration\Configuration\ConfigurationProvider;
use Foundry\Framework\Migration\Migration;
use Foundry\Framework\Migration\Migrator;
use Illuminate\Console\ConfirmableTrait;
use LaravelDoctrine\Migrations\Exceptions\ExecutedUnavailableMigrationsException;
use LaravelDoctrine\Migrations\Exceptions\MigrationVersionException;


/**
 * Class MigrateCommand
 * refer to Laravel doctrine
 *
 * @package Foundry\Framework\Console\Migrations\Console
 *
 * @author Medard Ilunga
 */
class MigrateCommand extends MigrationCommand
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'foundry:migrations:migrate
    {plugin : Name of the plugin for which migrations need to be created}
    {version=latest : The version number (YYYYMMDDHHMMSS) or alias (first, prev, next, latest) to migrate to.}
    {--connection= : For a specific connection }
    {--write-sql= : The path to output the migration SQL file instead of executing it. }
    {--dry-run : Execute the migration as a dry run. }
    {--query-time= : Time all the queries individually. }
    {--force : Force the operation to run when in production. }';

    /**
     * @var string
     */
    protected $description = 'Execute a migration to a specified version or the latest available version for a given plugin.';

    /**
     * Execute the console command.
     *
     * @param ConfigurationProvider $provider
     * @param Migrator              $migrator
     *
     * @throws \Doctrine\DBAL\Migrations\MigrationException
     * @return int
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
                $this->option('connection') ?: null
            );

            $migration = null;

            try {
                $migration = new Migration(
                    $configuration,
                    $plugin,
                    $this->argument('version')
                );
            } catch (MigrationVersionException $e) {
                $this->error($e->getMessage());
            }

            try {
                $migration->checkIfNotExecutedUnavailableMigrations($plugin);
            } catch (ExecutedUnavailableMigrationsException $e) {
                $this->handleExecutedUnavailableMigrationsException($e, $configuration);
            }

            if ($path = $this->option('write-sql')) {
                $migrator->migrateToFile($migration, $path);
            } else {
                $migrator->migrate(
                    $migration,
                    $this->option('dry-run') ? true : false,
                    $this->option('query-time') ? true : false
                );
            }

            foreach ($migrator->getNotes() as $note) {
                $this->line($note);
            }
        }else{
            $this->line(sprintf('No "<info>%s </info>" plugin found!', camel_case(strtolower($plugin))));
        }

    }

    /**
     * @param ExecutedUnavailableMigrationsException $e
     * @param Configuration                          $configuration
     */
    protected function handleExecutedUnavailableMigrationsException(
        ExecutedUnavailableMigrationsException $e,
        Configuration $configuration
    ) {
        $this->error('WARNING! You have previously executed migrations in the database that are not registered migrations.');

        foreach ($e->getMigrations() as $migration) {
            $this->line(sprintf(
                '    <comment>>></comment> %s (<comment>%s</comment>)',
                $configuration->getDateTime($migration),
                $migration
            ));
        }

        if (!$this->confirm('Are you sure you wish to continue?')) {
            $this->error('Migration cancelled!');
            exit(1);
        }
    }
}
