<?php

namespace AnyContent\CMCK\Modules\Backend\Admin\ExcelBackup;

use AnyContent\CMCK\Modules\Backend\Edit\Exchange\Exporter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use AnyContent\CMCK\Modules\Backend\Core\Repositories\RepositoryManager;

use Symfony\Component\Filesystem\Filesystem;

class BackupCommand extends \AnyContent\CMCK\Modules\Backend\Core\Application\Command
{

    protected function configure()
    {
        $this->setName('cmck:backup:export')
             ->setDescription('Creates excel backup archive of a repository.')
             ->addArgument(
                 'repository',
                 InputArgument::REQUIRED,
                 'Name/Id of the repository having the content type to be exported. Use the list command to show available repositories.'
             )
             ->addArgument('contentType', InputArgument::OPTIONAL, 'Name of the content type to be exported.')
             ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Provide path to export folder.')
             ->addOption('filename', 'f', InputOption::VALUE_REQUIRED, 'Set filename to export.');
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

        $exporter = new Exporter();
        $exporter->setOutput($output);

        if ($input->getArgument('contentType') != '') {
            $data     = $exporter->backupXLSX($repository, $input->getArgument('contentType'));
            $filename = $repositoryName . '_' . $input->getArgument('contentType') . '_' . date('Ymd_hi') . '.xlsx';
        }
        else {
            $data     = $exporter->backupXLSX($repository);
            $filename = $repositoryName . '_all_' . date('Ymd_hi') . '.xlsx';
        }

        // Check if filename has been provied
        if ($input->getOption('filename')) {
            $filename = $input->getOption('filename');
        }

        // Check if path has been provided

        if ($input->getOption('path')) {
            $path     = $input->getOption('path');
            $realPath = realpath($path);
            if (!$realPath) {
                $output->writeln(self::escapeError . 'Path ' . $path . ' not found.' . self::escapeReset);

                return;
            }

            $filename = $path . '/' . $filename;
        }

        $filesystem = new Filesystem();
        $output->writeln('Dumping data to ' . $filename);
        $filesystem->dumpFile($filename, $data);
        $output->writeln('');
        $output->writeln('Done');
        $output->writeln('');

        return;
    }
}