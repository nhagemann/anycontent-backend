<?php

namespace AnyContent\Backend\Modules\Edit\Controller;


use AnyContent\Backend\Controller\AbstractAnyContentBackendController;
use AnyContent\Backend\Services\RepositoryManager;
use AnyContent\CMCK\Modules\Backend\Core\Application\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ANYCONTENT')]
class ConfigController extends AbstractAnyContentBackendController
{
    #[Route('/xxx','anycontent_config_edit')]
    public function xxx(): Response
    {

    }


}