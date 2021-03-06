<?php

namespace Foundry\Framework\Console;

/**
 * Generate Foundry Package structure
 *
 * Class GeneratePackageCommand
 *
 * @package Foundry\Framework\Console
 *
 * @author Medard Ilunga
 */
class GeneratePluginCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'foundry:generate:plugin
    {name : The name of the plugin }
    {--description= : The description of this plugin}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Generates a foundry package structure';


    public function handle()
    {

        /**
         * Package name and description
         */
        $name = camel_case(strtolower($this->argument('name')));
        $description = $this->option('description');

        /**
         * Composer file content
         */
        $composer = "{\n\t\"name\": \"foundry-plugins/".$name.
                    "\",\n\t\"description\": \"".$description.
                    "\",\n\t\"type\": \"foundry-plugins\",\n\t\"require\": {\n\t},".
                    "\n\t\"autoload\": {\n\t\t\"psr-4\": {\n\t\t\t\"Foundry\\\\".ucfirst($name)."\\\\\": \"src/\"\n\t\t}\n\t},".
                    "\n\t\"minimum-stability\": \"dev\",\n\t\"prefer-stable\": true\t\n}";

        /**
         * Roots folders
         */
        $roots = ['/src', '/config', '/migrations'];

        /**
         * Roots files
         */
        $files = ['/.gitignore' => '',
                    '/.editorconfig' => '',
                    '/.gitattributes' => '',
                    '/readme.md' => '',
                    '/composer.json' => $composer];

        /**
         * Api folders
         */
        $apiFolders = ['/Entities','/Models','/Repositories','/Services'];

        $base = base_path('plugins/foundry');

        if(!$base)
            $this->createDirectory($base);

        $path = $base.'/'.$name;

        if(!is_dir($path)){
            $this->createDirectory($path);

            foreach ($files as $file => $content){
                $f = fopen($path.$file, 'w');
                fwrite($f, $content);
                fclose($f);
            }

            foreach ($roots as $dir){
               $this->createDirectory($path.$dir);
            }

            $api = $path.'/src/Api';

            $this->createDirectory($api);

            foreach ($apiFolders as $dir){
                $this->createDirectory($api.$dir);
            }

            $this->message('Package structure created successfully!');

        }else
            $this->message('Package with same name already exists!', 'red');
    }

    private function createDirectory($path){
        mkdir($path, 0777, true);
    }
}
