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
class GeneratePackageCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'foundry:generate:package
    {name : The name of the package }
    {--description= : The description of this package}';

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
        $name = camel_case($this->argument('name'));
        $description = $this->option('description');

        /**
         * Composer file content
         */
        $composer = "{\n\t\"name\": \"Foundry/".ucfirst($name).
                    "\",\n\t\"description\": \"".$description.
                    "\",\n\t\"type\": \"plugins/foundry\",\n\t\"require\": {\n\t\t\"composer/installers\": \"~1.0\"\n\t}\n}";

        /**
         * Roots folders
         */
        $roots = ['/src', '/config'];

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

        $base = base_path('packages/foundry');

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
