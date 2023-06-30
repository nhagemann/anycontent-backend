<?php

namespace AnyContent\Backend\Modules\Revisions\Controller;

use AnyContent\Backend\Controller\AbstractAnyContentBackendController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ANYCONTENT')]
class RevisionsController extends AbstractAnyContentBackendController
{
    #[Route('/xxx', 'anycontent_records_revisions')]
    public function xxx(): Response
    {
    }
}
