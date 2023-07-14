<?php

namespace AnyContent\Backend\Command;

use AnyContent\Backend\Helper\ConsolePrinter;
use AnyContent\Backend\Services\RepositoryManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anycontent:repositories',
    description: 'Lists all currently registered repositories and content types.',
    aliases: [],
    hidden: false
)]
class ListRepositoriesCommand extends Command
{
    public function __construct(
        private RepositoryManager $repositoryManager,
        private ConsolePrinter $printer
    ) {
        parent::__construct(null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repositories = $this->repositoryManager->listRepositories();

        foreach ($repositories as $url => $repository) {
            $this->printer->h1($repository['title']);

            $contentTypes = $this->repositoryManager->listContentTypes($url);

            $this->printer->info('Content Types:', true);
            foreach ($contentTypes as $contentTypeName => $contentType) {
                $this->printer->writeln(sprintf('<strong>%s</strong> (%s)', $contentType['title'], $contentTypeName));
            }

            $this->printer->writeln();

            $configTypes = $this->repositoryManager->listConfigTypes($url);

            $this->printer->info('Config Types:', true);
            foreach ($configTypes as $configTypeName => $configType) {
                $this->printer->writeln(sprintf('<strong>%s</strong> (%s)', $configType['title'], $configTypeName));
            }

            $this->printer->writeln('');
        }
        $this->printer->writeln();

        return Command::SUCCESS;
    }
}
