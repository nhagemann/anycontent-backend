<?php

namespace AnyContent\CMCK\Modules\Backend\Admin\ExcelBackup;

use AnyContent\CMCK\Modules\Backend\Edit\Exchange\Exporter;
use AnyContent\CMCK\Modules\Backend\Edit\Exchange\Importer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use AnyContent\CMCK\Modules\Backend\Core\Repositories\RepositoryManager;

use Symfony\Component\Filesystem\Filesystem;

class BackupImportCommand extends \AnyContent\CMCK\Modules\Backend\Core\Application\Command
{

    protected function configure()
    {
        $this->setName('cmck:backup:import')
             ->setDescription('Imports excel backup archive of a repository.')
             ->addArgument(
                 'repository',
                 InputArgument::REQUIRED,
                 'Name/Id of the repository having the content type to be imported. Use the list command to show available repositories.'
             )
             ->addArgument('filename', InputArgument::REQUIRED, 'Name of the file to be imported.')
             ->addArgument('contentType', InputArgument::OPTIONAL, 'Name of the content type to be imported.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $app = $this->getSilexApplication();

        $repositoryName = $input->getArgument('repository');

        /** @var RepositoryManager $repositoryManager */
        $repositoryManager = $app['repos'];

        $repository = $repositoryManager->getRepositoryById($repositoryName);

        if (!$repository) {
            $output->writeln(self::escapeError . 'Repository ' . $repositoryName . ' unknown. Use the list command to show available repositories.' . self::escapeReset);

            return;
        }

        $output->writeln('');
        $output->writeln('Selecting repository ' . $repositoryName);
        $output->writeln('');

        $filename = $input->getArgument('filename');

        if (strpos($filename, '/') !== 0) {
            $filename = getcwd() . '/' . $filename;
        }
        $filename = realpath($filename);

        if (!file_exists($filename)) {
            $output->writeln(self::escapeError . 'Could not find/access file.' . self::escapeReset);

            return;
        }

        $importer = new Importer();
        $importer->setOutput($output);

        if ($input->getArgument('contentType') != '') {
            $importer->importBackupXLSX($repository, $filename, $input->getArgument('contentType'), 'exchange');
        }
        else {
            $importer->importBackupXLSX($repository, $filename, null, 'exchange');
        }

        return;
    }
}