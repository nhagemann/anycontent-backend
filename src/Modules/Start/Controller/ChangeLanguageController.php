<?php

namespace AnyContent\Backend\Modules\Start\Controller;

use AnyContent\Backend\Controller\AbstractAnyContentBackendController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ANYCONTENT')]
class ChangeLanguageController extends AbstractAnyContentBackendController
{
    #[Route('/change-language/list/{contentTypeAccessHash}/page/{page}', 'anycontent_records_change_language', methods: ['POST'])]
    public function changeLanguageListRecords(Request $request, $contentTypeAccessHash, $page = 1)
    {
        $this->contextManager->setCurrentLanguage($request->get('language'));

        return $this->redirect($this->generateUrl('anycontent_records', ['contentTypeAccessHash' => $contentTypeAccessHash, 'page' => $page]), 303);
    }

    #[Route('/change-language/edit-record/{contentTypeAccessHash}/{recordId}', 'anycontent_record_edit_change_language', methods: ['POST'])]
    public function changeLanguageEditRecord(Request $request, $contentTypeAccessHash, $recordId)
    {
        $this->contextManager->setCurrentLanguage($request->get('language'));

        return $this->redirect($this->generateUrl('anycontent_record_edit', ['contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId, '']), 303);
    }

    #[Route('/change-language/add-record/{contentTypeAccessHash}', 'anycontent_record_add_change_language', methods: ['POST'])]
    public function changeLanguageAddRecord(Request $request, $contentTypeAccessHash)
    {
        $this->contextManager->setCurrentLanguage($request->get('language'));

        return $this->redirect($this->generateUrl('anycontent_record_add', ['contentTypeAccessHash' => $contentTypeAccessHash]), 303);
    }

    #[Route('/change-language/content/sort/{contentTypeAccessHash}', 'anycontent_records_sort_change_language', methods: ['POST'])]
    public function changeLanguageSortRecords(Request $request, $contentTypeAccessHash)
    {
        $this->contextManager->setCurrentLanguage($request->get('language'));

        return $this->redirect($this->generateUrl('anycontent_records_sort', ['contentTypeAccessHash' => $contentTypeAccessHash]), 303);
    }

    #[Route('/change-language/edit-config/{configTypeAccessHash}', 'anycontent_config_edit_change_language', methods: ['POST'])]
    public function changeLanguageEditConfig(Request $request, $configTypeAccessHash)
    {
        $this->contextManager->setCurrentLanguage($request->get('language'));

        return $this->redirect($this->generateUrl('anycontent_config_edit', ['configTypeAccessHash' => $configTypeAccessHash]), 303);
    }
}
