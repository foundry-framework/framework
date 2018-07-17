<?php

namespace Foundry\Framework\Console;


use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;

class GenerateEntityCommand extends Command
{

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'foundry:generate:entity
    {package: The package for which this entity is for}
    {name : The name of the entity }
    {--properties= : Comma separated properties of the entity with their respective types and optional required or not.
                    the structure is nameOfColumn:Type:required (e.g: name:string, last_name:string:false, count:int, ... )
                    By default all columns are considered string and required }
    {--user: Should this entity extend the User Class? }
    {--table=: The name of the database table related to this entity, only if name is different to entity name}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Generates a foundry package structure';


    public function handle()
    {
        $timestamps = $this->confirm("Does the Entity require timestamps (deleted_at, created_at, updated_at)[Y/N]? ");

        $package = $this->argument('package');
        $isUser = $this->option('user');
        $table = $this->option('table')?
                                    strtolower(preg_replace('/\s+/\-/','_', $this->option('table'))):
                                    strtolower(preg_replace('/\s+/\-/','_', $this->argument('name')));

        $name = ucfirst(camel_case($this->argument('name')));
        $fields = $this->getFields($this->option('properties'));

        $root = base_path('packages/foundry'.camel_case($package));

        if(is_dir($root)){

            /**
             * Get directories
             */

            $dir = $root.'/src/Api';

            if(!is_dir($dir))
                $this->message('The src/Api folder was not found in '. $package. ' package');

            $entityFolder = $dir.'/Entities';
            $modelFolder = $dir.'/Models';
            $servicesFolder = $dir. '/Services';
            $repoFolder = $dir.'/Repositories';

            if(!is_dir($entityFolder))
                $this->createDirectory($entityFolder);

            if(!is_dir($modelFolder)){
                $this->createDirectory($modelFolder);
            }

            if(!is_dir($servicesFolder))
                $this->createDirectory($servicesFolder);

            if(!is_dir($repoFolder))
                $this->createDirectory($repoFolder);


            /**
             * Create classes
             */

            $entity = $this->initEntityClass($package, $name, $table, $fields, $timestamps, $isUser);

            $this->createFile($entityFolder.'/'. $name, $entity);

        }else
            $this->message('Package '. $package. ' does not exist!');

    }

    /**
     * Init an Entity class
     *
     * @param string $package | Name of the root package
     * @param string $name | Name of the Entity
     * @param string $table | Database name of the table related to this entity
     * @param array $fields | Associative array of Entity properties
     * @param bool $timestamps | Should this entity have timestamps
     * @param bool $isUser | Should this entity extend the User abstract class
     *
     * @return ClassType
     */
    private function initEntityClass(string $package, string $name, string $table, array $fields, bool $timestamps, bool $isUser) : ClassType
    {
        $namespace = new PhpNamespace(ucfirst(strtolower($package)).'\\Api\\Entities');

        $psr4 = $isUser? 'Foundry\\Framework\\Api\\Entities\\User': 'Foundry\\Framework\\Api\\Entities\\Entity';
        $namespace->addUse($psr4);

        $namespace->addUse('Doctrine\\ORM\\Mapping');

        return $this->createEntityClass($namespace, $name, $table, $fields, $timestamps, $isUser);

    }

    /**
     * Create a file if doesn't exist and append content
     *
     * @param $path
     * @param $content
     *
     * @return void
     */
    private function createFile($path, $content) : void
    {
        $file = fopen($path, 'w');
        fwrite($file, $content);
        fclose($file);
    }

    /**
     * Create Directory
     *
     * @param $path | path to the directory
     *
     * @return void
     */
    private function createDirectory($path) : void
    {
        mkdir($path, 0777, true);
    }

