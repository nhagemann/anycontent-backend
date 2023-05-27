<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\Exchange;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

use Symfony\Component\Console\Output\OutputInterface;


use AnyContent\CMCK\Modules\Backend\Core\Repositories\RepositoryManager;

use Symfony\Component\Filesystem\Filesystem;

class ArchiveCommand extends \AnyContent\CMCK\Modules\Backend\Core\Application\Command
{

    protected function configure()
    {
        $this->setName('cmck:archive')
            ->setDescription('Exports a whole repository as file based content archive.')
            ->addArgument('repository', InputArgument::REQUIRED, 'Name/Id of the repository having the content type to be exported. Use the list command to show available repositories.')
            ->addArgument('path', InputArgument::REQUIRED, 'export path')
            ->addOption('json', 'j')
            ->addOption('xlsx', 'x')
            ->addOption('merge', 'm');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getSilexApplication();


        /** @var RepositoryManager $repositoryManager */
        $repositoryManager = $app['repos'];

        $repositoryName = $input->getArgument('repository');

        $repository = $repositoryManager->getRepositoryById($repositoryName);

        if (!$repository) {
            $output->writeln(self::escapeError . 'Repository ' . $repositoryName . ' unknown. Use the list command to show available repositories.' . self::escapeReset);

            return;
        }

        if ($repository->getTitle() == '') {
            $repository->setTitle($repository->getName());
        }

        $filesystem = new Filesystem();

        $path = rtrim($input->getArgument('path'), '/');


        if (!$filesystem->exists($path)) {
            $output->writeln(self::escapeError . 'Could not access folder ' . $path . self::escapeReset);

            return;
        }

        $output->writeln('');
        $output->writeln('Starting export for repository ' . $repository->getTitle() . ' (' . $repository->getName() . ')');
        $output->writeln('');

        $exporter = new Exporter();
        $exporter->setOutput($output);

        $path .= '/' . $repositoryName;

        if (!$filesystem->exists($path)) {
            $filesystem->mkdir($path);
        }


        foreach ($repository->getContentTypeDefinitions() as $definition) {

            $contentTypeName = $definition->getName();

            $output->writeln('');
            $output->writeln(self::escapeCyan . 'Starting export for content type ' . $contentTypeName . self::escapeReset);
            $output->writeln('');

            foreach ($definition->getWorkspaces() as $workspace => $workspaceTitle) {
                foreach ($definition->getLanguages() as $language => $languageTitle) {

                    $output->writeln('');
                    $output->writeln(self::escapeYellow . 'Selecting workspace ' . $workspaceTitle . ' language ' . $languageTitle . self::escapeReset);
                    $output->writeln('');

                    $folder = $path . '/data/content/' . $contentTypeName . '/' . $workspace . '/' . $language;


                    if (file_exists($folder) && $input->getOption('merge') == false) {
                        $filesystem->remove($folder);
                    }

                    if (!file_exists($folder)) {
                        $filesystem->mkdir($folder);
                    }


                    if ($input->getOption('xlsx') == true) {

                        $data = $exporter->exportXLSX($repository, $contentTypeName, $workspace, $language);
                        if (!$data) {
                            $output->writeln(self::escapeError . 'Could not access repository ' . $repositoryName . '.' . self::escapeReset);
                            return;
                        }
                    } else // default (JSON)
                    {

                        $data = $exporter->exportJSON($repository, $contentTypeName, $workspace, $language);
                        if (!$data) {
                            $output->writeln(self::escapeError . 'Could not access repository ' . $repositoryName . '.' . self::escapeReset);
                            return;
                        }

                        $data = json_decode($data,true);

                        $output->writeln('');
                        $output->writeln(self::escapeCyan . 'Splitting ' . count($data['records']).' records into single json files.'. self::escapeReset);
                        $output->writeln('');


                        foreach ($data['records'] as $id => $record) {
                            $filename = $folder. '/'.$id . '.json';
                            $filesystem->dumpFile($filename, json_encode($record, JSON_PRETTY_PRINT));
                        }

                    }
                }
            }
        }


//
//
//        $filesystem = new Filesystem();
//
//        if ($verbose) {
//            $output->writeln('');
//            $output->writeln('Dumping data to ' . $filename);
//            $filesystem->dumpFile($filename, $data);
//            $output->writeln('');
//            $output->writeln('Done');
//            $output->writeln('');
//        } else {
//            $output->write($data);
//        }

    }
}