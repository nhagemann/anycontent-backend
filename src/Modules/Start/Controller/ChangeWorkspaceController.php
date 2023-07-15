<?php

namespace AnyContent\Backend\Modules\Start\Controller;

use AnyContent\Backend\Controller\AbstractAnyContentBackendController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ANYCONTENT')]
class ChangeWorkspaceController extends AbstractAnyContentBackendController
{
     #[Route('/change-workspace/list/{contentTypeAccessHash}/page/{page}', 'anycontent_records_change_workspace', methods: ['POST'])]
    public function changeWorkspaceListRecords(Request $request, $contentTypeAccessHash, $page = 1)
    {
        $this->contextManager->setCurrentWorkspace($request->get('workspace'));

        return $this->redirect($this->generateUrl('anycontent_records', ['contentTypeAccessHash' => $contentTypeAccessHash, 'page' => $page]), 303);
    }

    #[Route('/change-workspace/edit-record/{contentTypeAccessHash}/{recordId}', 'anycontent_record_edit_change_workspace', methods: ['POST'])]
    public function changeWorkspaceEditRecord(Request $request, $contentTypeAccessHash, $recordId)
    {
        $this->contextManager->setCurrentWorkspace($request->get('workspace'));

        return $this->redirect($this->generateUrl('anycontent_record_edit', ['contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId]), 303);
    }

    #[Route('/change-workspace/add-record/{contentTypeAccessHash}', 'anycontent_record_add_change_workspace', methods: ['POST'])]
    public function changeWorkspaceAddRecord(Request $request, $contentTypeAccessHash)
    {
        $this->contextManager->setCurrentWorkspace($request->get('workspace'));

        return $this->redirect($this->generateUrl('anycontent_record_add', ['contentTypeAccessHash' => $contentTypeAccessHash]), 303);
    }

    #[Route('/change-workspace/content/sort/{contentTypeAccessHash}', 'anycontent_records_sort_change_workspace', methods: ['POST'])]
    public function changeWorkspaceSortRecords(Request $request, $contentTypeAccessHash)
    {
        $this->contextManager->setCurrentWorkspace($request->get('workspace'));

        return $this->redirect($this->generateUrl('anycontent_records_sort', ['contentTypeAccessHash' => $contentTypeAccessHash]), 303);
    }

    #[Route('/change-workspace/edit-config/{configTypeAccessHash}', 'anycontent_config_edit_change_workspace', methods: ['POST'])]
    public function changeWorkspaceEditConfig(Request $request, $configTypeAccessHash)
    {
        $this->contextManager->setCurrentWorkspace($request->get('workspace'));

        return $this->redirect($this->generateUrl('anycontent_config_edit', ['configTypeAccessHash' => $configTypeAccessHash]), 303);
    }
}
