<?php

namespace AnyContent\Backend\Modules\Listing\Controller;

use AnyContent\Backend\Controller\AbstractAnyContentBackendController;

use AnyContent\Backend\Modules\Listing\ContentViews\DefaultContentView;
use AnyContent\Backend\Services\ContentViewsManager;
use AnyContent\Backend\Services\ContextManager;
use AnyContent\Backend\Services\MenuManager;
use AnyContent\Backend\Services\RepositoryManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use AnyContent\Client\Repository;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ListingController extends AbstractAnyContentBackendController
{
    public function __construct(
        protected RepositoryManager $repositoryManager,
        protected ContextManager $contextManager,
        protected MenuManager $menuManager,
        private DefaultContentView $defaultContentView

    )
    {
        parent::__construct($this->repositoryManager,$this->contextManager, $this->menuManager);
    }

    /**
     *$app->get('/content/list/{contentTypeAccessHash}/{nr}/page/{page}/{workspace}/{language}', 'AnyContent\CMCK\Modules\Backend\Core\Listing\Controller::listRecords')
    ->bind('listRecords')->value('page', 1)->value('workspace', null)->value('language', null)->value('nr', 0);
    $app->get('/content/list/{contentTypeAccessHash}/{workspace}/{language}', 'AnyContent\CMCK\Modules\Backend\Core\Listing\Controller::listRecords')
    ->bind('listRecordsReset')->value('workspace', null)->value('language', null)->value('nr', 1);

    $app['contentViews']->registerContentView('default', 'AnyContent\CMCK\Modules\Backend\Core\Listing\ContentViewDefault');
     */

    #[Route('/content/list/{contentTypeAccessHash}/{nr}/page/{page}/{workspace}/{language}','anycontent_records')]
    #[Route('/content/list/{contentTypeAccessHash}/{nr}','anycontent_records_reset')]
    //#[Route('/content/list/{contentTypeAccessHash}','anycontent_list_records')]
    public function listRecords(#[CurrentUser] ?UserInterface $user, Request $request, ContentViewsManager $contentViewsManager, $contentTypeAccessHash, $page = 1, $workspace = null, $language = null, $nr = 0)
    {

        $vars = array();

        $vars['menu_mainmenu'] = $this->menuManager->renderMainMenu();

        /** @var Repository $repository */
        $repository = $this->repositoryManager->getRepositoryByContentTypeAccessHash($contentTypeAccessHash);

        $vars['repository']          = $repository;
        $repositoryAccessHash        = $this->repositoryManager->getRepositoryAccessHash($repository);
        $vars['links']['repository'] = $this->generateUrl('anycontent_repository', array( 'repositoryAccessHash' => $repositoryAccessHash ));
        $vars['links']['self']       = $this->generateUrl('anycontent_records', array( 'contentTypeAccessHash' => $contentTypeAccessHash ));

        $contentTypeDefinition = $repository->getContentTypeDefinition();

        $this->contextManager->setCurrentRepository($repository);
        $this->contextManager->setCurrentContentType($contentTypeDefinition);
        $this->contextManager->setCurrentListingPage($page);
        $vars['definition'] = $contentTypeDefinition;

        if ($workspace != null && $contentTypeDefinition->hasWorkspace($workspace))
        {
            $this->contextManager->setCurrentWorkspace($workspace);
        }
        if ($language != null && $contentTypeDefinition->hasLanguage($language))
        {
            $this->contextManager->setCurrentLanguage($language);
        }

        // set workspace, language and timeshift of repository object to make sure content views are accessing the right content dimensions

        $repository->selectWorkspace($this->contextManager->getCurrentWorkspace());
        $repository->selectLanguage($this->contextManager->getCurrentLanguage());
        $repository->setTimeShift($this->contextManager->getCurrentTimeShift());


        // Jump to record if existing id has been entered into the search field

        if ($request->query->has('q'))
        {

            if (is_numeric($request->query->get('q')))
            {
                $recordId = (int)$request->query->get('q');
                if ($repository->getRecord($recordId))
                {
                    $this->contextManager->setCurrentSearchTerm('');

                    return new RedirectResponse($this->generateUrl('anycontent_edit', array( 'contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId )), 303);
                }
            }

            $this->contextManager->setCurrentSearchTerm($request->query->get('q'));


        }

        // store sorting order
        if ($request->query->has('s'))
        {
            $this->contextManager->setCurrentSortingOrder($request->query->get('s'));
        }

        // store items per page
        if ($request->query->has('c'))
        {
            $this->contextManager->setCurrentItemsPerPage($request->query->get('c'));
        }

        // Determine Content View

        $contentViews = $contentViewsManager->getContentViews($repository, $contentTypeDefinition, $contentTypeAccessHash);

        if ((int)($nr) == 0)
        {
            $nr = $this->contextManager->getCurrentContentViewNr();
        }

        if (count($contentViews) == 0)
        {

            $contentViews[1] = $this->defaultContentView;
            $currentContentView = $contentViews[1];

                //new DefaultContentView(1, $repository, $contentTypeDefinition, $contentTypeAccessHash);
        }
        $vars['contentViews'] = $contentViews;

//        $currentContentView = $contentViewsManager->getContentView($repository, $contentTypeDefinition, $contentTypeAccessHash, $nr);
//
//        if (!$currentContentView)
//        {
//            $currentContentView = reset($contentViews);
//            $nr                 = key($contentViews);
//        }

        // Switch to first content view which support search queries
        if ($request->query->has('q') && !$currentContentView->doesProcessSearch())
        {
            $error = true;
            foreach ($contentViews as $nr => $currentContentView)
            {
                if ($currentContentView->doesProcessSearch())
                {
                    $error = false;
                    break;
                }
            }
            if ($error)
            {
                $this->contextManager->addAlertMessage('Configuration error. Could not find content view, which is able to process search queries.');
            }
        }

        $vars['contentView']          = $currentContentView;
        $vars['currentContentViewNr'] = $nr;
        $this->contextManager->setCurrentContentViewNr($nr);

        // sorting links

        $vars['links']['search']         = $this->generateUrl('anycontent_records', array( 'contentTypeAccessHash' => $contentTypeAccessHash, 'page' => 1, 's' => 'name', 'workspace' => $this->contextManager->getCurrentWorkspace(), 'language' => $this->contextManager->getCurrentLanguage() ));
        $vars['links']['closeSearchBox'] = $this->generateUrl('anycontent_records', array( 'contentTypeAccessHash' => $contentTypeAccessHash, 'page' => 1, 'q' => '' ));

        // context links
        $vars['links']['timeshift']  = '';//$this->generateUrl('timeShiftListRecords', array( 'contentTypeAccessHash' => $contentTypeAccessHash, 'page' => $page ));
        $vars['links']['workspaces'] = '';//$this->generateUrl('changeWorkspaceListRecords', array( 'contentTypeAccessHash' => $contentTypeAccessHash, 'page' => $page ));
        $vars['links']['languages']  = '';//$this->generateUrl('changeLanguageListRecords', array( 'contentTypeAccessHash' => $contentTypeAccessHash, 'page' => $page ));
        $vars['links']['reset']      = '';//$this->generateUrl('listRecordsReset', array( 'contentTypeAccessHash' => $contentTypeAccessHash ));

        $buttons      = array();
        $buttons[100] = array( 'label' => 'List Records', 'url' => $this->generateUrl('anycontent_records_reset', array( 'contentTypeAccessHash' => $contentTypeAccessHash, 'workspace' => $this->contextManager->getCurrentWorkspace(), 'language' => $this->contextManager->getCurrentLanguage() )), 'glyphicon' => 'glyphicon-list' );

        /*
        //if ($contentTypeDefinition->isSortable() && $user->canDo('sort', $repository, $contentTypeDefinition))
        if ($contentTypeDefinition->isSortable())
        {
            $buttons[200] = array( 'label' => 'Sort Records', 'url' => $this->generateUrl('sortRecords', array( 'contentTypeAccessHash' => $contentTypeAccessHash, 'workspace' => $this->contextManager->getCurrentWorkspace(), 'language' => $this->contextManager->getCurrentLanguage() )), 'glyphicon' => 'glyphicon-move' );
        }
        //if ($user->canDo('add', $repository, $contentTypeDefinition))
        //{
            $buttons[300] = array( 'label' => 'Add Record', 'url' => $this->generateUrl('addRecord', array( 'contentTypeAccessHash' => $contentTypeAccessHash, 'workspace' => $this->contextManager->getCurrentWorkspace(), 'language' => $this->contextManager->getCurrentLanguage() )), 'glyphicon' => 'glyphicon-plus' );
        //}
        //if ($user->canDo('export', $repository, $contentTypeDefinition))
        //{
            $buttons[400] = array( 'label' => 'Export Records', 'url' => $this->generateUrl('exportRecords', array( 'contentTypeAccessHash' => $contentTypeAccessHash )), 'glyphicon' => 'glyphicon-cloud-download', 'id' => 'listing_button_export' );
        //}
        //if ($user->canDo('import', $repository, $contentTypeDefinition))
        //{
            $buttons[500] = array( 'label' => 'Import Records', 'url' => $this->generateUrl('importRecords', array( 'contentTypeAccessHash' => $contentTypeAccessHash )), 'glyphicon' => 'glyphicon-cloud-upload', 'id' => 'listing_button_import' );
        //}
        */
        $vars['buttons'] = $this->menuManager->renderButtonGroup($buttons);

        $vars = $currentContentView->apply($this->contextManager, $vars);

        return $this->render($currentContentView->getTemplate(), $vars);
    }

}
