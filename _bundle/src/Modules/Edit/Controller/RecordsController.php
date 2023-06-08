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
class RecordsController extends AbstractAnyContentBackendController
{

    #[Route('/xxx','anycontent_record_add')]
    #[Route('/xxx','anycontent_record_edit')]
    #[Route('/xxx','anycontent_record_delete')]
    public function xxx(): Response
    {

    }


}