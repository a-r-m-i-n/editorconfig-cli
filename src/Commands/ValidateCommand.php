<?php declare(strict_types=1);
namespace FGTCLB\EditorConfig\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ValidateCommand extends Command
{
    public function configure(): void
    {
        $this
            ->setName('validate')
            ->setDescription('tbw;')

            ->addOption('working-dir', 'd', InputOption::VALUE_OPTIONAL, 'Current working directory', getcwd())
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Validating not implemented, yet.');
        /** @var string $workingDirectory */
        $workingDirectory = $input->getOption('working-dir');
        $realPath = realpath($workingDirectory) ?: 'error';
        $output->writeln(
            'I\'m currently in the directory <info>' . $realPath . '</info> ' .
            '<comment>("' . $workingDirectory . '")</comment>'
        );
        return 0;
    }
}
