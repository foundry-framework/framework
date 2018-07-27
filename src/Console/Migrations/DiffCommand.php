<?php

namespace Foundry\Framework\Console\Migrations\Console;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\Migrations\Provider\OrmSchemaProvider;
use Doctrine\ORM\EntityManagerInterface;
use Foundry\Framework\Migrations\Configuration\ConfigurationProvider;
use Illuminate\Console\Command;
use LaravelDoctrine\Migrations\Output\MigrationFileGenerator;
use LaravelDoctrine\Migrations\Output\SqlBuilder;
use Symfony\Component\Finder\Finder;

class DiffCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'foundry:migrations:diff
    {plugin : Name of the plugin for which migrations need to be created}
    {--connection= : For a specific connection }
    {--filter-expression= : Tables which are filtered by Regular Expression.}';

    /**
     * @var string
     */
    protected $description = 'Generate a migration for a given foundry plugin by comparing your current database to the foundry plugin mapping information.';

    /**
     * Execute the console command.
     *
     * @param ConfigurationProvider  $provider
     * @param ManagerRegistry        $registry
     * @param SqlBuilder             $builder
     * @param MigrationFileGenerator $generator
     */
    public function handle(
        ConfigurationProvider $provider,
        ManagerRegistry $registry,
        SqlBuilder $builder,
        MigrationFileGenerator $generator
    ) {

        $plugin = $this->argument('plugin');

        $configuration = $provider->getForConnection($plugin, $this->option('connection'));
        $em            = $registry->getManager($this->option('connection'));
        $connection    = $configuration->getConnection();

        $this->info($plugin);

//        // Overrule the filter
//        if ($filterExpr = $this->option('filter-expression')) {
//            $connection->getConfiguration()->setFilterSchemaAssetsExpression($filterExpr);
//        }
//
//        $fromSchema = $connection->getSchemaManager()->createSchema();
//        $toSchema   = $this->getSchemaProvider($em)->createSchema();
//
//
//        // Drop tables which don't suffice to the filter regex
//        if ($filterExpr = $connection->getConfiguration()->getFilterSchemaAssetsExpression()) {
//            foreach ($toSchema->getTables() as $table) {
//                $tableName = $table->getName();
//                if (!preg_match($filterExpr, $this->resolveTableName($tableName))) {
//                    $toSchema->dropTable($tableName);
//                }
//            }
//        }
//
//        $up   = $builder->up($configuration, $fromSchema, $toSchema);
//        $down = $builder->down($configuration, $fromSchema, $toSchema);
//
//        if (!$up && !$down) {
//            return $this->error('No changes detected in your mapping information.');
//        }
//
//        $path = $generator->generate(
//            $configuration,
//            false,
//            false,
//            $up,
//            $down
//        );

        $this->line(sprintf('Generated new migration class to "<info>%s</info>" from schema differences.', $path));
    }

    /**
     * @param EntityManagerInterface $em
     *
     * @return OrmSchemaProvider
     */
    protected function getSchemaProvider(EntityManagerInterface $em)
    {
        return new OrmSchemaProvider($em);
    }

    /**
     * Resolve a table name from its fully qualified name. The `$name` argument
     * comes from Doctrine\DBAL\Schema\Table#getName which can sometimes return
     * a namespaced name with the form `{namespace}.{tableName}`. This extracts
     * the table name from that.
     *
     * @param string $name
     *
     * @return string
     */
    protected function resolveTableName($name)
    {
        $pos = strpos($name, '.');

        return false === $pos ? $name : substr($name, $pos + 1);
    }

    protected function getPluginTableFilterExpression(ObjectManager $em, $plugin){

        $finder = new Finder();
        $finder->files()->name('*.php')->in(app_path('plugins/foundry/'.$plugin.'/src/Api/Entities/'));

        $namespace = 'Foundry\\'.ucfirst($plugin).'\Api\Entities\\';

        $tables = array();

        foreach ($finder as $file){

            $class = $namespace.basename($file,'.php');
            $entity = new $class();

            array_push($tables, $em->getClassMetadata(get_class($entity))->getTableName());
        }
    }
}
