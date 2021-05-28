<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli;

use Armin\EditorconfigCli\Compatibility\SingleCommandApplication;
use Armin\EditorconfigCli\EditorConfig\Rules\FileResult;
use Armin\EditorconfigCli\EditorConfig\Rules\Rule;
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
            ->setDescription("CLI tool to validate and auto-fix text files, based on given .editorconfig declarations.\n  Version:    <comment>" . VersionUtility::getApplicationVersionFromComposerJson() . '</comment>' . "\n  Written by: <comment>Armin Vieweg <https://v.ieweg.de></comment>")
            ->addUsage('ec')
            ->addUsage('ec *.js *.css')
            ->addUsage('ec -n --no-progress')
            ->addUsage('ec --fix')
            ->addUsage('ec -e"dist" -e".build"')
            ->addUsage('ec -s charset,eol -s trim')
            ->addUsage('ec --unstaged')
            ->addUsage('ec --finder-instance finder-config.php')

            ->addArgument('names', InputArgument::IS_ARRAY, 'Name(s) of file names to get checked. Wildcards allowed', ['*'])

            ->addOption('skip', 's', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Disables rules by name. Comma-separation allowed')
            ->addOption('strict', null, InputOption::VALUE_NONE, 'When set, any difference of indention size is spotted')
            ->addOption('fix', 'f', InputOption::VALUE_NONE, 'Fixes all found issues in files (files get overwritten)')
            ->addOption('compact', 'c', InputOption::VALUE_NONE, 'When set, does only list files, no details')

            ->addOption('dir', 'd', InputOption::VALUE_OPTIONAL, 'Working directory to scan', getcwd())
            ->addOption('git-only', 'g', InputOption::VALUE_NONE, 'Only scans files which are currently under control of Git.')
            ->addOption('git-only-cmd', null, InputOption::VALUE_OPTIONAL, 'Allows to modify git command executed when --git-only (-g) is given.', 'git ls-files')
            ->addOption('finder-config', null, InputOption::VALUE_OPTIONAL, 'Optional path to PHP file (relative from working dir (-d)), returning a pre-configured Symfony Finder instance')
            ->addOption('disable-auto-exclude', 'a', InputOption::VALUE_NONE, 'By default all files ignored by existing .gitignore, will be excluded from scanning. This options disables it')
            ->addOption('exclude', 'e', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Directories to exclude')

            ->addOption('uncovered', 'u', InputOption::VALUE_NONE, 'When set, all files which are not covered by .editorconfig get listed')
            ->addOption('no-progress', '', InputOption::VALUE_NONE, 'When set, no progress indicator is displayed')
            ->addOption('no-error-on-exit', '', InputOption::VALUE_NONE, 'When set, the CLI tool will always return code 0, also when issues have been found')
            ->setCode([$this, 'executing'])
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        /** @var string $dir */
        $dir = $input->getOption('dir');
        $this->scanner->setRootPath($dir);

        /** @var array|null $skip */
        $skip = $input->getOption('skip');
        $skippingRules = $this->parseSkippingRules($skip);
        $this->scanner->setSkippingRules($skippingRules);
    }

    protected function executing(Input $input, Output $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->getFormatter()->setStyle('debug', new OutputFormatterStyle('blue'));
        $io->getFormatter()->setStyle('warning', new OutputFormatterStyle('black', 'yellow'));

        /** @var string $workingDirectory */
        $workingDirectory = $input->getOption('dir');
        if (empty($workingDirectory)) {
            $workingDirectory = getcwd() ?: '.';
        }
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
            /** @var bool $gitOnlyEnabled */
            $gitOnlyEnabled = $input->getOption('git-only');
            /** @var string|null $gitOnlyCommand */
            $gitOnlyCommand = $input->getOption('git-only-cmd');

            $finder = $finder ?? FinderUtility::createByFinderOptions($finderOptions, $gitOnlyEnabled ? $gitOnlyCommand : null);

            // Check amount of files to scan and ask for confirmation
            if ($finderConfigPath) {
                $io->writeln('Searching with custom Finder instance...');
            } else {
                $io->writeln(sprintf('Searching in directory <comment>%s</comment>...', $realPath));
                if ($gitOnlyEnabled && $gitOnlyCommand) {
                    $io->writeln('Get files from git binary (command: <comment>' . $gitOnlyCommand . '</comment>):');
                }
            }
            if (!$finderConfigPath && $output->isVerbose()) {
                if ($gitOnlyEnabled && $gitOnlyCommand) {
                    $io->writeln('<debug>Names and (auto-) excludes disabled, because of set git-only mode.</debug>');
                } else {
                    $io->writeln('<debug>Names: ' . implode(', ', (array)$input->getArgument('names')) . '</debug>');
                    $io->writeln('<debug>Excluded: ' . (count(FinderUtility::getCurrentExcludes()) > 0 ? implode(', ', FinderUtility::getCurrentExcludes()) : '-') . '</debug>');
                    $io->writeln('<debug>Auto exclude: ' . ($input->getOption('disable-auto-exclude') ? 'disabled' : 'enabled') . '</debug>');
                }
            }
            if ($output->isVerbose()) {
                $io->writeln('<debug>Strict mode: ' . ($input->getOption('strict') ? 'enabled' : 'disabled') . '</debug>');
                $io->writeln('<debug>Output mode: ' . ($input->getOption('compact') ? 'compact' : 'full') . '</debug>');
            }

            $io->writeln(sprintf('Found <info>%d files</info> to scan.', $count = $finder->count()));

            if (0 === $count) {
                $io->writeln('Nothing to do here.');

                return $returnValue; // Early return
            }

            if ($count > 500 && !$input->getOption('no-interaction') && !$io->confirm('Continue?', false)) {
                // @codeCoverageIgnoreStart
                $io->writeln('Canceled.');

                return $returnValue; // Early return
                // @codeCoverageIgnoreEnd
            }

            if (!empty($this->scanner->getSkippingRules())) {
                $io->writeln('Skipping rules: <comment>' . implode('</comment>, <comment>', $this->scanner->getSkippingRules()) . '</comment>');
            }

            // Start scanning or fixing
            $returnValue = !$input->getOption('fix')
                ? $this->scan($finder, $count, $io, (bool)$input->getOption('strict'), (bool)$input->getOption('no-progress'), (bool)$input->getOption('compact'), (bool)$input->getOption('uncovered'))
                : $this->fix($finder, $io, (bool)$input->getOption('strict'));
        } else {
            $io->error(sprintf('Invalid working directory "%s" given!', $workingDirectory));
            $returnValue = 1;
        }

        if ($input->getOption('no-error-on-exit')) {
            if ($returnValue > 0 && $output->isVerbose()) {
                $io->writeln(sprintf('<debug>Bypassing error code %d</debug>', $returnValue));
            }
            $returnValue = 0;
        }

        return $returnValue;
    }

    protected function scan(Finder $finder, int $fileCount, SymfonyStyle $io, bool $strict = false, bool $noProgress = false, bool $compact = false, bool $uncovered = false): int
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
        $uncoveredFilePaths = [];
        foreach ($fileResults as $filePath => $fileResult) {
            if (!$fileResult->isValid()) {
                $errorCount = $fileResult->countErrors();
                ++$invalidFilesCount;
                $errorCountTotal += $errorCount;
                $io->writeln('<info>' . $filePath . '</info> <comment>[' . $errorCount . ']</comment>');
                if (!$compact) {
                    $io->listing(explode(PHP_EOL, $fileResult->getErrorsAsString()));
                }
            }
            if ($uncovered && !$fileResult->hasDeclarations()) {
                $uncoveredFilePaths[] = $filePath;
            }
        }

        // Output results
        if ($errorCountTotal > 0) {
            $io->writeln('<error>' . StringFormatUtility::buildScanResultMessage($errorCountTotal, $invalidFilesCount) . '</error>');
        } else {
            $io->writeln('<info>Done. ' . StringFormatUtility::buildScanResultMessage($errorCountTotal, $invalidFilesCount) . '</info>');
        }

        // Uncovered files
        if ($uncovered) {
            $io->newLine();

            if (0 === count($uncoveredFilePaths)) {
                $io->writeln('No uncovered files found. Good job!');
            } else {
                $textFiles = 1 === count($uncoveredFilePaths) ? 'One file is' : count($uncoveredFilePaths) . ' files are';

                $io->writeln($textFiles . ' not covered by .editiorconfig declarations:');
                foreach ($uncoveredFilePaths as $unstagedFilePath) {
                    $io->writeln('<info>' . $unstagedFilePath . '</info>');
                }
                $io->newLine();
            }
        }

        return $errorCountTotal > 0 ? 2 : 0;
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
                    $io->writeln(' * fixed <info>' . $text . '</info> in file <info>' . $file . '</info>');
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

    protected function parseSkippingRules(array $skippingRules = null): array
    {
        if (!$skippingRules) {
            return [];
        }

        $flattenSkipRules = [];
        foreach ($skippingRules as $skipRule) {
            foreach (explode(',', $skipRule) as $flatRule) {
                $flattenSkipRules[] = trim($flatRule);
            }
        }
        $skippingRules = $flattenSkipRules;

        foreach ($skippingRules as $index => $skipRule) {
            $replacements = [
                'char' => 'charset',
                'eol' => 'end_of_line',
                'indent_size' => 'size',
                'indent_style' => 'style',
                'tab' => 'tab_width',
                'newline' => 'insert_final_newline',
                'trim' => 'trim_trailing_whitespace',
            ];
            if (array_key_exists($skipRule, $replacements)) {
                $skippingRules[$index] = $replacements[$skipRule];
            }
        }

        if (!empty($notExistingRules = array_diff($skippingRules, Rule::getDefinitions()))) {
            throw new \InvalidArgumentException('You try to skip rules which are not existing (' . implode(', ', $notExistingRules) . ').' . PHP_EOL . 'Available rules are: ' . implode(', ', Rule::getDefinitions()), 1621795334);
        }

        return $skippingRules;
    }
}
