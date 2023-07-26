<?php

namespace AnyContent\Backend\Setup;

use AnyContent\Backend\ContentListViews\DefaultTable\DefaultTableContentListView;
use AnyContent\Backend\ContentListViews\Glossary\ContentListListViewGlossary;
use AnyContent\Backend\Services\ContentListViewsManager;
use AnyContent\Backend\Services\ContextManager;
use AnyContent\Backend\Services\RepositoryManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ContentListViewsAdder
{
    public function __construct(
        private RepositoryManager           $repositoryManager,
        private ContextManager              $contextManager,
        private UrlGeneratorInterface       $urlGenerator,
        private DefaultTableContentListView $defaultContentView,
    ) {
    }

    public function setupContentViews(ContentListViewsManager $contentViewsManager)
    {
        $this->defaultContentView->setName('default');
        $contentViewsManager->registerContentView('default', $this->defaultContentView);

        $view = new ContentListListViewGlossary();
        $view->init('glossary', $this->repositoryManager, $this->contextManager, $this->urlGenerator);
        $contentViewsManager->registerContentView('glossary', $view);
    }
}
