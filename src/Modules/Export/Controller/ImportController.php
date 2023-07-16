<?php

namespace AnyContent\Backend\Modules\Export\Controller;

use AnyContent\Backend\Controller\AbstractAnyContentBackendController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ANYCONTENT')]
class ImportController extends AbstractAnyContentBackendController
{
    #[Route('/modal/content/import/{contentTypeAccessHash}', 'anycontent_records_import_modal')]
    public function start(): Response
    {
    }
}
