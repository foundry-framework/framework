<?php

namespace Foundry\Framework\Migration\Output;


use Foundry\Framework\Migration\Configuration\Configuration;
use LaravelDoctrine\Migrations\Output\MigrationFileGenerator as Base;

class MigrationFileGenerator extends Base
{
    /**
     * @param bool|string $create
     * @param bool|string $update
     *
     * @return string
     */
    protected function getStub($create, $update)
    {
        $stub = 'blank';
        if ($create) {
            $stub = 'create';
        }

        if ($update) {
            $stub = 'update';
        }

        return __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . $stub . '.stub';
    }

    /**
     * @param Configuration $configuration
     * @param bool          $create
     * @param bool          $update
     * @param null          $up
     * @param null          $down
     *
     * @return string
     */
    public function foundryGenerate(
        Configuration $configuration,
        $create = false,
        $update = false,
        $up = null,
        $down = null
    )
    {
        $stub = $this->getStub($create, $update);

        $contents = $this->locator->locate($stub)->get();

        $contents = $this->replacer->replace($contents, $this->variables, [
            $configuration->getMigrationsNamespace(),
            $configuration->getNamingStrategy()->getClassName(),
            $this->getTableName($create, $update),
            $up ? $this->tabbedNewLine($up) : null,
            $down ? $this->tabbedNewLine($down) : null
        ]);

        $filename = $configuration->getNamingStrategy()->getFilename();

        $this->writer->write(
            $contents,
            $filename,
            $configuration->getMigrationsDirectory()
        );

        return $filename;
    }
}
