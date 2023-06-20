<?php

namespace AnyContent\Backend\Modules\Start\Controller;

use AnyContent\Backend\Controller\AbstractAnyContentBackendController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ANYCONTENT')]
class ChangeLanguageController extends AbstractAnyContentBackendController
{
    /**

    $app
    ->post('/change-language/content/edit/{contentTypeAccessHash}/{recordId}', 'AnyContent\CMCK\Modules\Backend\Core\WorkspacesLanguages\Controller::changeLanguageEditRecord')
    ->bind('changeLanguageEditRecord');
    $app
    ->post('/change-language/content/add/{contentTypeAccessHash}', 'AnyContent\CMCK\Modules\Backend\Core\WorkspacesLanguages\Controller::changeLanguageAddRecord')
    ->bind('changeLanguageAddRecord');
    $app
    ->post('/change-language/content/sort/{contentTypeAccessHash}', 'AnyContent\CMCK\Modules\Backend\Core\WorkspacesLanguages\Controller::changeLanguageSortRecords')
    ->bind('changeLanguageSortRecords');
    $app
    ->post('/change-language/config/edit/{configTypeAccessHash}', 'AnyContent\CMCK\Modules\Backend\Core\WorkspacesLanguages\Controller::changeLanguageEditConfig')
    ->bind('changeLanguageEditConfig');
     */
    #[Route('/change-language/list/{contentTypeAccessHash}/page/{page}', 'anycontent_records_change_language', methods: ['POST'])]
    public function changeLanguageListRecords(Request $request, $contentTypeAccessHash, $page = 1)
    {
        $this->contextManager->setCurrentLanguage($request->get('language'));

        return $this->redirect($this->generateUrl('anycontent_records', array('contentTypeAccessHash' => $contentTypeAccessHash, 'page' => $page)), 303);
    }

    #[Route('/change-language/edit-record/{contentTypeAccessHash}/{recordId]', 'anycontent_record_edit_change_language', methods: ['POST'])]
    public function changeLanguageEditRecord(Request $request, $contentTypeAccessHash, $recordId)
    {
        $this->contextManager->setCurrentLanguage($request->get('language'));

        return $this->redirect($this->generateUrl('anycontent_record_edit', array('contentTypeAccessHash' => $contentTypeAccessHash, 'record' => $recordId)), 303);
    }

    public static function changeLanguageAddRecord(Application $app, Request $request, $contentTypeAccessHash)
    {
        $app['context']->setCurrentLanguage($request->get('language'));
        ;

        return $app->redirect($app['url_generator']->generate('addRecord', array('contentTypeAccessHash' => $contentTypeAccessHash)), 303);
    }

    public static function changeLanguageSortRecords(Application $app, Request $request, $contentTypeAccessHash)
    {
        $app['context']->setCurrentLanguage($request->get('language'));
        ;

        return $app->redirect($app['url_generator']->generate('sortRecords', array('contentTypeAccessHash' => $contentTypeAccessHash)), 303);
    }

    public static function changeLanguageEditConfig(Application $app, Request $request, $configTypeAccessHash)
    {
        $app['context']->setCurrentLanguage($request->get('language'));
        ;

        return $app->redirect($app['url_generator']->generate('editConfig', array('configTypeAccessHash' => $configTypeAccessHash)), 303);
    }
}
