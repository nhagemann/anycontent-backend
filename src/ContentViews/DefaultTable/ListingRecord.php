<?php

namespace AnyContent\Backend\ContentViews\DefaultTable;

use AnyContent\Client\Record;
use AnyContent\CMCK\Modules\Backend\Core\Application\Application;
use AnyContent\CMCK\Modules\Backend\Core\Context\ContextManager;
use Symfony\Component\Routing\Generator\UrlGenerator;

class ListingRecord extends Record
{
    /** @var  Application */
    protected $app;

    protected $contentTypeAccessHash;

    /**
     * @return UrlGenerator
     */
    protected function getUrlGenerator()
    {
        return $this->app['url_generator'];
    }

    /**
     * @return ContextManager
     */
    protected function getContext()
    {
        return $this->app['context'];
    }

    public function initListingRecord(Application $app, $contentTypeAccessHash)
    {
        $this->app                   = $app;
        $this->contentTypeAccessHash = $contentTypeAccessHash;
    }

    public function getEditUrl()
    {
        return $this->getUrlGenerator()
                    ->generate('editRecord', [ 'contentTypeAccessHash' => $this->contentTypeAccessHash, 'recordId' => $this->getID(), 'workspace' => $this->getContext()
                                                                                                                                                               ->getCurrentWorkspace(), 'language' => $this->getContext()
                                                                                                                                                                                                           ->getCurrentLanguage(),
                    ]);
    }

    public function getDeleteUrl()
    {
        return $this->getUrlGenerator()
                    ->generate('deleteRecord', [ 'contentTypeAccessHash' => $this->contentTypeAccessHash, 'recordId' => $this->getID(), 'workspace' => $this->getContext()
                                                                                                                                                                 ->getCurrentWorkspace(), 'language' => $this->getContext()
                                                                                                                                                                                                             ->getCurrentLanguage(),
                    ]);
    }
}
