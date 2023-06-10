<?php

namespace AnyContent\Backend\Modules\Start\Controller;

use AnyContent\Backend\Controller\AbstractAnyContentBackendController;
use AnyContent\Backend\Services\ContextManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ANYCONTENT')]
class ChangeWorkspaceController extends AbstractAnyContentBackendController
{

    /**
     *       $app
    ->post('/change-workspace/content/list/{contentTypeAccessHash}/page/{page}', 'AnyContent\CMCK\Modules\Backend\Core\WorkspacesLanguages\Controller::changeWorkspaceListRecords')
    ->bind('changeWorkspaceListRecords');
    $app
    ->post('/change-workspace/content/edit/{contentTypeAccessHash}/{recordId}', 'AnyContent\CMCK\Modules\Backend\Core\WorkspacesLanguages\Controller::changeWorkspaceEditRecord')
    ->bind('changeWorkspaceEditRecord');
    $app
    ->post('/change-workspace/content/add/{contentTypeAccessHash}', 'AnyContent\CMCK\Modules\Backend\Core\WorkspacesLanguages\Controller::changeWorkspaceAddRecord')
    ->bind('changeWorkspaceAddRecord');
    $app
    ->post('/change-workspace/content/sort/{contentTypeAccessHash}', 'AnyContent\CMCK\Modules\Backend\Core\WorkspacesLanguages\Controller::changeWorkspaceSortRecords')
    ->bind('changeWorkspaceSortRecords');
    $app
    ->post('/change-workspace/config/edit/{configTypeAccessHash}', 'AnyContent\CMCK\Modules\Backend\Core\WorkspacesLanguages\Controller::changeWorkspaceEditConfig')
    ->bind('changeWorkspaceEditConfig');
    $app
    ->post('/change-language/content/list/{contentTypeAccessHash}/page/{page}', 'AnyContent\CMCK\Modules\Backend\Core\WorkspacesLanguages\Controller::changeLanguageListRecords')
    ->bind('changeLanguageListRecords');
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

    #[Route('/change-workspace/list/{contentTypeAccessHash}/page/{page}','anycontent_records_change_workspace', methods: ['POST'])]
    public function changeWorkspaceListRecords(Request $request, $contentTypeAccessHash, $page = 1)
    {
        $this->contextManager->setCurrentWorkspace($request->get('workspace'));

        return $this->redirect($this->generateUrl('anycontent_records', array( 'contentTypeAccessHash' => $contentTypeAccessHash, 'page' => $page )),303);
    }


    #[Route('/change-workspace/edit-record/{contentTypeAccessHash}/{recordId]','anycontent_record_edit_change_workspace', methods: ['POST'])]
    public function changeLanguageEditRecord(Request $request, $contentTypeAccessHash, $recordId)
    {
        $this->contextManager->setCurrentWorkspace($request->get('workspace'));

        return $this->redirect($this->generateUrl('anycontent_record_edit', array( 'contentTypeAccessHash' => $contentTypeAccessHash, 'record' => $recordId )),303);
    }


    public static function changeWorkspaceAddRecord(Application $app, Request $request, $contentTypeAccessHash)
    {
        $app['context']->setCurrentWorkspace($request->get('workspace'));

        return $app->redirect($app['url_generator']->generate('addRecord', array( 'contentTypeAccessHash' => $contentTypeAccessHash )),303);
    }


    public static function changeWorkspaceSortRecords(Application $app, Request $request, $contentTypeAccessHash)
    {
        $app['context']->setCurrentWorkspace($request->get('workspace'));

        return $app->redirect($app['url_generator']->generate('sortRecords', array( 'contentTypeAccessHash' => $contentTypeAccessHash)),303);
    }

    public static function changeWorkspaceEditConfig(Application $app, Request $request, $configTypeAccessHash)
    {
        $app['context']->setCurrentWorkspace($request->get('workspace'));;

        return $app->redirect($app['url_generator']->generate('editConfig', array( 'configTypeAccessHash' => $configTypeAccessHash)),303);
    }

}