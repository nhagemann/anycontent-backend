<?php

namespace AnyContent\Backend\Setup;

use AnyContent\Backend\Services\RepositoryManager;
use AnyContent\Client\Repository;
use AnyContent\Connection\Configuration\RecordFilesConfiguration;
use App\Kernel;

class RepositoryAdder
{
    public function addRepositories(RepositoryManager $repositoryManager){


        $configuration = new RecordFilesConfiguration();

        $configuration->addContentType('airline', __DIR__ . '/../../../_repositories/airlines.cmdl', __DIR__.'/../../../_repositories/records');

        $connection = $configuration->createReadWriteConnection();

        $repository = new Repository('demo', $connection);
        $repository->setPublicUrl('');

        $repositoryManager->addRepository('demo',$repository);
    }
}