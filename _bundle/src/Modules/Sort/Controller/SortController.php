<?php

namespace AnyContent\Backend\Modules\Sort\Controller;

use AnyContent\Backend\Controller\AbstractAnyContentBackendController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ANYCONTENT')]
class SortController extends AbstractAnyContentBackendController
{
    #[Route('/xxxxx', 'anycontent_sort')]
    public function start(): Response
    {
    }
}
