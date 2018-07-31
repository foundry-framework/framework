<?php

namespace Foundry\Framework\Migration\Event;

use Doctrine\Common\EventArgs;
use Foundry\Framework\Migration\Configuration\Configuration;


/**
 * Class MigrationsEventArgs
 * Had to override Doctrine configuration due to private properties
 * not accessible in child class. Perhaps should do a PR to Doctrine
 *
 * @package Foundry\Framework\Migration\Event
 *
 * @author Medard Ilunga as per Doctrine
 */
class MigrationsEventArgs extends EventArgs
{
    /**
     * @var Configuration
     */
    private $config;

    /**
     * The direction of the migration.
     *
     * @var string (up|down)
     */
    private $direction;

    /**
     * Whether or not the migrations are executing in dry run mode.
     *
     * @var bool
     */
    private $dryRun;

    public function __construct(Configuration $config, $direction, $dryRun)
    {
        $this->config    = $config;
        $this->direction = $direction;
        $this->dryRun    = (bool) $dryRun;
    }

    public function getConfiguration()
    {
        return $this->config;
    }

    public function getConnection()
    {
        return $this->config->getConnection();
    }

    public function getDirection()
    {
        return $this->direction;
    }

    public function isDryRun()
    {
        return $this->dryRun;
    }
}
