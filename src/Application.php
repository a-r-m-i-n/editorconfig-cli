<?php

declare(strict_types = 1);

namespace FGTCLB\EditorConfig;

use Symfony\Component\Console\Application as SymfonyConsoleApplication;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Application extends SymfonyConsoleApplication
{
    public function __construct(string $name = 'FGTCLB EditorConfig', string $version = '')
    {
        parent::__construct($name, $version);

        $this->loadCommands();
    }

    /**
     * Load commands dynamically from src/Commands folder.
     * Class name must end with "Command", file name with "Command.php".
     */
    private function loadCommands(): void
    {
        $finder = new Finder();
        $finder->in(__DIR__ . '/Commands/')->files()->name('*Command.php');

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $commandClass = __NAMESPACE__ . '\\Commands\\' . substr($file->getFilename(), 0, -4);
            if (\class_exists($commandClass)) {
                $this->add(new $commandClass());
            }
        }
    }
}
