<?php

namespace AnyContent\CMCK\Modules\Backend\View\Glossary;

use AnyContent\CMCK\Modules\Backend\Core\Listing\BaseContentView;
use AnyContent\CMCK\Modules\Backend\Core\Listing\ListingRecord;

class ContentViewGlossary extends BaseContentView
{

    public function getTitle()
    {
        if ($this->customAnnotation->hasParam(2))
        {
            return $this->customAnnotation->getParam(2);
        }

        return 'Glossary';
    }


    public function getTemplate()
    {
        return 'listing-contentview-glossary.twig';
    }


    public function apply($vars)
    {

        $glossary = array();

        $this->getRepository()->registerRecordClassForContentType($this->getContentTypeDefinition()
                                                                       ->getName(), 'AnyContent\CMCK\Modules\Backend\Core\Listing\ListingRecord');


        $records = $this->getRepository()->getRecords('','name');

        /** @var ListingRecord $record */
        foreach ($records as $record)
        {
            $record->initListingRecord($this->app, $this->contentTypeAccessHash);

            $index       = '0-9';
            $firstLetter = strtoupper(substr($record->getName(), 0, 1));
            if ($firstLetter >= 'A' && $firstLetter <= 'Z')
            {
                $index = $firstLetter;
            }
            $glossary[$index][] = $record;

        }

        ksort($glossary);

        foreach ($glossary as $index => $items)
        {
            $c                = max(25, count($items));
            $c                = ceil($c / 3);
            $glossary[$index] = array_chunk($items, $c);
        }

        $vars['glossary'] = $glossary;

        return $vars;
    }
}