<?php

namespace AnyContent\Backend\Modules\Help\Controller;

use AnyContent\Backend\Controller\AbstractAnyContentBackendController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ANYCONTENT')]
class HelpController extends AbstractAnyContentBackendController
{
    #[Route('/help', name:'anycontent_help', methods: ['GET'])]
    public function help(): Response
    {
        return $this->render('@AnyContentBackend/Help/help.html.twig');
    }
}
