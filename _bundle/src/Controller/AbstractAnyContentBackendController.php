<?php

namespace AnyContent\Backend\Controller;

use AnyContent\Backend\Services\ContentViewsManager;
use AnyContent\Backend\Services\ContextManager;
use AnyContent\Backend\Services\RepositoryManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractAnyContentBackendController extends AbstractController
{

    public function __construct(
        protected RepositoryManager $repositoryManager,
        protected ContextManager $contextManager,
    )
    {
    }
    public function render(string $view, array $parameters = [], Response $response = null): Response
    {
        $parameters['anycontent']=[];
        $parameters['anycontent']['context']=$this->contextManager;
        $parameters['anycontent']['repositories']=$this->repositoryManager;

        return parent::render($view, $parameters, $response);
    }

}