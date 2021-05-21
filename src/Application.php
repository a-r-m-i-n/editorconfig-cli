<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli;

use Armin\EditorconfigCli\Compatibility\SingleCommandApplication;
use Armin\EditorconfigCli\EditorConfig\Rules\FileResult;
use Armin\EditorconfigCli\EditorConfig\Scanner;
use Armin\EditorconfigCli\EditorConfig\Utility\FinderUtility;
use Armin\EditorconfigCli\EditorConfig\Utility\StringFormatUtility;
use Armin\EditorconfigCli\EditorConfig\Utility\VersionUtility;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
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
            ->addUsage('ec')
            ->addUsage('ec --fix')
            ->addUsage('ec -e"dist" -e".build"')
            ->addUsage('ec -n --no-progress')
            ->addUsage('ec --finder-instance finder-config.php')
            ->addUsage('ec *.js *.css')

            ->addArgument('names', InputArgument::IS_ARRAY, 'Name(s) of file names to get checked. Wildcards allowed.', ['*'])

            ->addOption('strict', 's', InputOption::VALUE_NONE, 'When set, any difference of indention size is spotted.')
            ->addOption('fix', 'f', InputOption::VALUE_NONE, 'Fixes all found issues in files (files get overwritten).')
            ->addOption('compact', 'c', InputOption::VALUE_NONE, 'When set, does only list files, no details.')

            ->addOption('dir', 'd', InputOption::VALUE_OPTIONAL, 'Working directory to scan.', getcwd())
            ->addOption('finder-config', null, InputOption::VALUE_OPTIONAL, 'Optional path to PHP file (relative from working dir (-d)), returning a pre-configured Symfony Finder instance.')
            ->addOption('disable-auto-exclude', 'a', InputOption::VALUE_NONE, 'By default all files ignored by existing .gitignore, will be excluded from scanning. This options disables it.')
            ->addOption('exclude', 'e', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Directories to exclude.')

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
            // Create (or get) Symfony Finder instance
            $finderOptions = [
                'path' => $realPath,
                'names' => (array)$input->getArgument('names'),
                'exclude' => (array)$input->getOption('exclude'),
                'disable-auto-exclude' => (bool)$input->getOption('disable-auto-exclude'),
            ];

            $finderConfigPath = null;
            if (!empty($input->getOption('finder-config'))) {
                /** @var string $finderConfigPath */
                $finderConfigPath = $input->getOption('finder-config');
                $finderConfigPath = $realPath . '/' . $finderConfigPath;
                $finder = FinderUtility::loadCustomFinderInstance($finderConfigPath, $finderOptions);

                $io->writeln(
                    sprintf('<comment>Loading custom Symfony Finder configuration from %s</comment>', $finderConfigPath)
                );
            }
            $finder = $finder ?? FinderUtility::createByFinderOptions($finderOptions);

            // Check amount of files to scan and ask for confirmation
            if ($finderConfigPath) {
                $io->writeln('Searching with custom Finder instance...');
            } else {
                $io->writeln(sprintf('Searching in directory <comment>%s</comment>...', $realPath));
            }
            if (!$finderConfigPath && $output->isVerbose()) {
                $io->writeln('<debug>Names: ' . implode(', ', (array)$input->getArgument('names')) . '</debug>');
                $io->writeln('<debug>Excluded: ' . implode(', ', FinderUtility::getCurrentExcludes()) . '</debug>');
            }

            $io->writeln(sprintf('Found <info>%d files</info> to scan.', $count = $finder->count()));

            if (0 === $count) {
                $io->writeln('Nothing to do here.');

                return $returnValue; // Early return
            }

            if ($count > 500 && !$input->getOption('no-interaction') && !$io->confirm('Continue?', false)) {
                $io->writeln('Canceled.');

                return $returnValue; // Early return
            }

            // Start scanning or fixing
            $returnValue = !$input->getOption('fix')
                ? $this->scan($finder, $count, $io, (bool)$input->getOption('strict'), (bool)$input->getOption('no-progress'), (bool)$input->getOption('compact'))
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

    protected function scan(Finder $finder, int $fileCount, SymfonyStyle $io, bool $strict = false, bool $noProgress = false, bool $compact = false): int
    {
        $io->writeln('<comment>Starting scan...</comment>');

        $callback = null;
        $progressBar = null;
        if (!$noProgress) {
            // Progress bar
            $progressBar = $this->createProgressBar($io, $fileCount);
            $amountIssues = $amountFilesWithIssues = 0;
            $callback = function (FileResult $fileResult) use ($progressBar, &$amountIssues, &$amountFilesWithIssues) {
                $progressBar->advance();
                if (!$fileResult->isValid()) {
                    ++$amountFilesWithIssues;
                    $amountIssues += $fileResult->countErrors();
                    $progressBar->setMessage(
                        '<error>' . StringFormatUtility::buildScanResultMessage($amountIssues, $amountFilesWithIssues) . '</error>'
                    );
                }
            };
        }

        // Start the scan
        $fileResults = $this->scanner->scan($finder, $strict, $callback);

        if (!$noProgress && $progressBar) {
            // Progress bar
            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%%');
            $progressBar->finish();
            $io->newLine(2);
        }

        // Prepare results after scan
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

        // Output results
        if ($errorCountTotal > 0) {
            $io->writeln('<error>' . StringFormatUtility::buildScanResultMessage($errorCountTotal, $invalidFilesCount) . '</error>');

            // Uncovered files
            if ($io->isVerbose() && count($unstagedFiles) > 0) {
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
        $hasUnfixableExceptions = false;
        foreach ($fileResults as $file => $fileResult) {
            if (!$fileResult->isValid()) {
                ++$invalidFilesCount;
                $errorCountTotal += $fileResult->countErrors();
                $fileResult->applyFixes();

                if ($fileResult->hasUnfixableExceptions()) {
                    $errorCountTotal -= $fileResult->countErrors();
                    $hasUnfixableExceptions = true;
                    foreach ($fileResult->getUnfixableExceptions() as $e) {
                        $io->writeln(' * <warning>WARNING</warning> ' . $e->getMessage());
                    }
                } else {
                    $text = 1 === $fileResult->countErrors() ? 'one issue' : $fileResult->countErrors() . ' issues';
                    $io->writeln(' * fixed <info>' . $text . '</info> in file <info>' . $file . '</info>.');
                }
            }
        }

        $io->writeln('<info>Done. ' . StringFormatUtility::buildScanResultMessage($errorCountTotal, $invalidFilesCount, 'Fixed') . '</info>');

        return false === $hasUnfixableExceptions ? 0 : 1;
    }

    protected function createProgressBar(SymfonyStyle $io, int $fileCount): ProgressBar
    {
        $progressBar = $io->createProgressBar($fileCount);

        $progressBar->setProgressCharacter('>');
        $progressBar->setBarCharacter('=');
        $progressBar->setBarWidth(50);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %remaining:6s% | %message%');

        $progressBar->setMessage('<info>No issues found, yet</info>');

        return $progressBar;
    }
}
