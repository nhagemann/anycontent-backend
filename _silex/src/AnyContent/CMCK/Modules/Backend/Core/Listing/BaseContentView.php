<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Listing;

use AnyContent\Client\Repository;
use AnyContent\CMCK\Modules\Backend\Core\Application\Application;
use AnyContent\CMCK\Modules\Backend\Core\Context\ContextManager;
use AnyContent\CMCK\Modules\Backend\Core\Layout\LayoutManager;
use AnyContent\CMCK\Modules\Backend\Core\Pager\PagingHelper;
use AnyContent\CMCK\Modules\Backend\Core\User\UserManager;
use CMDL\Annotations\CustomAnnotation;
use CMDL\ContentTypeDefinition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;

class BaseContentView
{

    protected $nr;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Repository
     */

    protected $repository;
    /**
     * @var ContentTypeDefinition
     */
    protected $contentTypeDefinition;

    /**
     * @var string
     */
    protected $contentTypeAccessHash;
    /**
     * @var CustomAnnotation
     */
    protected $customAnnotation;


    /**
     * @return UrlGenerator
     */
    public function getUrlGenerator()
    {
        return $this->app['url_generator'];
    }


    /**
     * @return ContextManager
     */
    public function getContext()
    {
        return $this->app['context'];
    }


    /**
     * @return LayoutManager
     */
    public function getLayout()
    {
        return $this->app['layout'];
    }


    /**
     * @return Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }


    /**
     * @return ContentTypeDefinition
     */
    public function getContentTypeDefinition()
    {
        return $this->contentTypeDefinition;
    }


    /**
     * @return string
     */
    public function getContentTypeAccessHash()
    {
        return $this->contentTypeAccessHash;
    }


    /**
     * @return CustomAnnotation
     */
    public function getCustomAnnotation()
    {
        return $this->customAnnotation;
    }


    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->app['request'];
    }


    /**
     * @return PagingHelper
     */
    public function getPager()
    {
        return $this->app['pager'];
    }


    public function __construct($nr, Application $app, Repository $repository, ContentTypeDefinition $contentTypeDefinition, $contentTypeAccessHash, CustomAnnotation $customAnnotation = null)
    {
        $this->nr                    = $nr;
        $this->app                   = $app;
        $this->repository            = $repository;
        $this->contentTypeDefinition = $contentTypeDefinition;
        $this->contentTypeAccessHash = $contentTypeAccessHash;
        $this->customAnnotation      = $customAnnotation;
    }


    public function getUrl()
    {
        return $this->getUrlGenerator()
                    ->generate('listRecords', array( 'contentTypeAccessHash' => $this->contentTypeAccessHash, 'nr' => $this->nr ));
    }


    public function getTitle()
    {
        return 'List';
    }


    public function getTemplate()
    {
        return 'template.twig';
    }


    public function doesProcessSearch()
    {
        return false;
    }


    public function apply($vars)
    {
        return $vars;
    }


    public function canDo($action, $object1 = null, $object2 = null, $object3 = null)
    {
        /** @var UserManager $user */
        $user = $this->app['user'];

        return $user->canDo($action, $object1, $object2, $object3);

    }
}