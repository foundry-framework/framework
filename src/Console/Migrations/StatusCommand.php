<?php

namespace Foundry\Framework\Console\Migrations\Console;

use Foundry\Framework\Migration\Configuration\Configuration;
use Foundry\Framework\Migration\Configuration\ConfigurationProvider;


/**
 * Class StatusCommand
 * refer to Laravel doctrine
 *
 * @package Foundry\Framework\Console\Migrations\Console
 *
 * @author Medard Ilunga
 */
class StatusCommand extends MigrationCommand
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'foundry:migrations:status
    {plugin : Name of the plugin for which migrations need to be created}
    {--connection= : For a specific connection.}
    {--show-versions : This will display a list of all available migrations and their status.}';

    /**
     * @var string
     */
    protected $description = 'View the status of a set of migrations for a given plugin.';

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

            $formattedVersions = [];
            foreach (['prev', 'current', 'next', 'latest'] as $alias) {
                $version = $configuration->resolveVersionAlias($alias);
                if ($version === null) {
                    if ($alias == 'next') {
                        $formattedVersions[$alias] = 'Already at latest version for '.camel_case(strtolower($plugin)). ' plugin';
                    } elseif ($alias == 'prev') {
                        $formattedVersions[$alias] = 'Already at first version for '.camel_case(strtolower($plugin)).' plugin';
                    }
                } elseif ($version === '0') {
                    $formattedVersions[$alias] = '<comment>0</comment>';
                } else {
                    $formattedVersions[$alias] = $configuration->getDateTime($version) . ' (<comment>' . $version . '</comment>)';
                }
            }

            $executedMigrations               = $configuration->getMigratedVersions();
            $availableMigrations              = $configuration->getAvailableVersions();
            $executedUnavailableMigrations    = array_diff($executedMigrations, $availableMigrations);
            $numExecutedUnavailableMigrations = count($executedUnavailableMigrations);
            $newMigrations                    = count(array_diff($availableMigrations, $executedMigrations));

            $this->line("\n <info>==</info> Configuration\n");

            $info = [
                'Database Driver'                 => $configuration->getConnection()->getDriver()->getName(),
                'Database Name'                   => $configuration->getConnection()->getDatabase(),
                'Version Table Name'              => $configuration->getMigrationsTableName(),
                'Migrations Namespace'            => $configuration->getMigrationsNamespace(),
                'Migrations Directory'            => $configuration->getMigrationsDirectory(),
                'Previous Version'                => $formattedVersions['prev'],
                'Current Version'                 => $formattedVersions['current'],
                'Next Version'                    => $formattedVersions['next'],
                'Latest Version'                  => $formattedVersions['latest'],
                'Executed Migrations'             => count($executedMigrations),
                'Executed Unavailable Migrations' => $numExecutedUnavailableMigrations > 0 ? '<error>' . $numExecutedUnavailableMigrations . '</error>' : 0,
                'Available Migrations'            => count($availableMigrations),
                'New Migrations'                  => $newMigrations > 0 ? '<question>' . $newMigrations . '</question>' : 0
            ];
            foreach ($info as $name => $value) {
                $this->line('    <comment>>></comment> ' . $name . ': ' . str_repeat(' ', 50 - strlen($name)) . $value);
            }

            if ($this->option('show-versions')) {
                if ($migrations = $configuration->getMigrations()) {
                    $executedUnavailableMigrations = $migrations;
                    $this->line("\n <info>==</info> Available Migration Versions for ".camel_case(strtolower($plugin))." plugin\n");

                    $this->showVersions($migrations, $configuration);
                }

                if (!empty($executedUnavailableMigrations)) {
                    $this->line("\n <info>==</info> Previously Executed Unavailable Migration Versions for ".camel_case(strtolower($plugin))." plugin\n");
                    $this->showVersions($executedUnavailableMigrations, $configuration);
                }
            }
        }else{
            $this->line(sprintf('No "<info>%s </info>" plugin found!', camel_case(strtolower($plugin))));
        }
    }

    /**
     * @param array         $migrations
     * @param Configuration $configuration
     */
    protected function showVersions(array $migrations = [], Configuration $configuration)
    {
        $migratedVersions = $configuration->getMigratedVersions();

        foreach ($migrations as $version) {
            $isMigrated           = in_array($version->getVersion(), $migratedVersions);
            $status               = $isMigrated ? '<info>migrated</info>' : '<error>not migrated</error>';
            $migrationDescription = '';
            if ($version->getMigration()->getDescription()) {
                $migrationDescription = str_repeat(' ', 5) . $version->getMigration()->getDescription();
            }
            $formattedVersion = $configuration->getDateTime($version->getVersion());

            $this->line('    <comment>>></comment> ' . $formattedVersion .
                ' (<comment>' . $version->getVersion() . '</comment>)' .
                str_repeat(' ', 49 - strlen($formattedVersion) - strlen($version->getVersion())) .
                $status . $migrationDescription);
        }
    }
}
