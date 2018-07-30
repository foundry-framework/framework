<?php

namespace Foundry\Framework\Console\Migrations\Console;

use Doctrine\Common\Persistence\ObjectManager;
use Foundry\Framework\Console\Command;
use Symfony\Component\Finder\Finder;

class MigrationCommand extends Command
{


    /**
     * Check if a given name is a plugin
     *
     * @param $plugin | name of the plugin to check
     *
     * @return bool
     */
    protected function isPlugin($plugin){
        return is_dir(plugin_path($plugin));
    }

    /**
     * Get table names of all entities of the particular plugin as a regex expression
     *
     * @param ObjectManager $em
     * @param $plugin | Plugin name
     *
     * @return string
     */
    protected function getPluginTableFilterExpression(ObjectManager $em, $plugin){

        $finder = new Finder();
        $finder->files()->name('*.php')->in(plugin_entities_path($plugin));

        $namespace = plugin_entities_namespace($plugin).'\\';

        $regex = '/';

        foreach ($finder as $file){

            $class = $namespace.basename($file,'.php');
            $entity = new $class();

            $name = $em->getClassMetadata(get_class($entity))->getTableName();

            if(strcmp($regex, '/') !== 0)
                $regex .= '|';

            $regex .= '^'.$name. '$';

        }

        return $regex;
    }

}
