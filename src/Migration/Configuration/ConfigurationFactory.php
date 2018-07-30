<?php

namespace Foundry\Framework\Migrations\Configuration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\MigrationException;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Container\Container;
use LaravelDoctrine\Migrations\Naming\DefaultNamingStrategy;

/**
 * Class ConfigurationFactory
 *
 * Foundry Custom configurationFactory class to handle generating migration files for a specific plugin
 *
 * @package Foundry\Framework\Migrations\Configuration
 *
 * @author Medard Ilunga
 */
class ConfigurationFactory
{
    /**
     * @var ConfigRepository
     */
    protected $config;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @param ConfigRepository $config
     * @param Container  $container
     */
    public function __construct(ConfigRepository $config, Container $container)
    {
        $this->config    = $config;
        $this->container = $container;
    }

    /**
     * @param Connection $connection
     * @param $plugin | The name of the plugin for which migrations are being generated
     * @param string $name | name of the migration if not the default
     *
     * @return Configuration
     */
    public function make(Connection $connection, $plugin, $name = null)
    {
        if ($name && $this->config->has('migrations.' . $name)) {
            $config = new Repository($this->config->get('migrations.' . $name, []));
        } else {
            $config = new Repository($this->config->get('migrations.default', []));
        }

        $configuration = new Configuration($connection);
        $configuration->setName($config->get('name', 'Doctrine Migrations'));
        $configuration->setMigrationsNamespace($config->get('namespace', 'Database\\Migrations'));
        $configuration->setMigrationsTableName($config->get('table', 'migrations'));

        $configuration->getConnection()->getConfiguration()->setFilterSchemaAssetsExpression(
            $config->get('schema.filter', '/^(?).*$/')
        );

        $configuration->setNamingStrategy($this->container->make(
            $config->get('naming_strategy', DefaultNamingStrategy::class)
        ));

        try {
            $configuration->setMigrationsFinder($configuration->getNamingStrategy()->getFinder());
        } catch (MigrationException $e) {}


        $directory = plugins_migrations_path($plugin);

        if(!is_dir($directory))
            mkdir($directory, 0777, true);

        $configuration->setMigrationsDirectory($directory);
        $configuration->setPluginName(camel_case(strtolower($plugin)));
        $configuration->registerMigrationsFromDirectory($directory);

        return $configuration;
    }
}