    /**
     * Get various properties of the class to be created
     *
     * @param string $fields | string representation of class properties
     *
     * @return array
     */
    private function getFields(string $fields) : array
    {
        $properties = array();

        if($fields){
            $fields = explode(',', $fields);

            foreach ($fields as $field){

                $required = true;
                $type = 'string';

                if(strpos($field, ':')){

                    $field = explode(':', $field);

                    if(in_array($field[1], $this->getDBTypes()))
                        $type = $field[1];

                    if(isset($field[2]))
                        $required = $field[2];

                    $field = $field[0];
                }

                $field = preg_replace('/\s+/\-/','_', $field);

                array_push($properties, [
                    'name' => $field,
                    'type' => $type,
                    'required' => $required
                ]);
            }

        }

        return $properties;
    }

    /**
     * Add class properties
     *
     * @param ClassType $class | The Entity class
     * @param array $property | array of class property
     * @param string $visibility | type of property
     *
     * @return ClassType
     */
    private function addProperty(ClassType $class, array  $property, $visibility = 'private') : ClassType
    {
        $column = '@Mapping\\column(type="'.$property['type'].'") \n';

        if($property['required']){
            $column = '@Mapping\\column(type="'.$property['type'].'", nullable=true) \n';
        }

        $name = strtolower($property['name']);

        $class->addProperty($name)
                ->setVisibility($visibility)
                ->addComment('@var '.$property['type'])
                ->addComment($column);

        $class = $this->addMethod($class, $name, $property['type']);

        return $class;
    }

    /**
     * Add a class method
     *
     * @param ClassType $class | Class object
     * @param string $column | Name of column whose methods need to be added
     * @param string $type_hint | type of column
     *
     * @return ClassType
     */
    private function addMethod(ClassType $class, string $column, string $type_hint) : ClassType
    {
        $types = ['get', 'set'];

        $methods = [];

        foreach ($types as $type){
            $method = new Method($type.camel_case($column));
            $method->setVisibility('public');

            switch ($type){

                case 'get':
                    $method->addComment('@return '.$type_hint)
                            ->setBody('return $this->'.$column);
                    break;
                case 'set':
                    $method->addComment('@param '.$type_hint.' $'.$column)
                            ->addBody('$this->'.$column. ' = '. $column)
                            ->addParameter($column)
                            ->setTypeHint($type_hint);
                            ;
                    break;
            }

            array_push($methods, $method);

        }

        $class->setMethods($methods);

        return $class;
    }

    /**
     * Create an Entity class Object
     *
     * @param PhpNamespace $namespace | class namespace
     * @param string $name | class name
     * @param string $table | database table name
     * @param array $fields | array of class properties
     * @param bool $timestamps | if the class needs timestamps
     * @param bool $isUser | if should extend User rather than Entity abstract class
     *
     * @return ClassType
     */
    private function createEntityClass(PhpNamespace $namespace, string  $name, string  $table, array $fields, bool $timestamps, bool $isUser) : ClassType
    {
        $class = $namespace->addClass($name);

        $class->addExtend($isUser? 'User':'Entity');

        $class->addComment("Class ".$name.'\n')
            ->addComment("@Mapping\\Entity")
            ->addComment('@Mapping\\Table(name="'.$table.'")');

        if($timestamps){
            $fields = array_merge($fields, [
                array(
                    'name' => 'created_at',
                    'type' => 'datetime',
                    'required' => true,
                ),
                array(
                    'name' => 'updated_at',
                    'type' => 'datetime',
                    'required' => false,
                ),
                array(
                    'name' => 'deleted_at',
                    'type' => 'datetime',
                    'required' => false,
                )
            ]);
        }

        foreach ($fields as $column){
            $class = $this->addProperty($class, $column);
        }

        return $class;

    }

    private function createModelClass(){

    }

    private function createRepoClass(){

    }

    private function createServiceClass(){

    }

    /**
     * Return valid doctrine db types
     *
     * @return array
     */
    private function getDBTypes() : array
    {
        return [
            'smallint',
            'integer',
            'bigint',
            'decimal',
            'float',
            'string',
            'text',
            'guid',
            'binary',
            'blob',
            'boolean',
            'date',
            'datetime',
            'datetimetz',
            'time',
            'array',
            'json_array',
            'object'
        ];
    }
}
