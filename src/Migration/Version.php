<?php

namespace Foundry\Framework\Migrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Migrations\MigrationException;
use Doctrine\DBAL\Migrations\Provider\LazySchemaDiffProvider;
use Doctrine\DBAL\Migrations\Provider\SchemaDiffProvider;
use Doctrine\DBAL\Migrations\Provider\SchemaDiffProviderInterface;
use Doctrine\DBAL\Migrations\Version as Base;
use Foundry\Framework\Migrations\Configuration\Configuration;

/**
 * Class Version
 *
 * @package Foundry\Framework\Migrations
 *
 * @author Medard Ilunga
 */
class Version extends Base
{

    /**
     * The Migrations Configuration instance for this migration
     *
     * @var Configuration
     */
    private $configuration;

    /**
     * The version in timestamp format (YYYYMMDDHHMMSS)
     *
     * @var string
     */
    private $version;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /** @var SchemaDiffProviderInterface */
    private $schemaProvider;

    public function __construct(Configuration $configuration, $version, $class, SchemaDiffProviderInterface $schemaProvider = null)
    {
        parent::__construct($configuration, $version, $class, $schemaProvider);

        $this->configuration = $configuration;
        $this->connection    = $configuration->getConnection();
        $this->version       = $version;

        if ($schemaProvider !== null) {
            $this->schemaProvider = $schemaProvider;
        }
        if ($schemaProvider === null) {
            try {
                $schemaProvider = new SchemaDiffProvider(
                    $this->connection->getSchemaManager(),
                    $this->connection->getDatabasePlatform()
                );
            } catch (DBALException $e) {
            }
            $this->schemaProvider = LazySchemaDiffProvider::fromDefaultProxyFactoryConfiguration($schemaProvider);
        }
    }

    /**
     * Mark a migration file has executed by inserting it into the migrations table in the DB
     */
    public function markMigrated()
    {
        $this->markVersion('up');
    }

    /**
     * Insert or delete a migration in the DB
     *
     * @param $direction
     */
    private function markVersion($direction)
    {
        $action = $direction === 'up' ? 'insert' : 'delete';

        try {
            $this->configuration->createMigrationTable();
        } catch (DBALException $e) {
        } catch (MigrationException $e) {}

        $this->connection->$action(
            $this->configuration->getMigrationsTableName(),
            [
                $this->configuration->getQuotedMigrationsColumnName() => $this->version,
                $this->configuration->getQuotedMigrationsPluginName($this->connection) => $this->configuration->getPluginName()
            ]
        );
    }

    /**
     * Mark a migration file has not executed by removing it from the migrations table in the DB
     */
    public function markNotMigrated()
    {
        $this->markVersion('down');
    }

}
