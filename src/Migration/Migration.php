<?php

namespace Foundry\Framework\Migrations;

use Doctrine\DBAL\Migrations\Events;
use Doctrine\DBAL\Migrations\Migration as DBALMigration;
use Doctrine\DBAL\Migrations\MigrationException;
use Doctrine\DBAL\Migrations\OutputWriter;
use Foundry\Framework\Migrations\Configuration\Configuration;
use LaravelDoctrine\Migrations\Exceptions\ExecutedUnavailableMigrationsException;
use LaravelDoctrine\Migrations\Exceptions\MigrationVersionException;
use Doctrine\DBAL\Migrations\Event\MigrationsEventArgs;
use const COUNT_RECURSIVE;

/**
 * Class Migration
 * @package Foundry\Framework\Migrations
 *
 * @author Medard Ilunga
 */
class Migration extends DBALMigration
{
    /**
     * @var string|null
     */
    protected $version;

    /**
     * @var boolean
     */
    private $noMigrationException;

    /**
     * The OutputWriter object instance used for outputting information
     *
     * @var OutputWriter
     */
    private $outputWriter;

    /**
     * @var string
     */
    protected $plugin;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @param Configuration $configuration
     * @param string $plugin | plugin name
     * @param string $version
     *
     */
    public function __construct(Configuration $configuration, $plugin, $version = 'latest')
    {
        parent::__construct($configuration);

        $this->configuration        = $configuration;
        $this->outputWriter         = $configuration->getOutputWriter();
        $this->plugin               = $plugin;
        $this->noMigrationException = false;

        $this->setVersion($configuration, $version);
    }

    /**
     * @param Configuration $configuration
     * @param string        $versionAlias
     */
    protected function setVersion(Configuration $configuration, $versionAlias)
    {
        $version = $configuration->resolveVersionAlias($versionAlias);

        if ($version === null || $version === false) {
            if ($versionAlias == 'prev') {
                throw new MigrationVersionException('Already at first version');
            }
            if ($versionAlias == 'next') {
                throw new MigrationVersionException('Already at latest version');
            }

            throw new MigrationVersionException(sprintf('Unknown version: %s', e($versionAlias)));
        }

        $this->version = $version;
    }

    /**
     * @param $plugin | plugin name
     * @throws ExecutedUnavailableMigrationsException
     */
    public function checkIfNotExecutedUnavailableMigrations($plugin)
    {
        $configuration = $this->configuration;

        $executedUnavailableMigrations = array_diff(
            $configuration->getPluginMigratedVersions($plugin),
            $configuration->getAvailableVersions()
        );

        if (count($executedUnavailableMigrations) > 0) {
            throw new ExecutedUnavailableMigrationsException($executedUnavailableMigrations);
        }
    }


    /**
     * Run a migration to the current version or the given target version.
     *
     * @param string $to The version to migrate to.
     * @param boolean $dryRun Whether or not to make this a dry run and not execute anything.
     * @param boolean $timeAllQueries Measuring or not the execution time of each SQL query.
     * @param callable|null $confirm A callback to confirm whether the migrations should be executed.
     *
     * @return array An array of migration sql statements. This will be empty if the the $confirm callback declines to execute the migration
     *
     * @throws MigrationException
     * @throws \Exception
     */
    public function migrate($to = null, $dryRun = false, $timeAllQueries = false, callable $confirm = null)
    {
        /**
         * If no version to migrate to is given we default to the last available one.
         */
        if ($to === null) {
            $to = $this->configuration->getLatestVersion();
        }

        $from = (string) $this->configuration->getCurrentVersion();
        $to   = (string) $to;

        /**
         * Throw an error if we can't find the migration to migrate to in the registered
         * migrations.
         */
        $migrations = $this->configuration->getMigrations();
        if ( ! isset($migrations[$to]) && $to > 0) {
            throw MigrationException::unknownMigrationVersion($to);
        }

        $direction           = $from > $to ? Version::DIRECTION_DOWN : Version::DIRECTION_UP;
        $migrationsToExecute = $this->configuration->getMigrationsToExecute($direction, $to);

        /**
         * If
         *  there are no migrations to execute
         *  and there are migrations,
         *  and the migration from and to are the same
         * means we are already at the destination return an empty array()
         * to signify that there is nothing left to do.
         */
        if ($from === $to && empty($migrationsToExecute) && ! empty($migrations)) {
            return $this->noMigrations();
        }

        if ( ! $dryRun && false === $this->migrationsCanExecute($confirm)) {
            return [];
        }

        $output  = $dryRun ? 'Executing dry run of migration' : 'Migrating';
        $output .= ' <info>%s</info> to <comment>%s</comment> from <comment>%s</comment>';
        $this->outputWriter->write(sprintf($output, $direction, $to, $from));

        /**
         * If there are no migrations to execute throw an exception.
         */
        if (empty($migrationsToExecute) && ! $this->noMigrationException) {
            throw MigrationException::noMigrationsToExecute();
        } elseif (empty($migrationsToExecute)) {
            return $this->noMigrations();
        }

        $this->configuration->dispatchEvent(
            Events::onMigrationsMigrating,
            new MigrationsEventArgs($this->configuration, $direction, $dryRun)
        );

        $sql  = [];
        $time = 0;

        foreach ($migrationsToExecute as $version) {
            $versionSql                  = $version->execute($direction, $dryRun, $timeAllQueries);
            $sql[$version->getVersion()] = $versionSql;
            $time                       += $version->getTime();
        }

        $this->configuration->dispatchEvent(
            Events::onMigrationsMigrated,
            new MigrationsEventArgs($this->configuration, $direction, $dryRun)
        );

        $this->outputWriter->write("\n  <comment>------------------------</comment>\n");
        $this->outputWriter->write(sprintf("  <info>++</info> finished in %ss", $time));
        $this->outputWriter->write(sprintf("  <info>++</info> %s migrations executed", count($migrationsToExecute)));
        $this->outputWriter->write(sprintf("  <info>++</info> %s sql queries", count($sql, COUNT_RECURSIVE) - count($sql)));

        return $sql;
    }


    /**
     * @return string|null
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function noMigrations() : array
    {
        $this->outputWriter->write('<comment>No migrations to execute.</comment>');

        return [];
    }

    public function migrationsCanExecute(callable $confirm = null) : bool
    {
        return null === $confirm ? true : (bool) $confirm();
    }
}
