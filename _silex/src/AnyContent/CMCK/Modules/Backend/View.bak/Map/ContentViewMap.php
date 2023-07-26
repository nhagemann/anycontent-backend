<?php

namespace AnyContent\CMCK\Modules\Backend\View\Map;

use AnyContent\CMCK\Modules\Backend\Core\Listing\BaseContentView;

/**
 * Class ContentViewMap
 *
 * Usage:  @custom content-view map property
 *
 * @package AnyContent\CMCK\Modules\Backend\View\Map
 */
class ContentViewMap extends BaseContentView
{

    public function getTitle()
    {
        if ($this->customAnnotation->hasParam(3))
        {
            return $this->customAnnotation->getParam(3);
        }

        return 'Map';
    }


    public function getTemplate()
    {
        return 'content-view-map.twig';
    }


    public function apply($vars)
    {


        $customAnnotation = $this->getCustomAnnotation();

        $property = $customAnnotation->getParam(2);

        $places = array();


        $this->getRepository()->registerRecordClassForContentType($this->getContentTypeDefinition()
                                                                       ->getName(), 'AnyContent\CMCK\Modules\Backend\Core\Listing\ListingRecord');

        $records = $this->getRepository()->getRecords($property .' <> ""');


        foreach ($records as $record)
        {

            $location = explode(',', $record->getProperty($property));
            if (count($location) == 2)
            {
                $place          = array();
                $place['id']    = $record->getId();
                $place['title'] = $record->getName();
                $place['lat']   = $location[0];
                $place['long']  = $location[1];
                $place['url']   = $this->app['url_generator']->generate('editRecord', array( 'contentTypeAccessHash' => $this->getContentTypeAccessHash(), 'recordId' => $record->getId(), 'workspace' => $this->getContext()
                                                                                                                                                                                                               ->getCurrentWorkspace(), 'language' => $this->getContext()
                                                                                                                                                                                                                                                           ->getCurrentLanguage()
                ));

                $places[] = $place;
            }

        }

        $vars['places'] = json_encode($places);

        return $vars;
    }
}