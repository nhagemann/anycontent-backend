<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\Exchange;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


use AnyContent\CMCK\Modules\Backend\Core\Repositories\RepositoryManager;

use Symfony\Component\Filesystem\Filesystem;

class ExportCommand extends \AnyContent\CMCK\Modules\Backend\Core\Application\Command
{

    protected function configure()
    {
        $this->setName('cmck:export')
            ->setDescription('Export records of a content type into one json or Excel/XML file.')
            ->addArgument(
                'repository',
                InputArgument::REQUIRED,
                'Name/Id of the repository having the content type to be exported. Use the list command to show available repositories.'
            )
            ->addArgument('contentType', InputArgument::REQUIRED, 'Name of the content type to be exported.')
            ->addArgument('workspace', InputArgument::OPTIONAL, 'Name of the workspace to be exported [default].')
            ->addArgument('language', InputArgument::OPTIONAL, 'Name of the language to be exported [default].')
            ->addOption('json', 'j')
            ->addOption('xlsx', 'x')
            ->addOption('print', 'p')
            ->addOption('repositoryFolder', 'r', InputOption::VALUE_NONE, 'Create repository folder.');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {


        $app = $this->getSilexApplication();

        /** @var RepositoryManager $repositoryManager */
        $repositoryManager = $app['repos'];

        $verbose = !$input->getOption('print');
        $repositoryFolder = $input->getOption('repositoryFolder');


        $argumentContentTypeName = $input->getArgument('contentType');
        $argumentRepositoryName = $input->getArgument('repository');

        $argumentWorkspace = 'default';

        if ($input->getArgument('workspace') != '') {
            $argumentWorkspace = $input->getArgument('workspace');
        }

        $argumentLanguage = 'default';
        if ($input->getArgument('language') != '') {
            $argumentLanguage = $input->getArgument('language');
        }


        $exporter = new Exporter();

        if ($verbose) {
            $exporter->setOutput($output);
        }


        if ($argumentRepositoryName == '?') {
            $repositories = array_keys($repositoryManager->listRepositories());
        } else {
            $repositories = [$argumentRepositoryName];
        }

        foreach ($repositories as $repositoryName) {
            $repository = $repositoryManager->getRepositoryById($repositoryName);

            if (!$repository) {
                $output->writeln(
                    self::escapeError.'Repository '.$repositoryName.' unknown. Use the list command to show available repositories.'.self::escapeReset

                );
                break;
            }


            if ($verbose) {
                $output->writeln('');
                $output->writeln('Selecting repository '.$repositoryName);
                $output->writeln('');
            }

            if ($argumentContentTypeName == '?') {
                $contentTypeNames = $repository->getContentTypeNames();
            } else {
                $contentTypeNames = [$argumentContentTypeName];
            }

            foreach ($contentTypeNames as $contentTypeName) {
                if ($verbose) {
                    $output->writeln('');
                    $output->writeln('Starting export for content type '.$contentTypeName);
                    $output->writeln('');
                }

                if (!$repository->hasContentType($contentTypeName)) {
                    $output->writeln(
                        self::escapeError.'Repository '.$repositoryName.' does not have a content type named '.$contentTypeName.'. Use the list command to show available content types.'.self::escapeReset
                    );
                    break;
                }

                $definition = $repository->getContentTypeDefinition($contentTypeName);

                if ($argumentWorkspace == '?') {
                    $workspaces = array_keys($definition->getWorkspaces());
                } else {
                    $workspaces = [$argumentWorkspace];
                }


                foreach ($workspaces as $workspace) {

//                    if (!$definition->hasWorkspace($workspace)) {
//                        $output->writeln(
//                            self::escapeError.'Content type '.$contentTypeName.' does not have a workspace named '.$workspace.self::escapeReset
//                        );
//                        break;
//                    }

                    if ($verbose) {
                        $output->writeln('');
                        $output->writeln('Selecting workspace '.$workspace);
                        $output->writeln('');
                    }

                    if ($argumentLanguage == '?') {
                        $languages = array_keys($definition->getLanguages());
                    } else {
                        $languages = [$argumentLanguage];
                    }

                    foreach ($languages as $language) {

//                        if (!$repository->getContentTypeDefinition($contentTypeName)->hasLanguage($language)) {
//                            $output->writeln(
//                                self::escapeError.'Content type '.$contentTypeName.' does not have a language named '.$language.self::escapeReset
//                            );
//                            break;
//                        }

                        if ($verbose) {
                            $output->writeln('');
                            $output->writeln('Selecting language '.$language);
                            $output->writeln('');
                        }

                        if ($input->getOption('xlsx') == true) {
                            $filename = $contentTypeName.'.'.$workspace.'.'.$language.'.xlsx';
                            $data = $exporter->exportXLSX($repository, $contentTypeName, $workspace, $language);

                        } else // default (JSON)
                        {
                            $filename = $contentTypeName.'.'.$workspace.'.'.$language.'.json';
                            $data = $exporter->exportJSON($repository, $contentTypeName, $workspace, $language);

                        }

                        if (!$data) {
                            $output->writeln(
                                self::escapeError.'Could not export data.'.self::escapeReset
                            );
                        } else {
                            $filesystem = new Filesystem();

                            if ($repositoryFolder) {
                                $filesystem->mkdir($repositoryName);
                                $filename = $repositoryName.'/'.$filename;
                            }

                            if ($verbose) {
                                $output->writeln('');
                                $output->writeln('Dumping data to '.$filename);
                                $filesystem->dumpFile($filename, $data);
                                $output->writeln('');
                                $output->writeln('Done');
                                $output->writeln('');
                            } else {
                                $output->write($data);
                            }
                        }

                    }

                }
            }

        }
        return;
    }
}