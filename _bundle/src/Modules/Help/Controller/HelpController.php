<?php

namespace AnyContent\Backend\Modules\Help\Controller;


use AnyContent\Backend\Controller\AbstractAnyContentBackendController;
use AnyContent\Backend\Services\RepositoryManager;
use AnyContent\CMCK\Modules\Backend\Core\Application\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ANYCONTENT')]
class HelpController extends AbstractAnyContentBackendController
{
    #[Route('/help','anycontent_help')]
    public function help(): Response
    {

    }


}