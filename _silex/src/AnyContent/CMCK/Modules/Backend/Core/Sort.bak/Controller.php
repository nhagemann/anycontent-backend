<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Sort;

use AnyContent\Client\ContentFilter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

use AnyContent\Client\Repository;
use AnyContent\Client\Record;
use AnyContent\Client\UserInfo;

class Controller
{

    public static function sortRecords(Application $app, Request $request, $contentTypeAccessHash)
    {
        $vars = array();

        /** @var Repository $repository */
        $repository = $app['repos']->getRepositoryByContentTypeAccessHash($contentTypeAccessHash);
        $app['context']->setCurrentRepository($repository);

        $vars['repository']          = $repository;
        $repositoryAccessHash        = $app['repos']->getRepositoryAccessHash($repository);
        $vars['links']['repository'] = $app['url_generator']->generate('indexRepository', array( 'repositoryAccessHash' => $repositoryAccessHash ));

        $contentTypeDefinition = $repository->getContentTypeDefinition();
        $app['context']->setCurrentContentType($contentTypeDefinition);
        $vars['definition'] = $contentTypeDefinition;

        $vars['menu_mainmenu'] = $app['menus']->renderMainMenu();

        $buttons         = array();
        $buttons[100]    = array( 'label' => 'List Records', 'url' => $app['url_generator']->generate('listRecords', array( 'contentTypeAccessHash' => $contentTypeAccessHash, 'page' => $app['context']->getCurrentListingPage() )), 'glyphicon' => 'glyphicon-list' );
        $vars['buttons'] = $app['menus']->renderButtonGroup($buttons);

        $vars['links']['search'] = $app['url_generator']->generate('listRecords', array( 'contentTypeAccessHash' => $contentTypeAccessHash, 'page' => 1, 's' => 'name' ));

        // context links
        $vars['links']['timeshift']  = $app['url_generator']->generate('timeShiftSortRecords', array( 'contentTypeAccessHash' => $contentTypeAccessHash, 'page' => 1 ));
        $vars['links']['workspaces'] = $app['url_generator']->generate('changeWorkspaceSortRecords', array( 'contentTypeAccessHash' => $contentTypeAccessHash, 'page' => 1 ));
        $vars['links']['languages']  = $app['url_generator']->generate('changeLanguageSortRecords', array( 'contentTypeAccessHash' => $contentTypeAccessHash, 'page' => 1 ));
        $vars['links']['reset']      = $app['url_generator']->generate('listRecordsReset', array( 'contentTypeAccessHash' => $contentTypeAccessHash ));

        $vars['links']['sort'] = $app['url_generator']->generate('postSortRecords', array( 'contentTypeAccessHash' => $contentTypeAccessHash ));


        // set workspace, language and timeshift of repository object to make sure content views are accessing the right content dimensions

        $repository->selectWorkspace($app['context']->getCurrentWorkspace());
        $repository->selectLanguage($app['context']->getCurrentLanguage());
        $repository->setTimeshift($app['context']->getCurrentTimeShift());

        $vars['records_left']  = $repository->getSortedRecords(0);
        $vars['records_right'] = array_diff_key($repository->getRecords(),$vars['records_left']);

        return $app->renderPage('sort-tree.twig', $vars);
    }


    public static function postSortRecords(Application $app, Request $request, $contentTypeAccessHash)
    {
        $hidden = $request->get('$hidden');

        /** @var Repository $repository */
        $repository = $app['repos']->getRepositoryByContentTypeAccessHash($contentTypeAccessHash);

        $contentTypeDefinition = $repository->getContentTypeDefinition();
        $app['context']->setCurrentContentType($contentTypeDefinition);
        $app['context']->setCurrentWorkspace($hidden['workspace']);
        $app['context']->setCurrentLanguage($hidden['language']);

        // set workspace, language and timeshift of repository object to make sure content views are accessing the right content dimensions

        $repository->selectWorkspace($app['context']->getCurrentWorkspace());
        $repository->selectLanguage($app['context']->getCurrentLanguage());
        $repository->setTimeshift($app['context']->getCurrentTimeShift());

        if ($request->request->has('list'))
        {
            $app['context']->resetTimeShift();

            $list = [];

            foreach (json_decode($request->get('list'), true) as $item)
            {
                $list[$item['id']]=$item['parent_id'];
            }

            $repository->sortRecords($list);

            return new RedirectResponse($app['url_generator']->generate('sortRecords', array( 'contentTypeAccessHash' => $contentTypeAccessHash )), 303);
        }
        else
        {
            return $app['layout']->render('error.twig');
        }

    }

}