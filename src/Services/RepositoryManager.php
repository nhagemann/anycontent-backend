<?php

namespace AnyContent\Backend\Services;

use AnyContent\Backend\Setup\RepositoryAdder;
use AnyContent\Client\Repository;
use AnyContent\Client\UserInfo;
use CMDL\ConfigTypeDefinition;

class RepositoryManager
{
    /**
     * @var Repository[]
     */
    protected $repositories = [];

    protected $repositoryAccessHashes = [];

    protected $contentTypeAccessHashes = [];

    protected $configTypeAccessHashes = [];

    /** @var  UserInfo */
    protected $userInfo;

    public function __construct(RepositoryAdder $repositoryAdder)
    {
        $repositoryAdder->addRepositories($this);
    }

    public function addRepository($name, Repository $repository, $title = ''): void
    {
        $repository->setName($name);
        $repository->setTitle($title);

        $userInfo = $repository->getCurrentUserInfo();
        if ($userInfo->getName() == '' && $this->userInfo != null) {
            $repository->setUserInfo($this->userInfo);
        }

        $this->repositories[$repository->getName()] = $repository;

        foreach ($repository->getContentTypeNames() as $contentTypeName) {
            $this->contentTypeAccessHashes[$this->getContentTypeAccessHash(
                $repository,
                $contentTypeName
            )] = ['repositoryId' => $repository->getName(), 'contentTypeName' => $contentTypeName];
        }

        foreach ($repository->getConfigTypeNames() as $configTypeName) {
            $this->configTypeAccessHashes[$this->getConfigTypeAccessHash(
                $repository,
                $configTypeName
            )] = ['repositoryId' => $repository->getName(), 'configTypeName' => $configTypeName];
        }

        $this->repositoryAccessHashes[$this->getRepositoryAccessHash(
            $repository
        )] = ['repositoryId' => $repository->getName()];
    }

    public function getRepositoryAccessHash(Repository $repository): string
    {
        return md5($repository->getName());
    }

    public function getContentTypeAccessHash(Repository $repository, $contentTypeName): string
    {
        return md5($repository->getName() . '-contentType-' . $contentTypeName);
    }

    public function getConfigTypeAccessHash(Repository $repository, $configTypeName): string
    {
        return md5($repository->getName() . '-contentType-' . $configTypeName);
    }

    public function getAccessHash($repository, $contentTypeDefinition = null): string
    {
        if ($contentTypeDefinition != null) {
            return $this->getContentTypeAccessHash($repository, $contentTypeDefinition->getName());
        } else {
            return $this->getRepositoryAccessHash($repository);
        }
    }

    public function setUserInfo(UserInfo $userInfo): void
    {
        $this->userInfo = $userInfo;
        foreach ($this->repositories as $repository) {
            $repository->setUserInfo($userInfo);
        }
    }

    public function listRepositories(): array
    {
        $repositories = [];
        foreach ($this->repositories as $repository) {
            $title = $repository->getTitle();
            if ($title == '') {
                $title = $repository->getName();
            }

            $repositories[$repository->getName()] = [
                'title'      => $title,
                'accessHash' => $this->getRepositoryAccessHash($repository),
            ];
        }

        return $repositories;
    }

    public function listContentTypes($id): array
    {
        $contentTypes = [];

        if (array_key_exists($id, $this->repositories)) {
            $repository = $this->repositories[$id];

            foreach ($repository->getContentTypeList() as $name => $title) {
                $contentTypes[$name] = [
                    'name'       => $name,
                    'title'      => $title,
                    'accessHash' => $this->getContentTypeAccessHash($repository, $name),
                ];
            }
        }

        return $contentTypes;
    }

    public function listConfigTypes($id): array
    {
        $configTypes = [];

        if (array_key_exists($id, $this->repositories)) {
            $repository = $this->repositories[$id];

            foreach ($repository->getConfigTypeList() as $name => $title) {
                $configTypes[$name] = [
                    'name'       => $name,
                    'title'      => $title,
                    'accessHash' => $this->getConfigTypeAccessHash($repository, $name),
                ];
            }
        }

        return $configTypes;
    }

    public function hasFiles($id): bool
    {
        if (array_key_exists($id, $this->repositories)) {
            $repository = $this->repositories[$id];

            return $repository->hasFiles();
        }

        return false;
    }

    public function getRepositoryById($id): ?Repository
    {
        if (array_key_exists($id, $this->repositories)) {
            return $this->repositories[$id];
        }

        return null;
    }

    public function getRepositoryByRepositoryAccessHash($hash): ?Repository
    {
        if (array_key_exists($hash, $this->repositoryAccessHashes)) {
            $id = $this->repositoryAccessHashes[$hash]['repositoryId'];

            return $this->getRepositoryById($id);
        }

        return null;
    }

    public function getRepositoryByContentTypeAccessHash($hash): ?Repository
    {
        if (array_key_exists($hash, $this->contentTypeAccessHashes)) {
            $id              = $this->contentTypeAccessHashes[$hash]['repositoryId'];
            $contentTypeName = $this->contentTypeAccessHashes[$hash]['contentTypeName'];
            $repository      = $this->getRepositoryById($id);

            $repository->selectContentType($contentTypeName);

            return $repository;
        }

        return null;
    }

    public function getRepositoryByConfigTypeAccessHash($hash): ?Repository
    {
        if (array_key_exists($hash, $this->configTypeAccessHashes)) {
            $id         = $this->configTypeAccessHashes[$hash]['repositoryId'];
            return $this->getRepositoryById($id);
        }

        return null;
    }

    public function getConfigTypeDefinitionByConfigTypeAccessHash($hash): ?ConfigTypeDefinition
    {
        if (array_key_exists($hash, $this->configTypeAccessHashes)) {
            $id             = $this->configTypeAccessHashes[$hash]['repositoryId'];
            $configTypeName = $this->configTypeAccessHashes[$hash]['configTypeName'];
            $repository     = $this->getRepositoryById($id);

            if ($repository->hasConfigType($configTypeName)) {
                return $repository->getConfigTypeDefinition($configTypeName);
            }
        }

        return null;
    }
}
