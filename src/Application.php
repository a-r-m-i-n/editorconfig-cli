<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli;

use Armin\EditorconfigCli\EditorConfig\Rules\FileResult;
use Armin\EditorconfigCli\EditorConfig\Scanner;
use Armin\EditorconfigCli\EditorConfig\Utility\FinderUtility;
use Armin\EditorconfigCli\EditorConfig\Utility\VersionUtility;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class Application extends SingleCommandApplication
{
    /**
     * @var Scanner
     */
    private $scanner;

    public function __construct(string $name = 'ec', ?Scanner $scanner = null)
    {
        parent::__construct($name);

        $this->scanner = $scanner ?? new Scanner();

        $this
            ->setName('ec')
            ->setVersion(VersionUtility::getApplicationVersionFromComposerJson())
            ->setDescription("CLI tool to validate and auto-fix text files, based on given .editorconfig declarations.\n  Version: <comment>" . VersionUtility::getApplicationVersionFromComposerJson() . '</comment>')
            ->addUsage('ec -e"vendor"')
            ->addUsage('ec -e"vendor" --fix')
            ->addUsage('ec -e"vendor" -n --no-progress')

            ->addArgument('names', InputArgument::IS_ARRAY, 'Name(s) of file names to get checked. Wildcards allowed.', ['*'])

            ->addOption('dir', 'd', InputOption::VALUE_OPTIONAL, 'Working directory to scan.', getcwd())
            ->addOption('disable-auto-exclude', 'a', InputOption::VALUE_NONE, 'When set, "vendor" and "node_modules" are not excluded by default.')
            ->addOption('exclude', 'e', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Directories to exclude.')
            ->addOption('strict', 's', InputOption::VALUE_NONE, 'When set, any difference of indention size is spotted.')
            ->addOption('fix', 'f', InputOption::VALUE_NONE, 'Fixes all found issues in files (files get overwritten).')
            ->addOption('compact', 'c', InputOption::VALUE_NONE, 'When set, does only list files, no details.')

            ->addOption('no-progress', '', InputOption::VALUE_NONE, 'When set, no progress indicator is displayed.')
            ->addOption('no-error-on-exit', '', InputOption::VALUE_NONE, 'When set, the CLI tool will always return code 0, also when issues have been found.')
            ->setCode([$this, 'executing'])
        ;
    }

    protected function executing(Input $input, Output $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->getFormatter()->setStyle('debug', new OutputFormatterStyle('blue'));
        $io->getFormatter()->setStyle('warning', new OutputFormatterStyle('black', 'yellow'));

        /** @var string $workingDirectory */
        $workingDirectory = $input->getOption('dir');
        $realPath = realpath($workingDirectory);
        $returnValue = 0;

        if ($realPath) {
            $finder = FinderUtility::create(
                $realPath,
                (array)$input->getArgument('names'),
                (array)$input->getOption('exclude'),
                (bool)$input->getOption('disable-auto-exclude')
            );

            $io->writeln(sprintf('Searching in directory <comment>%s</comment> ...', $realPath));
            if ($output->isVerbose()) {
                $io->writeln('<debug>Names: ' . implode(', ', (array)$input->getArgument('names')) . '</debug>');
                $io->writeln('<debug>Excluded: ' . implode(', ', FinderUtility::getCurrentExcludes()) . '</debug>');
            }

            $io->writeln(sprintf('Found <info>%d files</info> to scan.', $count = $finder->count()));

            if ($count > 500 && !$input->getOption('no-interaction') && !$io->confirm('Continue?', false)) {
                $io->writeln('Canceled.');

                return $returnValue; // Early return
            }

            $returnValue = !$input->getOption('fix')
                ? $this->scan($finder, $io, (bool)$input->getOption('strict'), (bool)$input->getOption('no-progress'), (bool)$input->getOption('compact'))
                : $this->fix($finder, $io, (bool)$input->getOption('strict'));
        } else {
            $io->error(sprintf('Invalid working directory "%s" given!', $workingDirectory));
            $returnValue = 1;
        }

        if ($input->getOption('no-error-on-exit')) {
            $returnValue = 0;
        }

        return $returnValue;
    }

    protected function scan(Finder $finder, SymfonyStyle $io, bool $strict = false, bool $noProgress = false, bool $compact = false): int
    {
        $callback = null;
        if (!$noProgress) {
            $callback = function (FileResult $fileResult) use ($io) {
                if ($fileResult->isValid()) {
                    $io->write('.');
                } else {
                    $io->write('<error>E</error>');
                }
            };
        }

        $io->writeln('<comment>Starting scan...</comment>');
        $fileResults = $this->scanner->scan($finder, $strict, $callback);

        if ($callback) {
            $io->newLine(2);
        }
        $invalidFilesCount = 0;
        $errorCountTotal = 0;
        $unstagedFiles = [];
        foreach ($fileResults as $file => $fileResult) {
            if (!$fileResult->isValid()) {
                $errorCount = $fileResult->countErrors();
                ++$invalidFilesCount;
                $errorCountTotal += $errorCount;
                $io->writeln('<info>' . $file . '</info> <comment>[' . $errorCount . ']</comment>');
                if (!$compact) {
                    $io->listing(explode(PHP_EOL, $fileResult->getErrorsAsString()));
                }
            }
            if (!$fileResult->hasDeclarations()) {
                $unstagedFiles[] = $fileResult;
            }
        }

        if ($errorCountTotal > 0) {
            $io->writeln('<warning>Found ' . $errorCountTotal . ' issues in ' . $invalidFilesCount . ' files!</warning>');
            if ($io->isVerbose()) {
                $io->newLine();
                $io->writeln('<debug>' . count($unstagedFiles) . ' files are not covered by .editiorconfig declarations:</debug>');
                foreach ($unstagedFiles as $unstagedFileResult) {
                    $io->writeln('<debug> - ' . $unstagedFileResult->getFilePath() . '</debug>');
                }
            }

            return 2;
        }
        $io->writeln('<info>Done. No issues found.</info>');

        return 0;
    }

    protected function fix(Finder $finder, SymfonyStyle $io, bool $strict = false): int
    {
        $io->writeln('<comment>Starting to fix issues...</comment>');

        $fileResults = $this->scanner->scan($finder, $strict);
        $invalidFilesCount = 0;
        $errorCountTotal = 0;
        foreach ($fileResults as $file => $fileResult) {
            if (!$fileResult->isValid()) {
                ++$invalidFilesCount;
                $errorCountTotal += $fileResult->countErrors();
                $fileResult->applyFixes();
                $io->writeln(' * fixed <info>' . $fileResult->countErrors() . ' issues</info> in file <info>' . $file . '</info>.');
            }
        }

        if ($errorCountTotal > 0) {
            $io->writeln('<info>Done. Fixed ' . $errorCountTotal . ' issues in ' . $invalidFilesCount . ' files!</info>');
        } else {
            $io->writeln('<info>Done. No issues found to fix.</info>');
        }

        return 0;
    }
}
