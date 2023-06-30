<?php

namespace AnyContent\Backend\Modules\Listing\Controller;

use AnyContent\Backend\Controller\AbstractAnyContentBackendController;
use AnyContent\Backend\Modules\Listing\ContentViews\DefaultContentView;
use AnyContent\Backend\Services\ContentViewsManager;
use AnyContent\Backend\Services\ContextManager;
use AnyContent\Backend\Services\FormManager;
use AnyContent\Backend\Services\MenuManager;
use AnyContent\Backend\Services\RepositoryManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ListingController extends AbstractAnyContentBackendController
{
    public function __construct(
        protected RepositoryManager $repositoryManager,
        protected ContextManager $contextManager,
        protected FormManager $formManager,
        protected MenuManager $menuManager,
        protected EventDispatcherInterface $dispatcher,
        private DefaultContentView $defaultContentView
    ) {
        parent::__construct($this->repositoryManager, $this->contextManager, $this->formManager, $this->menuManager, $this->dispatcher);
    }

    #[Route('/content/list/{contentTypeAccessHash}/{nr}/{page}/{workspace}/{language}', 'anycontent_records', methods: ['GET'])]
    public function listRecords(#[CurrentUser] ?UserInterface $user, Request $request, ContentViewsManager $contentViewsManager, $contentTypeAccessHash, $page = 1, $workspace = null, $language = null, $nr = 0)
    {
        $vars = [];

        $vars['menu_mainmenu'] = $this->menuManager->renderMainMenu();

        $repository = $this->repositoryManager->getRepositoryByContentTypeAccessHash($contentTypeAccessHash);
        $vars['repository']          = $repository;

        $contentTypeDefinition = $repository->getContentTypeDefinition();

        $this->contextManager->setCurrentRepository($repository);
        $this->contextManager->setCurrentContentType($contentTypeDefinition);
        $this->contextManager->setCurrentListingPage($page);
        $vars['definition'] = $contentTypeDefinition;

        if ($workspace != null && $contentTypeDefinition->hasWorkspace($workspace)) {
            $this->contextManager->setCurrentWorkspace($workspace);
        }
        if ($language != null && $contentTypeDefinition->hasLanguage($language)) {
            $this->contextManager->setCurrentLanguage($language);
        }

        // set workspace, language and timeshift of repository object to make sure content views are accessing the right content dimensions

        $repository->selectWorkspace($this->contextManager->getCurrentWorkspace());
        $repository->selectLanguage($this->contextManager->getCurrentLanguage());
        $repository->setTimeShift($this->contextManager->getCurrentTimeShift());

        // Jump to record if existing id has been entered into the search field

        if ($request->query->has('q')) {
            if (is_numeric($request->query->get('q'))) {
                $recordId = (int)$request->query->get('q');
                if ($repository->getRecord($recordId)) {
                    $this->contextManager->setCurrentSearchTerm('');

                    return new RedirectResponse($this->generateUrl('anycontent_record_edit', ['contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId, 'workspace' => $this->contextManager->getCurrentWorkspace(), 'language' => $this->contextManager->getCurrentLanguage()]), 303);
                }
            }

            $this->contextManager->setCurrentSearchTerm($request->query->get('q'));
        }

        // store sorting order
        if ($request->query->has('s')) {
            $this->contextManager->setCurrentSortingOrder($request->query->get('s'));
        }

        // store items per page
        if ($request->query->has('c')) {
            $this->contextManager->setCurrentItemsPerPage($request->query->get('c'));
        }

        // Determine Content View

        $contentViews = $contentViewsManager->getContentViews($repository, $contentTypeDefinition, $contentTypeAccessHash);

        if ((int)($nr) == 0) {
            $nr = $this->contextManager->getCurrentContentViewNr();
        }

        if (count($contentViews) == 0) {
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
        if ($request->query->has('q') && !$currentContentView->doesProcessSearch()) {
            $error = true;
            foreach ($contentViews as $nr => $currentContentView) {
                if ($currentContentView->doesProcessSearch()) {
                    $error = false;
                    break;
                }
            }
            if ($error) {
                $this->contextManager->addAlertMessage('Configuration error. Could not find content view, which is able to process search queries.');
            }
        }

        $vars['contentView']          = $currentContentView;
        $vars['currentContentViewNr'] = $nr;
        $this->contextManager->setCurrentContentViewNr($nr);

        $this->addRepositoryLinks($vars, $repository, $page);

        $buttons = $this->getButtons($contentTypeAccessHash, $contentTypeDefinition);
        $vars['buttons'] = $this->menuManager->renderButtonGroup($buttons);

        $vars = $currentContentView->apply($this->contextManager, $vars);

        return $this->render($currentContentView->getTemplate(), $vars);
    }
}