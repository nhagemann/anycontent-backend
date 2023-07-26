<?php

namespace AnyContent\Backend\ContentViews;

use AnyContent\Backend\Services\ContextManager;
use AnyContent\Backend\Services\RepositoryManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AbstractContentView
{
    protected string $name;

    protected RepositoryManager $repositoryManager;
    protected ContextManager $contextManager;

    protected UrlGeneratorInterface $urlGenerator;

    public function init(
        string $name,
        RepositoryManager $repositoryManager,
        ContextManager $contextManager,
        UrlGeneratorInterface $urlGenerator
    ): void {
        $this->name = $name;
        $this->repositoryManager = $repositoryManager;
        $this->contextManager = $contextManager;
        $this->urlGenerator = $urlGenerator;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getTitle()
    {
        return 'Listing';
    }

    public function getTemplate(): string
    {
        return '@AnyContentBackend/Listing/listing-contentview-default.html.twig';
    }

    public function doesProcessSearch()
    {
        return true;
    }

    public function __invoke(&$vars)
    {
    }
}
