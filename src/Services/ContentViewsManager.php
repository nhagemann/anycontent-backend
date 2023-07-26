<?php

namespace AnyContent\Backend\Services;

use AnyContent\Backend\Setup\ContentViewsAdder;
use AnyContent\Client\Repository;
use CMDL\ContentTypeDefinition;

class ContentViewsManager
{
    private array $contentViews = [];

    public function __construct(
        private ContentViewsAdder $contentViewsAdder
    ) {
        $this->contentViewsAdder->setupContentViews($this);
    }

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
            if ($customAnnotation->getType() == 'content-view') {
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

    public function registerContentView(string $name, $defaultContentView)
    {
        $this->contentViews[$name] = $defaultContentView;
    }
}
