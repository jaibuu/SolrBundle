<?php
namespace FS\SolrBundle\Command;

use FS\SolrBundle\Console\ConsoleErrorListOutput;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ClearIndexCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('solr:index:clear')
            ->setDescription('Clear the whole index');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $solr = $this->getContainer()->get('solr.client.default');

        try {
            $solr->clearIndex();
        } catch (\Exception $e) {
        }

        $results = $this->getContainer()->get('solr.console.command.results');
        if ($results->hasErrors()) {
            $output->writeln('<info>Clear index finished with errors!</info>');
        } else {
            $output->writeln('<info>Index successful cleared successful</info>');
        }

        if ($results->hasErrors()) {
            $output->writeln('');
            $error = array_shift($results->getErrors());

            $output->writeln(sprintf('<comment>Error: %s</comment>', $error->getMessage()));
        }
    }
}
