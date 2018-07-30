<?php

namespace Foundry\Framework\Migrations;

use Foundry\Framework\Migrations\Configuration\Configuration;
use LaravelDoctrine\Migrations\Output\MigrationFileGenerator as Base;

/**
 * Class MigrationFileGenerator
 *
 * @package Foundry\Framework\Migrations
 *
 * @author Medard Ilunga
 */
class MigrationFileGenerator extends Base
{
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
