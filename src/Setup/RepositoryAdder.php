<?php

namespace AnyContent\Backend\Setup;

use AnyContent\Backend\Exception\AnyContentBackendException;
use AnyContent\Backend\Services\RepositoryManager;
use AnyContent\Client\Repository;
use AnyContent\Client\UserInfo;
use AnyContent\Connection\Configuration\ContentArchiveConfiguration;
use AnyContent\Connection\Configuration\MySQLSchemalessConfiguration;
use AnyContent\Connection\Configuration\RecordFilesConfiguration;
use AnyContent\Connection\Configuration\RecordsFileConfiguration;
use AnyContent\Connection\FileManager\DirectoryBasedFilesAccess;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Adds all configured repositories
 */
class RepositoryAdder
{
    private RepositoryManager $repositoryManager;

    public function __construct(private array $connections, private Security $security)
    {
    }

    public function addRepositories(RepositoryManager $repositoryManager)
    {
        $this->repositoryManager = $repositoryManager;

        $userInfo = new UserInfo($this->security->getUser()->getUserIdentifier());
        $this->repositoryManager->setUserInfo($userInfo);

        $recordsFileConnections = [];
        $recordFilesConnections = [];

        foreach ($this->connections as $connection) {
            $name = $connection['name'];
            $type = $connection['type'];

            $repository = null;

            switch ($type) {
                case 'recordsfile':
                    $dataType = isset($connection['content_file']) ? 'content' : 'config';
                    $dataFile = $connection['content_file'] ?? $connection['config_file'];
                    $filesPath = $connection['files_path'] ?? null;
                    $recordsFileConnections[$name][] = ['cmdl_file' => $connection['cmdl_file'], 'data_type' => $dataType, 'data_file' => $dataFile, 'files_path' => $filesPath];
                    break;
                case 'recordfiles':
                    $dataType = isset($connection['content_path']) ? 'content' : 'config';
                    $contentPath = $connection['content_path'] ?? null;
                    $configFile = $connection['config_file'] ?? null;
                    $filesPath = $connection['files_path'] ?? null;
                    $recordFilesConnections[$name][] = ['cmdl_file' => $connection['cmdl_file'], 'data_type' => $dataType, 'content_path' => $contentPath, 'config_file' => $configFile, 'files_path' => $filesPath];
                    break;
                case 'contentarchive':
                    $path = $connection['data_path'];
                    $repository = $this->addContentArchiveConnection($name, $path);
                    break;
                case 'mysql':
                    $host = $connection['db_host'];
                    $database = $connection['db_name'];
                    $user = $connection['db_user'];
                    $password = $connection['db_password'];
                    $port = $connection['db_port'];
                    $path = $connection['cmdl_path'];
                    $repository = $this->addMySQLConnection($name, $host, $database, $user, $password, $port, $path);
                    break;
            }

            if ($repository === null) {
                continue;
            }

            if (isset($connection['files_path'])) {
                $this->addFileManager($repository, $connection['files_path']);
            }

            $this->addRecordFilesConnections($recordFilesConnections);
            $this->addRecordsFileConnections($recordsFileConnections);
        }
    }

    private function addRecordsFileConnections($connections): void
    {
        foreach ($connections as $name => $dataTypes) {
            $filesPath = null;
            $configuration = new RecordsFileConfiguration();
            foreach ($dataTypes as $dataType) {
                if ($dataType['data_type'] === 'content') {
                    $configuration->addContentType(basename($dataType['cmdl_file'], '.cmdl'), $dataType['cmdl_file'], $dataType['data_file']);
                }
                if ($dataType['data_type'] === 'config') {
                    $configuration->addConfigType(basename($dataType['cmdl_file'], '.cmdl'), $dataType['cmdl_file'], $dataType['data_file']);
                }
                $filesPath = $dataType['files_path'] ?? $filesPath;
            }
            $connection = $configuration->createReadWriteConnection();
            $repository = new Repository($name, $connection);
            $this->repositoryManager->addRepository($name, $repository);
            if ($filesPath !== null) {
                $this->addFileManager($repository, $filesPath);
            }
        }
    }

    private function addRecordFilesConnections($connections): void
    {
        foreach ($connections as $name => $dataTypes) {
            $filesPath = null;
            $configuration = new RecordFilesConfiguration();
            foreach ($dataTypes as $dataType) {
                if ($dataType['data_type'] === 'content') {
                    $configuration->addContentType(basename($dataType['cmdl_file'], '.cmdl'), $dataType['cmdl_file'], $dataType['content_path']);
                }
                if ($dataType['data_type'] === 'config') {
                    $configuration->addConfigType(basename($dataType['cmdl_file'], '.cmdl'), $dataType['cmdl_file'], $dataType['config_file']);
                }
                $filesPath = $dataType['files_path'] ?? $filesPath;
            }
            $connection = $configuration->createReadWriteConnection();
            $repository = new Repository($name, $connection);
            $this->repositoryManager->addRepository($name, $repository);
            if ($filesPath !== null) {
                $this->addFileManager($repository, $filesPath);
            }
        }
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
