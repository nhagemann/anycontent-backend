<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\Exchange;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

use Symfony\Component\Console\Output\OutputInterface;

use AnyContent\CMCK\Modules\Backend\Core\Repositories\RepositoryManager;

class ImportCommand extends \AnyContent\CMCK\Modules\Backend\Core\Application\Command
{

    protected function configure()
    {
        $this->setName('cmck:import')
            ->setDescription('Import records of a content type.')
            ->addArgument('repository', InputArgument::REQUIRED, 'Name/Id of the repository having the content type to be imported. Use the list command to show available repositories.')
            ->addArgument('content type', InputArgument::REQUIRED, 'Name of the content type to be imported.')
            ->addArgument('workspace', InputArgument::REQUIRED, 'Name of the workspace to be imported.')
            ->addArgument('language', InputArgument::REQUIRED, 'Name of the language to be imported.')
            ->addArgument('filename', InputArgument::REQUIRED, 'Name of the file to be imported.')
            ->addOption('json', 'j')
            ->addOption('xlsx', 'x');

    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('');

        $app = $this->getSilexApplication();
        /** @var RepositoryManager $repositoryManager */
        $repositoryManager = $app['repos'];

        $contentTypeName = $input->getArgument('content type');
        $repositoryName = $input->getArgument('repository');
        $workspace = $input->getArgument('workspace');
        $language = $input->getArgument('language');
        $filename = $input->getArgument('filename');


        $output->writeln('Starting import for content type ' . $contentTypeName);

        $output->writeln('');

        $repository = $repositoryManager->getRepositoryById($repositoryName);

        if (!$repository->hasContentType($contentTypeName)) {
            $output->writeln(self::escapeError . 'Repository ' . $repositoryName . ' does not have a content type named ' . $contentTypeName . '. Use the list command to show available content types.' . self::escapeReset);

            return;
        }

        if (!$repository->getContentTypeDefinition($contentTypeName)->hasWorkspace($workspace)) {
            $output->writeln(self::escapeError . 'Content type ' . $contentTypeName . ' does not have a workspace named ' . $workspace . self::escapeReset);
        }

        if (!$repository->getContentTypeDefinition($contentTypeName)->hasLanguage($language)) {
            $output->writeln(self::escapeError . 'Content type ' . $contentTypeName . ' does not have a language named ' . $language . self::escapeReset);
        }

        if (strpos($filename, '/') !== 0) {
            $filename = getcwd() . '/' . $filename;
        }
        $filename = realpath($filename);

        if (!file_exists($filename)) {
            $output->writeln(self::escapeError . 'Could not find/access file.' . self::escapeReset);

            return;
        }

        $output->writeln('Reading ' . $filename);
        $output->writeln('');

        $importer = new Importer();
        $importer->setOutput($output);

        if ($input->getOption('xlsx') == true) {
            $importer->importXLSX($repository, $contentTypeName, $filename, $workspace, $language);
        } else // default (JSON)
        {
            $data = file_get_contents($filename);
            $importer->importJSON($repository, $contentTypeName, $data, $workspace, $language);
        }
    }
}
