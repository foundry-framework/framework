<?php

namespace Foundry\Framework\Console;

use Illuminate\Console\Command as IlluminateCommand;

class Command extends IlluminateCommand
{
    /**
     * Display blue message
     *
     * @param        $message
     * @param string $color
     */
    public function message($message, $color = 'blue')
    {
        $this->getOutput()->writeln('<fg=' . $color . '>' . $message . '</fg=' . $color . '>');
    }
}
