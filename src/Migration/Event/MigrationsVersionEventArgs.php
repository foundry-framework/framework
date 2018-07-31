<?php

namespace Foundry\Framework\Migration\Event;

use Foundry\Framework\Migration\Configuration\Configuration;
use Foundry\Framework\Migration\Version;

/**
 * Class MigrationsVersionEventArgs
 * Had to override Doctrine configuration due to private properties
 * not accessible in child class. Perhaps should do a PR to Doctrine
 *
 * @package Foundry\Framework\Migration\Event
 *
 * @author Medard Ilunga as per Doctrine
 */
class MigrationsVersionEventArgs extends MigrationsEventArgs
{
    /**
     * The version the event pertains to.
     *
     * @var Version
     */
    private $version;

    public function __construct(Version $version, Configuration $config, $direction, $dryRun)
    {
        parent::__construct($config, $direction, $dryRun);
        $this->version = $version;
    }

    public function getVersion()
    {
        return $this->version;
    }
}
