<?php

namespace AnyContent\Backend\Setup;

use AnyContent\Backend\ContentViews\DefaultContentView;
use AnyContent\Backend\ContentViews\Glossary\ContentViewGlossary;
use AnyContent\Backend\Services\ContentViewsManager;
use AnyContent\Backend\Services\ContextManager;
use AnyContent\Backend\Services\RepositoryManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ContentViewsAdder
{
    public function __construct(
        private RepositoryManager $repositoryManager,
        private ContextManager $contextManager,
        private UrlGeneratorInterface $urlGenerator,
        private DefaultContentView $defaultContentView,
    ) {
    }

    public function setupContentViews(ContentViewsManager $contentViewsManager)
    {
        $this->defaultContentView->setName('default');
        $contentViewsManager->registerContentView('default', $this->defaultContentView);

        $view = new ContentViewGlossary();
        $view->init('glossary', $this->repositoryManager, $this->contextManager, $this->urlGenerator);
        $contentViewsManager->registerContentView('glossary', $view);
    }
}
