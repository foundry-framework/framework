<?php

namespace Foundry\Framework\Console\Migrations\Console;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use Exception;
use Foundry\Framework\Migrations\Configuration\ConfigurationProvider;
use Illuminate\Console\ConfirmableTrait;

/**
 * Class ResetCommand
 * refer to Laravel doctrine
 *
 * @package Foundry\Framework\Console\Migrations\Console
 *
 * @author Medard Ilunga
 */
class ResetCommand extends MigrationCommand
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'foundry:migrations:reset
    {plugin : Name of the plugin for which migrations need to be reset}
    {--connection= : For a specific connection.}';

    /**
     * @var string
     */
    protected $description = 'Reset all migrations for a given plugin';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * Execute the console command.
     *
     * @param ConfigurationProvider $provider
     * @param ManagerRegistry $registry
     * @throws Exception
     */
    public function handle(
        ConfigurationProvider $provider,
        ManagerRegistry $registry
    ){
        if (!$this->confirmToProceed()) {
            return;
        }

        $plugin = $this->argument('plugin');

        $configuration = $provider->getForConnection(
            $plugin,
            $this->option('connection')
        );

        $em = $registry->getManager($this->option('connection'));

        $this->connection = $configuration->getConnection();

        $this->safelyDropTables($plugin, $em);

        $this->info('Database was reset');
    }

    /**
     * @param $plugin
     * @param $em
     * @throws Exception
     */
    private function safelyDropTables($plugin, $em)
    {
        $this->throwExceptionIfPlatformIsNotSupported();

        $filterExpr = $this->getPluginTableFilterExpression($em, $plugin);

        $schema = $this->connection->getSchemaManager();
        $tables = $schema->listTableNames();
        foreach ($tables as $table) {
            if(preg_match($filterExpr, $table))
                $this->safelyDropTable($table);
        }
    }

    /**
     * @throws Exception
     */
    private function throwExceptionIfPlatformIsNotSupported()
    {
        $platformName = $this->connection->getDatabasePlatform()->getName();

        if (!array_key_exists($platformName, $this->getCardinalityCheckInstructions())) {
            throw new Exception(sprintf('The platform %s is not supported', $platformName));
        }
    }

    /**
     * @param string $table
     */
    private function safelyDropTable($table)
    {
        $platformName = $this->connection->getDatabasePlatform()->getName();
        $instructions = $this->getCardinalityCheckInstructions()[$platformName];

        $queryDisablingCardinalityChecks = $instructions['needsTableIsolation'] ?
                                                sprintf($instructions['disable'], $table) :
                                                $instructions['disable'];
        $this->connection->query($queryDisablingCardinalityChecks);

        $schema = $this->connection->getSchemaManager();
        $schema->dropTable($table);

        $queryEnablingCardinalityChecks = $instructions['needsTableIsolation'] ?
                                                sprintf($instructions['enable'], $table) :
                                                $instructions['enable'];
        $this->connection->query($queryEnablingCardinalityChecks);
    }

    /**
     * @return array
     */
    private function getCardinalityCheckInstructions()
    {
        return [
            'mssql' => [
                'needsTableIsolation'   => true,
                'enable'                => 'ALTER TABLE %s NOCHECK CONSTRAINT ALL',
                'disable'               => 'ALTER TABLE %s CHECK CONSTRAINT ALL',
            ],
            'mysql' => [
                'needsTableIsolation'   => false,
                'enable'                => 'SET FOREIGN_KEY_CHECKS = 1',
                'disable'               => 'SET FOREIGN_KEY_CHECKS = 0',
            ],
            'postgresql' => [
                'needsTableIsolation'   => true,
                'enable'                => 'ALTER TABLE %s ENABLE TRIGGER ALL',
                'disable'               => 'ALTER TABLE %s DISABLE TRIGGER ALL',
            ],
            'sqlite' => [
                'needsTableIsolation'   => false,
                'enable'                => 'PRAGMA foreign_keys = ON',
                'disable'               => 'PRAGMA foreign_keys = OFF',
            ],
        ];
    }
}
