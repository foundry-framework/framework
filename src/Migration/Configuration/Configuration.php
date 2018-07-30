<?php

namespace Foundry\Framework\Migrations\Configuration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use LaravelDoctrine\Migrations\Configuration\Configuration as Base;
use Doctrine\DBAL\Migrations\MigrationException;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Foundry\Framework\Migrations\Version;
use LaravelDoctrine\Migrations\Naming\NamingStrategy;


class Configuration extends Base
{
    /**
     * @var NamingStrategy
     */
    protected $namingStrategy;

    /**
     * @var string
     */
    protected $pluginName;

    /**
     * Prevent write queries.
     *
     * @var bool
     */
    private $isDryRun = false;

    /**
     * Flag for whether or not the migration table has been created
     *
     * @var boolean
     */
    private $migrationTableCreated = false;

    /**
     * The migration column name to track the name of the plugin a given version belongs to
     *
     * @var string
     */
    private $migrationsPluginName = 'plugin';

    /**
     * @return NamingStrategy
     */
    public function getNamingStrategy()
    {
        return $this->namingStrategy;
    }

    /**
     * Returns the migration plugin name
     *
     * @return string $migrationsPluginName The migration plugin name
     */
    public function getMigrationsPluginName()
    {
        return $this->migrationsPluginName;
    }

    /**
     * @param NamingStrategy $namingStrategy
     */
    public function setNamingStrategy(NamingStrategy $namingStrategy)
    {
        $this->namingStrategy = $namingStrategy;
    }

    /**
     * @param string $name
     */
    public function setPluginName($name)
    {
        $this->pluginName = $name;
    }

    /**
     * @return string
     */
    public function getPluginName()
    {
        return $this->pluginName;
    }

    /**
     * Returns all migrated versions for a given plugin from the versions table, in an array.
     *
     * @param $plugin | plugin name
     * @return Version[]
     */
    public function getPluginMigratedVersions($plugin)
    {
        try {
            $this->createMigrationTable();
        } catch (DBALException $e) {
        } catch (MigrationException $e) {}

        if ( ! $this->migrationTableCreated && $this->isDryRun) {
            return [];
        }

        $this->connect();

        $ret = $this->getConnection()->fetchAll("SELECT " . $this->getQuotedMigrationsColumnName() .
                                                    " FROM " . $this->getMigrationsTableName().
                                                    " WHERE ".$this->getMigrationsPluginName()." = \"". camel_case(strtolower($plugin))."\"");

        return array_map('current', $ret);
    }

    /**
     * Returns an array of available migration version numbers for the current plugin.
     *
     * @return array
     */
    public function getAvailableVersions()
    {
        $availableVersions = [];

        $this->registerMigrationsFromDirectory($this->getMigrationsDirectory());

        foreach ($this->getMigrations() as $migration) {
            $availableVersions[] = $migration->getVersion();
        }

        return $availableVersions;
    }


    /**
     * Create the migration table to track migrations with.
     *
     * @return boolean Whether or not the table was created.
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Migrations\MigrationException
     */
    public function createMigrationTable()
    {
        $this->validate();

        if ($this->migrationTableCreated) {
            return false;
        }

        $this->connect();
        if ($this->getConnection()->getSchemaManager()->tablesExist([$this->getMigrationsTableName()])) {
            $this->migrationTableCreated = true;

            return false;
        }

        if ($this->isDryRun) {
            return false;
        }

        $columns = [
            $this->getMigrationsColumnName() => $this->getMigrationsColumn(),
            $this->getMigrationsPluginName() => $this->getMigrationsPlugin(),
        ];

        $table   = new Table($this->getMigrationsTableName(), $columns);
        $table->setPrimaryKey([$this->getMigrationsColumnName()]);
        $this->getConnection()->getSchemaManager()->createTable($table);

        $this->migrationTableCreated = true;

        return true;
    }


    /**
     * @return Column
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getMigrationsColumn() : Column
    {
        return new Column($this->getMigrationsColumnName(), Type::getType('string'), ['length' => 255]);
    }

    /**
     * @return Column
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getMigrationsPlugin() : Column
    {
        return new Column($this->getMigrationsPluginName(), Type::getType('string'), ['length' => 255]);
    }

    /**
     * Returns the quoted migration plugin name
     *
     * @param Connection $connection
     * @return string The quoted migration plugin name
     *
     */
    public function getQuotedMigrationsPluginName(Connection $connection)
    {
        try {
            return $this->getMigrationsPlugin()->getQuotedName($connection->getDatabasePlatform());
        } catch (DBALException $e) {}

        return null;
    }

    /**
     * Returns the array of migrations to executed based on the given direction
     * and target version number.
     *
     * @param string $direction The direction we are migrating.
     * @param string $to        The version to migrate to.
     *
     * @return Version[] $migrations   The array of migrations we can execute.
     */
    public function getMigrationsToExecute($direction, $to)
    {
        if (empty($this->getMigrations())) {
            $this->registerMigrationsFromDirectory($this->getMigrationsDirectory());
        }

        if ($direction === Version::DIRECTION_DOWN) {
            if (count($this->getMigrations())) {
                $allVersions = array_reverse(array_keys($this->getMigrations()));
                $classes     = array_reverse(array_values($this->getMigrations()));
                $allVersions = array_combine($allVersions, $classes);
            } else {
                $allVersions = [];
            }
        } else {
            $allVersions = $this->getMigrations();
        }
        $versions = [];
        $migrated = $this->getMigratedVersions();
        foreach ($allVersions as $version) {
            if ($this->shouldExecuteMigration($direction, $version, $to, $migrated)) {
                $versions[$version->getVersion()] = $version;
            }
        }

        return $versions;
    }

    /**
     * Check if we should execute a migration for a given direction and target
     * migration version.
     *
     * @param string  $direction The direction we are migrating.
     * @param Version $version   The Version instance to check.
     * @param string  $to        The version we are migrating to.
     * @param array   $migrated  Migrated versions array.
     *
     * @return boolean
     */
    private function shouldExecuteMigration($direction, Version $version, $to, $migrated)
    {
        if ($direction === Version::DIRECTION_DOWN) {
            if ( ! in_array($version->getVersion(), $migrated, true)) {
                return false;
            }

            return $version->getVersion() > $to;
        }

        if ($direction === Version::DIRECTION_UP) {
            if (in_array($version->getVersion(), $migrated, true)) {
                return false;
            }

            return $version->getVersion() <= $to;
        }
    }


    /**
     * Returns the Version instance for a given version in the format YYYYMMDDHHMMSS.
     *
     * @param string $version The version string in the format YYYYMMDDHHMMSS.
     *
     * @return Version
     *
     * @throws MigrationException Throws exception if migration version does not exist.
     */
    public function getVersion($version)
    {
        if (empty($this->getMigrations())) {
            $this->registerMigrationsFromDirectory($this->getMigrationsDirectory());
        }

        if ( ! isset($this->getMigrations()[$version])) {
            throw MigrationException::unknownMigrationVersion($version);
        }

        return $this->getMigrations()[$version];
    }

    /**
     * Register an array of migrations. Each key of the array is the version and
     * the value is the migration class name.
     *
     *
     * @param array $migrations
     *
     * @return Version[]
     * @throws MigrationException
     */
    public function registerMigrations(array $migrations)
    {
        $versions = [];
        foreach ($migrations as $version => $class) {
            $versions[] = $this->registerMigration($version, $class);
        }

        return $versions;
    }
}
