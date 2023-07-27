<?php

namespace AnyContent\Backend\Modules\Start\Controller;

use AnyContent\Backend\Controller\AbstractAnyContentBackendController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ANYCONTENT')]
class ChangeLanguageController extends AbstractAnyContentBackendController
{
    #[Route('/change-language/list/{contentTypeAccessHash}/page/{page}', name:'anycontent_records_change_language', methods: ['POST'])]
    public function changeLanguageListRecords(Request $request, $contentTypeAccessHash, $page = 1)
    {
        $this->contextManager->setCurrentLanguage($request->get('language'));

        return $this->redirect($this->generateUrl('anycontent_records', ['contentTypeAccessHash' => $contentTypeAccessHash, 'page' => $page]), 303);
    }

    #[Route('/change-language/edit-record/{contentTypeAccessHash}/{recordId}', name:'anycontent_record_edit_change_language', methods: ['POST'])]
    public function changeLanguageEditRecord(Request $request, $contentTypeAccessHash, $recordId)
    {
        $this->contextManager->setCurrentLanguage($request->get('language'));

        return $this->redirect($this->generateUrl('anycontent_record_edit', ['contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId, '']), 303);
    }

    #[Route('/change-language/add-record/{contentTypeAccessHash}', name:'anycontent_record_add_change_language', methods: ['POST'])]
    public function changeLanguageAddRecord(Request $request, $contentTypeAccessHash)
    {
        $this->contextManager->setCurrentLanguage($request->get('language'));

        return $this->redirect($this->generateUrl('anycontent_record_add', ['contentTypeAccessHash' => $contentTypeAccessHash]), 303);
    }

    #[Route('/change-language/content/sort/{contentTypeAccessHash}', name:'anycontent_records_sort_change_language', methods: ['POST'])]
    public function changeLanguageSortRecords(Request $request, $contentTypeAccessHash)
    {
        $this->contextManager->setCurrentLanguage($request->get('language'));

        return $this->redirect($this->generateUrl('anycontent_records_sort', ['contentTypeAccessHash' => $contentTypeAccessHash]), 303);
    }

    #[Route('/change-language/edit-config/{configTypeAccessHash}', name:'anycontent_config_edit_change_language', methods: ['POST'])]
    public function changeLanguageEditConfig(Request $request, $configTypeAccessHash)
    {
        $this->contextManager->setCurrentLanguage($request->get('language'));

        return $this->redirect($this->generateUrl('anycontent_config_edit', ['configTypeAccessHash' => $configTypeAccessHash]), 303);
    }

    #[Route('/change-language/revisions/content/{contentTypeAccessHash}/{recordId}', name:'anycontent_revisions_content_change_language', methods: ['POST'])]
    public function changeLanguageRecordRevisions(Request $request, $contentTypeAccessHash, $recordId)
    {
        $this->contextManager->setCurrentLanguage($request->get('language'));

        return $this->redirect($this->generateUrl('anycontent_record_revisions', ['contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId, 'workspace' => $this->contextManager->getCurrentWorkspace(), 'language' => $this->contextManager->getCurrentLanguage()]), 303);
    }

    #[Route('/change-language/revisions/config/{configTypeAccessHash}', name:'anycontent_revisions_config_change_language', methods: ['POST'])]
    public function changeLanguageConfigRevisions(Request $request, $configTypeAccessHash)
    {
        $this->contextManager->setCurrentLanguage($request->get('language'));

        return $this->redirect($this->generateUrl('anycontent_config_revisions', ['configTypeAccessHash' => $configTypeAccessHash, 'workspace' => $this->contextManager->getCurrentWorkspace(), 'language' => $this->contextManager->getCurrentLanguage()]), 303);
    }
}
