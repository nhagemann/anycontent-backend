<?php

namespace AnyContent\CMCK\Modules\Backend\Core\WorkspacesLanguages;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

use CMDL\ContentTypeDefinition;

class Module extends \AnyContent\CMCK\Modules\Backend\Core\Core\Module
{

    public function init(Application $app, $options = array())
    {
        parent::init($app, $options);

        $app
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

    }


    public function preRender(Application $app)
    {
        $workspaces = $app['layout']->getVar('workspaces', array());

        /** @var ContentTypeDefinition $contentType */
        $contentTypeDefinition = $app['context']->getCurrentContentType();

        if ($contentTypeDefinition)
        {
            $workspaces['active'] = true;
            $workspaces['list']   = $contentTypeDefinition->getWorkspaces();

            if (count($contentTypeDefinition->getWorkspaces()) < 2)
            {
                $workspaces['active'] = false;
            }
        }
        else
        {
            $workspaces['active'] = false;
        }

        $workspaces['current'] = $app['context']->getCurrentWorkspace();
        $workspaces['currentName'] = $app['context']->getCurrentWorkspaceName();

        $app['layout']->addVar('workspaces', $workspaces);



        // do the same for current language

        $languages = $app['layout']->getVar('languages', array());

        /** @var ContentTypeDefinition $contentType */
        $contentTypeDefinition = $app['context']->getCurrentContentType();

        if ($contentTypeDefinition)
        {
            $languages['active'] = true;
            $languages['list']   = $contentTypeDefinition->getLanguages();

            if (!$contentTypeDefinition->hasLanguages())
            {
                $languages['active'] = false;
            }
        }
        else
        {
            $languages['active'] = false;
        }

        $languages['current'] = $app['context']->getCurrentLanguage();
        $languages['currentName'] = $app['context']->getCurrentLanguageName();

        $app['layout']->addVar('languages', $languages);




    }
}