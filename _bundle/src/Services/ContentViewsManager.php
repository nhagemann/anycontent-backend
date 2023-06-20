<?php

namespace AnyContent\Backend\Services;

use AnyContent\Client\Repository;
use AnyContent\CMCK\Modules\Backend\Core\Listing\BaseContentView;
use CMDL\ContentTypeDefinition;

class ContentViewsManager
{
    protected $contentViewRegistrations = [];

    protected $contentViewObjects = [];

    public function registerContentView($type, $class, $options = [])
    {
        $this->contentViewRegistrations[$type] = ['class' => $class, 'options' => $options];
    }

    /**
     * @param ContentTypeDefinition $contentTypeDefinition
     * @param                       $contentTypeAccessHash
     * @param                       $nr
     *
     * @return bool | BaseContentView
     */
    public function getContentView(Repository $repository, ContentTypeDefinition $contentTypeDefinition, $contentTypeAccessHash, $nr)
    {
        $contentViews = $this->getContentViews($repository, $contentTypeDefinition, $contentTypeAccessHash);

        if (array_key_exists($nr, $contentViews)) {
            return $contentViews[$nr];
        }

        return false;
    }

    public function getContentViews(Repository $repository, ContentTypeDefinition $contentTypeDefinition, $contentTypeAccessHash)
    {
        if (!array_key_exists($contentTypeAccessHash, $this->contentViewObjects)) {
            $i                                                = 0;
            $this->contentViewObjects[$contentTypeAccessHash] = [];
            /** @var  $customAnnotation CustomAnnotation */
            foreach ($contentTypeDefinition->getCustomAnnotations() as $customAnnotation) {
                if ($customAnnotation->getType() == 'content-view') {
                    if ($customAnnotation->hasParam(1)) {
                        $i++;
                        $type = $customAnnotation->getParam(1);

                        if (array_key_exists($type, $this->contentViewRegistrations)) {
                            $class = $this->contentViewRegistrations[$type]['class'];

                            $contentView = new $class($i, $this->app, $repository, $contentTypeDefinition, $contentTypeAccessHash, $customAnnotation, $this->contentViewRegistrations[$type]['options']);

                            $this->contentViewObjects[$contentTypeAccessHash][$i] = $contentView;
                        }
                    }
                }
            }
        }

        return $this->contentViewObjects[$contentTypeAccessHash];
    }
}
