<?php

namespace AnyContent\Backend\Modules\Files\Controller;

use AnyContent\Backend\Controller\AbstractAnyContentBackendController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ANYCONTENT')]
class FilesController extends AbstractAnyContentBackendController
{
    #[Route('/xxx', 'anycontent_files')]
    public function xxx(): Response
    {
    }
}
