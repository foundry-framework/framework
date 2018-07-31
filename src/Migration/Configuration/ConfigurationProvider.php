<?php

namespace Foundry\Framework\Migration\Configuration;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;

/**
 * Class ConfigurationProvider
 *
 * @package Foundry\Framework\Migration\Configuration
 *
 * @author Medard Ilunga
 */
class ConfigurationProvider
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var ConfigurationFactory
     */
    protected $factory;

    /**
     * @param ManagerRegistry      $registry
     * @param ConfigurationFactory $factory
     */
    public function __construct(ManagerRegistry $registry, ConfigurationFactory $factory)
    {
        $this->registry = $registry;
        $this->factory  = $factory;
    }

    /**
     * @param $plugin | name of foundry plugin
     * @param string|null $name
     *
     * @return Configuration
     */
    public function getForConnection($plugin, $name = null)
    {
        /**
         * @var $connection Connection
         */
        $connection = $this->registry->getConnection($name);

        return $this->factory->make($connection, $plugin, $name);
    }
}
