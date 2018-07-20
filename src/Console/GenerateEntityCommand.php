<?php

namespace Foundry\Framework\Console;


use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;

/**
 * Class GenerateEntityCommand
 * Generates Entity, Model, Service, and Repo classes
 *
 *
 * @package Foundry\Framework\Console
 *
 * @author Medard Ilunga
 */
class GenerateEntityCommand extends Command
{

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'foundry:generate:entity
    {package : The package for which this entity is for}
    {name : The name of the entity }
    {--properties= : Comma separated properties of the entity with their respective types and optional required or not. the structure is nameOfColumn:Type:required (e.g: name:string, last_name:string:false, count:int, ... ). By default all columns are considered string and required }
    {--user : Should this entity extend the User Class? }
    {--table= : The name of the database table related to this entity, only if name is different to entity name}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Generates an Entity and all related files (Model, Service, Repository)';


    public function handle()
    {
        $timestamps = $this->confirm("Does the Entity require timestamps (created_at, updated_at)? ");
        $deleted = $this->confirm("Can the object be soft deleted?");

        $package = $this->argument('package');
        $isUser = $this->option('user');
        $table = $this->option('table')?
                                    strtolower(preg_replace('/\s+[-]/','_', $this->option('table'))):
                                    strtolower(preg_replace('/\s+[-]/','_', $this->argument('name')));

        $name = ucfirst(camel_case($this->argument('name')));
        $fields = $this->getFields($this->option('properties')?$this->option('properties'):'');

        $root = base_path('packages/foundry/'.camel_case($package));

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

            $entity = $this->initEntityClass($package, $name, $table, $fields, $timestamps, $deleted, $isUser);
            $this->createFile($entityFolder.'/'. $name, $entity);

            $model = $this->initModelClass($package, $name);
            $this->createFile($modelFolder.'/'.$name.'Model', $model);

            $service = $this->initServiceClass($package, $name);
            $this->createFile($servicesFolder.'/'.$name.'Service', $service);

            $repo = $this->initRepoClass($package, $name);
            $this->createFile($repoFolder.'/'.$name.'Repository', $repo);

            $this->message('Entity class and all related classes added successfully!');

        }else
            $this->message('Package '. $package. ' does not exist!', 'red');

    }

    /**
     * Init an Entity class
     *
     * @param string $package | Name of the root package
     * @param string $name | Name of the Entity
     * @param string $table | Database name of the table related to this entity
     * @param array $fields | Associative array of Entity properties
     * @param bool $timestamps | Should this entity have timestamps
     * @param bool $deletable | Can the object be deleted?
     * @param bool $isUser | Should this entity extend the User abstract class
     *
     * @return PhpNamespace
     */
    private function initEntityClass(string $package, string $name, string $table, array $fields, bool $timestamps, bool $deletable, bool $isUser) : PhpNamespace
    {
        $namespace = new PhpNamespace(camel_case(strtolower($package)).'\\Api\\Entities');

        $psr4 = $isUser? 'Foundry\\Framework\\Api\\Entities\\User': 'Foundry\\Framework\\Api\\Entities\\Entity';
        $alias = $isUser? 'BaseUser': 'BaseEntity';
        $namespace->addUse($psr4, $alias);

        $namespace->addUse('Doctrine\\ORM\\Mapping');

        return $this->createEntityClass($namespace, $name, $table, $fields, $timestamps, $deletable, $psr4);

    }

    private function initModelClass(string $package, string $name) : PhpNamespace
    {
        $namespace = new PhpNamespace(camel_case(strtolower($package)).'\\Api\\Models');

        $psr4 = 'Foundry\\Framework\\Api\\Models\\Model';
        $namespace->addUse($psr4);

        $entity = camel_case(strtolower($package)).'\\Api\\Entities\\'.$name;

        return $this->createModelClass($namespace, $name, $entity, $psr4);
    }

    private function initServiceClass(string $package, string $name): PhpNamespace
    {
        $namespace = new PhpNamespace(camel_case(strtolower($package)).'\\Api\\Services');

        $psr4 = 'Foundry\\Framework\\Api\\Services\\Service';
        $namespace->addUse($psr4);

        $entity = ucfirst(strtolower($package)).'\\Api\\Entities\\'.$name;
        $model = ucfirst(strtolower($package)).'\\Api\\Models\\'.$name.'Model';

        return $this->createServiceClass($namespace, $name,$entity, $model, $psr4);
    }

    private function initRepoClass(string $package, string $name) : PhpNamespace
    {
        $namespace = new PhpNamespace(camel_case(strtolower($package)).'\\Api\\Repositories');

        $psr4 = 'Foundry\\Framework\\Api\\Repositories\\Repository';
        $namespace->addUse($psr4);

        return $this->createRepoClass($namespace, $name, $psr4);
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
        $create = true;

        if(file_exists($path.'.php')){
            $pos = strripos($path.'.php','/');
            $create = $this->confirm(substr($path.'.php', $pos + 1 ).' class already exists and will be overwritten, do you want to proceed?');
        }

        if($create){
            $file = fopen($path.'.php', 'w');
            fwrite($file, '<?php'.$this->newLine(2));
            fwrite($file, $content);
            fclose($file);
        }
    }

    /**
     * Create a Directory
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

                    if(in_array(trim($field[1]), $this->getDBTypes()))
                        $type = trim($field[1]);

                    if(isset($field[2]))
                        $required = trim($field[2]) === 'false'? false: true;

                    $field = trim($field[0]);
                }

                $field = preg_replace('/\s+[-]/','_', $field);

                array_push($properties, [
                    'name' => $field,
                    'type' => $type,
                    'required' => (bool) $required
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
        $column = '@Mapping\\column(type="'.$property['type'].'")'.$this->newLine(1);

        if(!$property['required']){
            $column = '@Mapping\\column(type="'.$property['type'].'", nullable=true)'.$this->newLine(1);
        }

        $name = strtolower($property['name']);

        $class->addProperty($name)
                ->setVisibility($visibility)
                ->addComment('@var '.$this->returnType($property['type']).$this->newLine(1))
                ->addComment($column);

        return $class;
    }

    /**
     * Add new line
     *
     * @param $l | number of lines
     *
     * @return string
     */
    private function newLine($l = 1) : string
    {
        $end = '';

        for ($i = 0; $i < $l; $i++){
            $end .= PHP_EOL;
        }

        return $end;
    }

    /**
     * Add a class method
     *
     * @param string $column | Name of column whose methods need to be added
     * @param string $type_hint | type of column
     *
     * @return array
     */
    private function addMethod(string $column, string $type_hint) : array
    {
        $types = ['get', 'set'];

        if($type_hint === 'boolean')
            $types = ['is', 'set'];

        $methods = [];

        foreach ($types as $type){

            $name = $type.ucfirst(camel_case($column));

            if($type_hint === 'boolean' &&
                strcmp(substr($column, 0,2), 'is') === 0){
                $name = $type.ucfirst(camel_case(substr($column, 2)));
            }


            $method = new Method($name);
            $method->setVisibility('public');

            switch ($type){

                case 'get':
                case 'is':
                    $method->addComment('@return '.$this->returnType($type_hint))
                            ->addBody('return $this->'.$column.';');
                    break;
                case 'set':
                    $method->addComment('@param '.$this->returnType($type_hint).' $'.$column)
                            ->addBody('$this->'.$column. ' = $'. $column.';')
                            ->addParameter($column)
                            ->setTypeHint($this->returnType($type_hint, false));
                            ;
                    break;
            }

            array_push($methods, $method);

        }

        return $methods;
    }

    /**
     * Create an Entity class Object
     *
     * @param PhpNamespace $namespace | class namespace
     * @param string $name | class name
     * @param string $table | database table name
     * @param array $fields | array of class properties
     * @param bool $timestamps | if the class needs timestamps
     * @param bool $deletable
     * @param string $extend | class to be extended
     *
     * @return PhpNamespace
     */
    private function createEntityClass(PhpNamespace $namespace, string  $name, string  $table,
                                       array $fields, bool $timestamps, bool $deletable, string $extend) : PhpNamespace
    {

        $class = $this->createClass($namespace,$name, $extend);

        $class
              ->addComment("@Mapping\\Entity")
              ->addComment('@Mapping\\Table(name="'.$table.'")');


        if($timestamps){
            $class->addTrait('Foundry\Framework\TimeStamp');
            $namespace->addUse('Foundry\Framework\TimeStamp');
        }


        if($deletable){

            $class->addTrait('Foundry\Framework\Deletable');
            $class->addComment('@Gedmo\SoftDeleteable(fieldName="deleted_at", timeAware=false)'.$this->newLine());

            $namespace->addUse('Foundry\Framework\Deletable');
            $namespace->addUse('Gedmo\Mapping\Annotation', 'Gedmo');
        }

        $methods = [];

        foreach ($fields as $column){
            $class = $this->addProperty($class, $column);
            $methods = array_merge($methods, $this->addMethod($column['name'], $column['type']));
        }

        $class->setMethods($methods);

        return $namespace;

    }

    /**
     * @param PhpNamespace $namespace
     * @param string $name | name of the Entity class
     * @param string $entityClass | Full PSR4 namespace to the entity class
     * @param string $extend | Full PSR4 namespace of the class to be extended
     *
     * @return PhpNamespace
     */
    private function createModelClass(PhpNamespace $namespace, string $name, string $entityClass, string $extend) : PhpNamespace
    {

        $class = $this->createClass($namespace, $name.'Model', $extend);

        //add required abstract methods 'entity' and 'rules' and 'messages'

        $namespace->addUse($entityClass);
        $entity = new Method('entity');
        $entity->setStatic()
                ->addComment('Get the Entity Object represented by this Model'.$this->newLine())
                ->addComment('@return '.ucfirst(camel_case($name)))
                ->setBody('return new '.ucfirst(camel_case($name)).'();');

        $rules = new Method('rules');
        $rules->setStatic()
                ->addComment('Get rules related to the Entity\'s properties'.$this->newLine())
                ->addComment('@return array')
                ->setBody('//todo add rules '.$this->newLine(2).' return [];');

        $messages = new Method('messages');
        $messages->setStatic()
                ->addComment('Get customer error messages related to the rules'.$this->newLine())
                ->addComment('@return array')
                ->setBody('//todo add custom rules messages'.$this->newLine(2).'return [];');


        $class->setMethods([$entity, $rules, $messages]);

        return $namespace;
    }

    /**
     * @param PhpNamespace $namespace | namespace
     * @param string $name | name of the Entity class
     * @param string $extend | Full PSR4 namespace of the class to be extended
     *
     * @return PhpNamespace
     */
    private function createRepoClass(PhpNamespace $namespace, string $name, string $extend) : PhpNamespace
    {
        $class = $this->createClass($namespace, $name.'Repository', $extend);

        return $namespace;
    }

    /**
     * Create a service class
     *
     * @param PhpNamespace $namespace | namespace
     * @param string $name | name of the Entity class
     * @param string $entityClass | Full PSR4 namespace to the entity class
     * @param string $modelClass | Full PSR4 namespace to the model class
     * @param string $extend | Full PSR4 namespace of the class to be extended
     *
     * @return PhpNamespace
     */
    private function createServiceClass(PhpNamespace $namespace, string $name, string $entityClass, string $modelClass, string $extend) : PhpNamespace
    {
        $class = $this->createClass($namespace, $name.'Service', $extend);

        //add required abstract methods 'entity' and 'entityModel'

        $namespace->addUse($entityClass);
        $entity = new Method('entity');
        $entity->setStatic()
                ->addComment('Get the Entity Object'.$this->newLine())
                ->addComment('@return '.ucfirst(camel_case($name)))
                ->setBody('return new '.ucfirst(camel_case($name)).'();');

        $namespace->addUse($modelClass);
        $model = new Method('entityModel');
        $model->setStatic()
                ->addComment('Get the Model of the Entity Object'.$this->newLine())
                ->addComment('@return '.ucfirst(camel_case($name)).'Model')
                ->setBody('return new '.ucfirst(camel_case($name)).'Model();');

        $class->setMethods([$entity, $model]);

        return $namespace;
    }

    /**
     * Create a class based off of a @PhpNamespace
     *
     * @param PhpNamespace $namespace | namespace
     * @param string $name | name of class
     * @param string|null $extends | class to be extended
     *
     * @return ClassType
     */
    private function createClass(PhpNamespace $namespace, string $name, string $extends = null) : ClassType
    {
        $class = $namespace->addClass($name);

        $class->addComment("Class ".$name.$this->newLine(2));

        if($extends)
            $class->addExtend($extends);

        return $class;
    }

    /**
     * @param $type
     *
     * @param bool $escape
     * @return string
     */
    private function returnType($type, $escape = true){
        $arr = ['text',
                'bigint',
                'smallint',
                'decimal',
                'text',
                'guid',
                'binary',
                'blob',
                'date',
                'datetimetz',
                'time',
                'json_array',
                'object',
                'integer'];

        if(in_array($type, $arr))
            return $escape? '\mixed': '';
        elseif ($type === 'array')
            return 'array';
        elseif ($type === 'boolean')
            return 'bool';

        return $escape? '\\'.$type: $type;
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
