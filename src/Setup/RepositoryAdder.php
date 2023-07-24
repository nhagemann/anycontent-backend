<?php

namespace AnyContent\Backend\Setup;

use AnyContent\Backend\Exception\AnyContentBackendException;
use AnyContent\Backend\Services\RepositoryManager;
use AnyContent\Client\Repository;
use AnyContent\Connection\Configuration\ContentArchiveConfiguration;
use AnyContent\Connection\Configuration\MySQLSchemalessConfiguration;
use AnyContent\Connection\FileManager\DirectoryBasedFilesAccess;

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

//        $configuration = new RecordFilesConfiguration();
//
//        $configuration->addContentType('airline', __DIR__ . '/../../../_repositories/airlines.cmdl', __DIR__.'/../../../_repositories/records');
//        $configuration->addContentType('otherct', __DIR__ . '/../../../_repositories/otherct.cmdl', __DIR__.'/../../../_repositories/records');
//
//        $connection = $configuration->createReadWriteConnection();
//
//        $repository = new Repository('demo', $connection);
//        $repository->setPublicUrl('');
//
//        $repositoryManager->addRepository('demo',$repository);
//
//        $configuration = new RecordFilesConfiguration();
//
//        $configuration->addContentType('otherct', __DIR__ . '/../../../_repositories/otherct.cmdl', __DIR__.'/../../../_repositories/records');
//
//        $connection = $configuration->createReadWriteConnection();
//
//        $repository = new Repository('demo2', $connection);
//        $repository->setPublicUrl('');
//
//        $repositoryManager->addRepository('demo2',$repository);
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

    private function addMySQLConnection(string $name, string $host, string $database, string $user, string $password, string $port, string $path): Repository
    {
        $configuration = new MySQLSchemalessConfiguration();

        $configuration->initDatabase($host, $database, $user, $password, $port);

        $configuration->setCMDLFolder($path);
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
