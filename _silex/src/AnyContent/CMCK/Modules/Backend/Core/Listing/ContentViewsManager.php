<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Listing;

use AnyContent\Client\Repository;
use CMDL\ContentTypeDefinition;

class ContentViewsManager
{

    protected $contentViewRegistrations = array();

    protected $contentViewObjects = array();


    public function __construct($app)
    {
        $this->app = $app;
    }


    public function registerContentView($type, $class, $options = array())
    {
        $this->contentViewRegistrations[$type] = array( 'class' => $class, 'options' => $options );
    }


    /**
     * @param ContentTypeDefinition $contentTypeDefinition
     * @param                       $contentTypeAccessHash
     * @param                       $nr
     *
     * @return bool | BaseContentView
     */
    public function getContentView(Repository $repository,ContentTypeDefinition $contentTypeDefinition, $contentTypeAccessHash, $nr)
    {

        $contentViews = $this->getContentViews($repository, $contentTypeDefinition, $contentTypeAccessHash);

        if (array_key_exists($nr, $contentViews))
        {
            return $contentViews[$nr];
        }

        return false;
    }


    public function getContentViews(Repository $repository, ContentTypeDefinition $contentTypeDefinition, $contentTypeAccessHash)
    {
        if (!array_key_exists($contentTypeAccessHash, $this->contentViewObjects))
        {
            $i                                                = 0;
            $this->contentViewObjects[$contentTypeAccessHash] = array();
            /** @var  $customAnnotation CustomAnnotation */
            foreach ($contentTypeDefinition->getCustomAnnotations() as $customAnnotation)
            {
                if ($customAnnotation->getType() == 'content-view')
                {
                    if ($customAnnotation->hasParam(1))
                    {
                        $i++;
                        $type = $customAnnotation->getParam(1);

                        if (array_key_exists($type, $this->contentViewRegistrations))
                        {
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