<?php

namespace AnyContent\Backend\Command;

use AnyContent\Backend\Helper\ConsolePrinter;
use AnyContent\Backend\Services\RepositoryManager;
use AnyContent\Connection\MySQLSchemalessReadOnlyConnection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anycontent:revisions:delete',
    description: 'Deletes revisions in MySQL Schemaless repositories.',
    aliases: [],
    hidden: false
)]
class DeleteRevisionsCommand extends Command
{
    public function __construct(
        private RepositoryManager $repositoryManager,
        private ConsolePrinter $printer
    ) {
        parent::__construct(null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->printer->error('Not yet implemented');
        return Command::FAILURE;
        $repositories = $this->repositoryManager->listRepositories();

        foreach ($repositories as $repositoryInfo) {
            $repository = $this->repositoryManager->getRepositoryByRepositoryAccessHash($repositoryInfo['accessHash']);

            $connection = $repository->getWriteConnection();

            if ($connection instanceof MySQLSchemalessReadOnlyConnection) {
                $this->printer->h1($repositoryInfo['title']);
                foreach ($repository->getContentTypeDefinitions() as $contentTypeDefinition) {
                    $this->printer->writeln($contentTypeDefinition->getName());
                }
            }

            $this->printer->writeln('');
        }
        $this->printer->writeln();

        return Command::SUCCESS;
    }
}
