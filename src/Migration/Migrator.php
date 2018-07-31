<?php

namespace Foundry\Framework\Migration;


/**
 * Class Migrator
 *
 * @package Foundry\Framework\Migrations
 *
 * @author Medard Ilunga
 */
class Migrator
{
    /**
     * @var array
     */
    protected $notes = [];

    /**
     * @param Migration $migration
     * @param bool|false $dryRun
     * @param bool|false $timeQueries
     * @throws \Doctrine\DBAL\Migrations\MigrationException
     */
    public function migrate(Migration $migration, $dryRun = false, $timeQueries = false)
    {
        $sql = $migration->migrate(
            $migration->getVersion(),
            $dryRun,
            $timeQueries
        );

        $this->writeNotes($migration, $timeQueries, $sql);
    }

    /**
     * @param Version $version
     * @param            $direction
     * @param bool|false $dryRun
     * @param bool|false $timeQueries
     * @throws \Exception
     */
    public function execute(Version $version, $direction, $dryRun = false, $timeQueries = false)
    {
        $version->execute($direction, $dryRun, $timeQueries);

        $verb = $direction === 'down' ? 'Rolled back' : 'Migrated';

        $this->note($version->getVersion(), $version, $timeQueries, $verb);
    }

    /**
     * @param Migration $migration
     * @param string|bool $path
     * @throws \Doctrine\DBAL\Migrations\MigrationException
     */
    public function migrateToFile(Migration $migration, $path)
    {
        $path = is_bool($path) ? getcwd() : $path;

        $sql = $migration->getSql($migration->getVersion());
        $migration->writeSqlFile($path, $migration->getVersion());

        $this->writeNotes($migration, false, $sql);
    }

    /**
     * @param Version $version
     * @param         $direction
     * @param         $path
     * @throws \Doctrine\DBAL\Migrations\MigrationException
     */
    public function executeToFile(Version $version, $direction, $path)
    {
        $path = is_bool($path) ? getcwd() : $path;

        $version->writeSqlFile($path, $direction);

        $verb = $direction === 'down' ? 'Rolled back' : 'Migrated';

        $this->note($version->getVersion(), $version, false, $verb);
    }

    /**
     * @return array
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param Migration $migration
     * @param           $timeQueries
     * @param           $sql
     * @throws \Doctrine\DBAL\Migrations\MigrationException
     */
    protected function writeNotes(Migration $migration, $timeQueries, $sql)
    {
        if (count($sql) < 1) {
            $this->notes[] = '<info>Nothing to migrate.</info>';
        }

        foreach ($sql as $versionName => $sq) {
            $this->note(
                $versionName,
                $migration->getConfiguration()->getVersion($versionName),
                $timeQueries
            );
        }
    }

    /**
     * @param         $versionName
     * @param Version $version
     * @param bool    $timeQueries
     * @param string  $verb
     */
    protected function note($versionName, Version $version, $timeQueries = false, $verb = 'Migrated')
    {
        $msg = "<info>{$verb}:</info> $versionName";

        if ($timeQueries) {
            $msg .= " ({$version->getTime()}s)";
        }

        $this->notes[] = $msg;
    }
}
