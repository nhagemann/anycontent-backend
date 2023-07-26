<?php

namespace AnyContent\Backend\Services;

use AnyContent\Backend\ContentListViews\ContentListViewInterface;
use AnyContent\Backend\DependencyInjection\DefaultImplementation;
use AnyContent\Client\Repository;
use CMDL\ContentTypeDefinition;

class ContentListViewsManager
{
    private array $contentViews = [];

    public function getContentView(string $name, Repository $repository, ContentTypeDefinition $contentTypeDefinition)
    {
        if (array_key_exists($name, $this->contentViews)) {
            return $this->contentViews[$name];
        }
        return $this->contentViews['default'];
    }

    public function getContentViews(Repository $repository, ContentTypeDefinition $contentTypeDefinition)
    {
        $contentViews = [];
        foreach ($contentTypeDefinition->getCustomAnnotations() as $customAnnotation) {
            if ($customAnnotation->getType() == 'content-list-view') {
                if ($customAnnotation->hasParam(1)) {
                    $name = $customAnnotation->getParam(1);
                    if (array_key_exists($name, $this->contentViews)) {
                        $contentViews[$name] = $this->contentViews[$name];
                    }
                }
            }
        }
        return $contentViews;
    }

    public function registerContentView(ContentListViewInterface $defaultContentView)
    {
        if (array_key_exists($defaultContentView->getName(), $this->contentViews)) {
            if (!$this->contentViews[$defaultContentView->getName()] instanceof DefaultImplementation) {
                return;
            }
        }
        $this->contentViews[$defaultContentView->getName()] = $defaultContentView;
    }
}
