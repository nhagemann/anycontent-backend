<?php

namespace AnyContent\Backend\Setup;

use AnyContent\Backend\Exception\AnyContentBackendException;
use AnyContent\Backend\Services\RepositoryManager;
use AnyContent\Client\Repository;
use AnyContent\Connection\Configuration\ContentArchiveConfiguration;
use AnyContent\Connection\Configuration\MySQLSchemalessConfiguration;
use AnyContent\Connection\Configuration\RecordsFileConfiguration;
use AnyContent\Connection\FileManager\DirectoryBasedFilesAccess;
use Symfony\Component\Finder\Finder;

/**
 * Adds all configured repositories
 */
class RepositoryAdder
{
    private RepositoryManager $repositoryManager;

    public function __construct(private array $connections)
    {
    }

    public function addRepositories(RepositoryManager $repositoryManager)
    {
        $this->repositoryManager = $repositoryManager;

        foreach ($this->connections as $connection) {
            $name = $connection['name'];
            $type = $connection['type'];

            $repository = null;

            switch ($type) {
                case 'recordsfile':
                    $data = $connection['data'];
                    $cmdl = $connection['cmdl'];
                    $repository = $this->addRecordsFileConnection($name, $data, $cmdl);
                    break;
                case 'recordfiles':
                    //$path = $connection['path'];
                    //$repository = $this->addRecordFilesConnection($name, $path);
                    break;
                case 'contentarchive':
                    $path = $connection['path'];
                    $repository = $this->addContentArchiveConnection($name, $path);
                    break;
                case 'mysql':
                    $host = $connection['db_host'];
                    $database = $connection['db_name'];
                    $user = $connection['db_user'];
                    $password = $connection['db_password'];
                    $port = $connection['db_port'];
                    $path = $connection['path'];
                    $repository = $this->addMySQLConnection($name, $host, $database, $user, $password, $port, $path);
                    break;
            }

            if ($repository === null) {
                continue;
            }

            if (isset($connection['files'])) {
                $files = $connection['files'];
                $this->addFileManager($repository, $files);
            }
        }
    }

    private function addRecordsFileConnection(string $name, string $dataFolder, string $cmdlFolder): Repository
    {
        if (!file_exists($cmdlFolder)) {
            throw new AnyContentBackendException(sprintf('Cannot find cmdl folder %s for connection %s', $cmdlFolder, $name));
        }
        if (!file_exists($dataFolder)) {
            throw new AnyContentBackendException(sprintf('Cannot find data folder %s for connection %s', $dataFolder, $name));
        }

        // Add all content types
        $finder = new Finder();
        $configuration = new RecordsFileConfiguration();
        foreach ($finder->in($cmdlFolder)->depth(0)->name('*.cmdl')->files() as $file) {
            $contentTypeName = $file->getFilenameWithoutExtension();
            $dataFileName = sprintf('%s/%s.json', $file->getPath(), $file->getFilenameWithoutExtension());
            $cmdlFilename = (string)$file->getRealPath();
            $configuration->addContentType($contentTypeName, $cmdlFilename, $dataFileName);
        }

        // Add config types, if there is a config subfolder
        $finder = new Finder();
        $cmdlFolder = sprintf('%s/config', $cmdlFolder);
        if (file_exists($cmdlFolder)) {
            foreach ($finder->in($cmdlFolder)->depth(0)->name('*.cmdl')->files() as $file) {
                $configTypeName = $file->getFilenameWithoutExtension();
                $dataFileName = sprintf('%s/%s.json', $file->getPath(), $file->getFilenameWithoutExtension());
                $cmdlFilename = (string)$file->getRealPath();
                $configuration->addConfigType($configTypeName, $cmdlFilename, $dataFileName);
            }
        }

        $connection = $configuration->createReadWriteConnection();

        $repository = new Repository($name, $connection);
        $this->repositoryManager->addRepository($name, $repository);
        return $repository;
    }

    private function addContentArchiveConnection(string $name, string $path): Repository
    {
        $configuration = new ContentArchiveConfiguration();
        $configuration->setContentArchiveFolder($path);
        $connection = $configuration->createReadWriteConnection();

        $repository = new Repository($name, $connection);
        $this->repositoryManager->addRepository($name, $repository);
        return $repository;
    }

    private function addMySQLConnection(string $name, string $host, string $database, string $user, string $password, string $port, string $cmdlFolderPath): Repository
    {
        $configuration = new MySQLSchemalessConfiguration();

        $configuration->initDatabase($host, $database, $user, $password, $port);

        $configuration->setCMDLFolder($cmdlFolderPath);
        $configuration->setRepositoryName($name);
        $configuration->addContentTypes();
        $configuration->addConfigTypes();

        $connection = $configuration->createReadWriteConnection();

        $repository = new Repository('phpunit', $connection);
        $this->repositoryManager->addRepository($name, $repository);
        return $repository;
    }

    private function addFileManager(Repository $repository, string $files)
    {
        if ($files !== '') {
            if (!file_exists($files)) {
                throw new AnyContentBackendException(sprintf('AnyContent Backend configuration error: Files path %s does not exist.', $files));
            }

            $fileManager = new DirectoryBasedFilesAccess($files);
            $fileManager->enableImageSizeCalculation();
            $repository->setFileManager($fileManager);
        }
    }
}
