<?php declare(strict_types=1);
namespace FGTCLB\EditorConfig\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ValidateCommand extends Command
{
    public function configure()
    {
        $this
            ->setName('validate')
            ->setDescription('tbw;')

            ->addOption('working-dir', 'd', InputOption::VALUE_OPTIONAL, 'Current working directory', getcwd())
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Validating not implemented, yet.');
        $output->writeln(
            'I\'m currently in the directory <info>' . realpath($input->getOption('working-dir')) . '</info> ' .
            '<comment>("' . $input->getOption('working-dir') . '")</comment>'
        );
        return 0;
    }
}
