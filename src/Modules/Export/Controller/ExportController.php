<?php

namespace AnyContent\Backend\Modules\Export\Controller;

use AnyContent\Backend\Controller\AbstractAnyContentBackendController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ANYCONTENT')]
class ExportController extends AbstractAnyContentBackendController
{
    #[Route('/xxxxx', 'anycontent_records_export')]
    public function start(): Response
    {
    }
}
