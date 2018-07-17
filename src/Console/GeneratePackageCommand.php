<?php

namespace Foundry\Framework\Console;


class GeneratePackageCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'foundry:generate:package
    {name : The name of the package }';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Generates a foundry package structure';


    public function handle()
    {
        $name = $this->argument('name');
        $roots = ['/src', '/config'];
        $files = ['/.gitignore','/.editorconfig','/.gitattributes','/readme.md'];
        $apiFolders = ['/Entities','/Models','/Repositories','/Services'];

        $base = base_path('packages/foundry');

        if(!$base)
            $this->createDirectory($base);

        $path = $base.'/'.$name;

        if(!$path){
            $this->createDirectory($path);

            foreach ($files as $file){
                fopen($path.$file, 'w');
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
