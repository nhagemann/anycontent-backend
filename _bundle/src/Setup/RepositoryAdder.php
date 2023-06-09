<?php

namespace AnyContent\Backend\Setup;

use AnyContent\Backend\Services\RepositoryManager;
use AnyContent\Client\Repository;
use AnyContent\Connection\Configuration\ContentArchiveConfiguration;
use AnyContent\Connection\Configuration\RecordFilesConfiguration;
use App\Kernel;

class RepositoryAdder
{
    private RepositoryManager $repositoryManager;
    public function __construct(private array $connections)
    {
    }

    public function addRepositories(RepositoryManager $repositoryManager){

        $this->repositoryManager = $repositoryManager;

        foreach ($this->connections as $connection){
            $name = $connection['name'];
            $type = $connection['type'];


            switch ($type){
                case "contentarchive":

                    $path = $connection['path'];
                      $this->addContentArchiveConnection($name,$path);
                    break;
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

    private function addContentArchiveConnection(string $name, string $path){

        $configuration = new ContentArchiveConfiguration();
        $configuration->setContentArchiveFolder($path);
        $connection = $configuration->createReadWriteConnection();


        $repository = new Repository($name, $connection);
        $this->repositoryManager->addRepository($name,$repository);


    }
}