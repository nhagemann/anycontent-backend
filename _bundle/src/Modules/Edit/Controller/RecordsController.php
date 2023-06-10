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

    #[Route('/xxx','anycontent_record_delete')]
    public function xxx(): Response
    {

    }

    public static function addRecord(Application $app, $contentTypeAccessHash, $recordId = null)
    {
        /** @var UserManager $user */
        $user = $app['user'];

        /** @var FormManager $formManager */
        $formManager = $app['form'];

        $vars = array();

        $vars['menu_mainmenu'] = $app['menus']->renderMainMenu();

        /** @var Repository $repository */
        $repository = $app['repos']->getRepositoryByContentTypeAccessHash($contentTypeAccessHash);

        if ($repository) {
            $vars['repository'] = $repository;
            $repositoryAccessHash = $app['repos']->getRepositoryAccessHash($repository);
            $vars['links']['repository'] = $this->generateUrl(
                'indexRepository',
                array('repositoryAccessHash' => $repositoryAccessHash)
            );
            $vars['links']['listRecords'] = $this->generateUrl(
                'listRecords',
                array(
                    'contentTypeAccessHash' => $contentTypeAccessHash,
                    'page' => 1,
                    'workspace' => $app['context']->getCurrentWorkspace(),
                    'language' => $app['context']->getCurrentLanguage(),
                )
            );

            $app['context']->setCurrentRepository($repository);
            $app['context']->setCurrentContentType($repository->getContentTypeDefinition());

            $formManager->setDataTypeDefinition($repository->getContentTypeDefinition());

            $vars['record'] = false;

            /** @var ContentTypeDefinition $contentTypeDefinition */
            $contentTypeDefinition = $repository->getContentTypeDefinition();

            $vars['definition'] = $contentTypeDefinition;

            if ($user->canDo(
                'add',
                $repository,
                $contentTypeDefinition
            )
            ) {
                $vars['links']['edit'] = true;

                /* @var ViewDefinition */

                $viewDefinition = $contentTypeDefinition->getInsertViewDefinition();

                $properties = array();
                foreach ($viewDefinition->getFormElementDefinitions() as $formElementDefinition) {
                    $properties[$formElementDefinition->getName()] = $formElementDefinition->getDefaultValue();
                }

                $vars['form'] = $formManager->renderFormElements(
                    'form_edit',
                    $viewDefinition->getFormElementDefinitions(),
                    $properties,
                    array(
                        'workspace' => $app['context']->getCurrentWorkspace(),
                        'language' => $app['context']->getCurrentLanguage(),
                    )
                );

                $buttons = array();
                $buttons[] = array(
                    'label' => 'List Records',
                    'url' => $this->generateUrl(
                        'listRecords',
                        array(
                            'contentTypeAccessHash' => $contentTypeAccessHash,
                            'page' => 1,
                            'workspace' => $app['context']->getCurrentWorkspace(),
                            'language' => $app['context']->getCurrentLanguage(),
                        )
                    ),
                    'glyphicon' => 'glyphicon-list',
                );
                if ($contentTypeDefinition->isSortable()) {
                    $buttons[] = array(
                        'label' => 'Sort Records',
                        'url' => $this->generateUrl(
                            'sortRecords',
                            array(
                                'contentTypeAccessHash' => $contentTypeAccessHash,
                                'workspace' => $app['context']->getCurrentWorkspace(),
                                'language' => $app['context']->getCurrentLanguage(),
                            )
                        ),
                        'glyphicon' => 'glyphicon-move',
                    );
                }
                $buttons[] = array(
                    'label' => 'Add Record',
                    'url' => $this->generateUrl(
                        'addRecord',
                        array('contentTypeAccessHash' => $contentTypeAccessHash)
                    ),
                    'glyphicon' => 'glyphicon-plus',
                );

                $vars['buttons'] = $app['menus']->renderButtonGroup($buttons);

                $saveoperation = $app['context']->getCurrentSaveOperation();

                $vars['save_operation'] = key($saveoperation);
                $vars['save_operation_title'] = array_shift($saveoperation);

                $vars['links']['search'] = $this->generateUrl(
                    'listRecords',
                    array(
                        'contentTypeAccessHash' => $contentTypeAccessHash,
                        'page' => 1,
                        's' => 'name',
                        'workspace' => $app['context']->getCurrentWorkspace(),
                        'language' => $app['context']->getCurrentLanguage(),
                    )
                );
                $vars['links']['timeshift'] = false;
                $vars['links']['workspaces'] = $this->generateUrl(
                    'changeWorkspaceAddRecord',
                    array('contentTypeAccessHash' => $contentTypeAccessHash)
                );
                $vars['links']['languages'] = $this->generateUrl(
                    'changeLanguageAddRecord',
                    array('contentTypeAccessHash' => $contentTypeAccessHash)
                );

                $app['layout']->addJsFile('editrecord.js');
                return $app->renderPage('editrecord.twig', $vars);
            } else {
                return $app->renderPage('forbidden.twig', $vars);
            }
        }
    }


    #[Route('/content/edit/{contentTypeAccessHash}/{recordId}/{workspace}/{language}','anycontent_record_edit')]
    public function editRecord($contentTypeAccessHash, $recordId, $workspace, $language)
    {

        $vars = array();


        $vars['links']['search'] = $this->generateUrl(            'anycontent_records',
            array('contentTypeAccessHash' => $contentTypeAccessHash, 'page' => 1, 's' => 'name')
        );


        $repository = $this->repositoryManager->getRepositoryByContentTypeAccessHash($contentTypeAccessHash);

        if ($repository) {

            $vars['repository'] = $repository;
            $repositoryAccessHash = $this->repositoryManager->getRepositoryAccessHash($repository);
            $vars['links']['repository'] = $this->generateUrl(
                'anycontent_repository',
                array('repositoryAccessHash' => $repositoryAccessHash)
            );
            $vars['links']['listRecords'] = $this->generateUrl(
                'anycontent_records',
                array(
                    'contentTypeAccessHash' => $contentTypeAccessHash,
                    'page' => 1,
                    'workspace' => $this->contextManager->getCurrentWorkspace(),
                    'language' => $this->contextManager->getCurrentLanguage(),
                )
            );

            $this->contextManager->setCurrentRepository($repository);

            $contentTypeDefinition = $repository->getContentTypeDefinition();
            $this->contextManager->setCurrentContentType($contentTypeDefinition);
            //$this->contextManager->setDataTypeDefinition($contentTypeDefinition);

            if ($workspace != null && $contentTypeDefinition->hasWorkspace($workspace)) {
                $this->contextManager->setCurrentWorkspace($workspace);
            }
            if ($language != null && $contentTypeDefinition->hasLanguage($language)) {
                $this->contextManager->setCurrentLanguage($language);
            }

            $repository->selectWorkspace($this->contextManager->getCurrentWorkspace());
            $repository->selectLanguage($this->contextManager->getCurrentLanguage());
            $repository->setTimeShift($this->contextManager->getCurrentTimeShift());
            $repository->selectView('default');

            $record = $repository->getRecord($recordId);



            $buttons = $this->getButtons($contentTypeAccessHash, $contentTypeDefinition);
            $vars['buttons'] = $this->menuManager->renderButtonGroup($buttons);
            
            $saveoperation = $this->contextManager->getCurrentSaveOperation();

            $vars['save_operation'] = key($saveoperation);
            $vars['save_operation_title'] = array_shift($saveoperation);

            $vars['links']['search'] = $this->generateUrl(
                'anycontent_records',
                array(
                    'contentTypeAccessHash' => $contentTypeAccessHash,
                    'page' => 1,
                    's' => 'name',
                    'workspace' => $this->contextManager->getCurrentWorkspace(),
                    'language' => $this->contextManager->getCurrentLanguage(),
                )
            );

                $vars['links']['edit'] = true;


                $vars['links']['delete'] = $this->generateUrl(
                    'anycontent_record_delete',
                    array(
                        'contentTypeAccessHash' => $contentTypeAccessHash,
                        'recordId' => $recordId,
                        'workspace' => $this->contextManager->getCurrentWorkspace(),
                        'language' => $this->contextManager->getCurrentLanguage(),
                    )
                );
            }
//            if ($user->canDo('add', $repository, $contentTypeDefinition)) {
//                $vars['links']['transfer'] = $this->generateUrl(
//                    'transferRecordModal',
//                    array(
//                        'contentTypeAccessHash' => $contentTypeAccessHash,
//                        'recordId' => $recordId,
//                        'workspace' => $app['context']->getCurrentWorkspace(),
//                        'language' => $app['context']->getCurrentLanguage(),
//                    )
//                );
//            }
//            $vars['links']['timeshift'] = $this->generateUrl(
//                'timeShiftEditRecord',
//                array('contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId)
//            );
            $vars['links']['workspaces'] = $this->generateUrl(
                'anycontent_record_edit_change_workspace',
                array('contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId)
            );
            $vars['links']['languages'] = $this->generateUrl(
                'anycontent_record_edit_change_language',
                array('contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId)
            );
//            $vars['links']['addrecordversion'] = $this->generateUrl(
//                'addRecordVersion',
//                array(
//                    'contentTypeAccessHash' => $contentTypeAccessHash,
//                    'recordId' => $recordId,
//                    'workspace' => $app['context']->getCurrentWorkspace(),
//                    'language' => $app['context']->getCurrentLanguage(),
//                )
//            );
//
//            $vars['links']['revisions'] = $this->generateUrl(
//                'listRecordRevisions',
//                array(
//                    'recordId'=>$recordId,
//                    'contentTypeAccessHash' => $contentTypeAccessHash,
//                    'workspace' => $app['context']->getCurrentWorkspace(),
//                    'language' => $app['context']->getCurrentLanguage(),
//                )
//            );

            if ($record) {
                $this->contextManager->setCurrentRecord($record);
                $vars['record'] = $record;


                $contentTypeDefinition = $repository->getContentTypeDefinition();

                $vars['definition'] = $contentTypeDefinition;


                $viewDefinition = $contentTypeDefinition->getViewDefinition('default');

                // TODO: Attributes ??
                //$vars['form'] = $app['form']->renderFormElements('form_edit', $viewDefinition->getFormElementDefinitions(), $record->getProperties(), $record->getAttributes());

                $vars['form'] = $this->formManager->renderFormElements(
                    'form_edit',
                    $viewDefinition->getFormElementDefinitions(),
                    $record->getProperties(),
                    []
                );

                return $this->render('@AnyContentBackend/Content/editrecord.html.twig', $vars);
            } else {
                $vars['id'] = $recordId;

                return $this->render('@AnyContentBackend/Content/record-not-found.html.twig', $vars);
            }


        return $this->render('@AnyContentBackend/Login/forbidden.twig', $vars);
    }


    public static function saveRecord(Application $app, Request $request, $contentTypeAccessHash, $recordId = null)
    {
        /** @var UserManager $user */
        $user = $app['user'];

        $hidden = $request->get('$hidden');

        $saveOperationTitle = 'Save';
        $saveOperation = 'save';
        $save = true;
        $duplicate = false;
        $insert = false;
        $list = false;

        switch ($hidden['save_operation']) {
            case 'save-insert':
                $saveOperationTitle = 'Save & Insert';
                $saveOperation = 'save-insert';
                $insert = true;
                break;
            case 'save-duplicate':
                $saveOperationTitle = 'Save & Duplicate';
                $saveOperation = 'save-duplicate';
                $duplicate = true;
                break;
            case 'save-list':
                $saveOperationTitle = 'Save & List';
                $saveOperation = 'save-list';
                $list = true;
                break;
            case 'save-insert':
                $saveOperationTitle = 'Save & Insert';
                $saveOperation = 'save-insert';
                $insert = true;
                break;
        }

        if ( isset($hidden['duplicate']) && $hidden['duplicate']==1 ) {
            $duplicate = true;
        }

        /** @var Repository $repository */
        $repository = $app['repos']->getRepositoryByContentTypeAccessHash($contentTypeAccessHash);

        if ($repository && $user->canDo('add', $repository, $repository->getContentTypeDefinition())) {
            $app['context']->setCurrentRepository($repository);
            $app['context']->setCurrentContentType($repository->getContentTypeDefinition());

            $app['form']->setDataTypeDefinition($repository->getContentTypeDefinition());

            $app['context']->setCurrentSaveOperation($saveOperation, $saveOperationTitle);

            $app['context']->setCurrentWorkspace($hidden['workspace']);
            $app['context']->setCurrentLanguage($hidden['language']);

            $repository->selectWorkspace($app['context']->getCurrentWorkspace());
            $repository->selectLanguage($app['context']->getCurrentLanguage());
            $repository->setTimeShift($app['context']->getCurrentTimeShift());
            $repository->selectView('default');

            if ($recordId) {
                /** @var Record $record */
                //$record = $repository->getRecord($recordId, $app['context']->getCurrentWorkspace(), 'default', $app['context']->getCurrentLanguage(), $app['context']->getCurrentTimeShift());
                $record = $repository->getRecord($recordId);

                if (!$record) // if we don't have a record with the given id (there never was one, or it has been deleted), create a new one with the given id.
                {
                    $record = $repository->createRecord('New Record');
                    //$record = new Record($repository->getContentTypeDefinition(), 'New Record', 'default', $app['context']->getCurrentWorkspace(), 'default', $app['context']->getCurrentLanguage());
                    $record->setId($recordId);
                }
            } else {
                $record = $repository->createRecord('New Record');
                //$record = new Record($repository->getContentTypeDefinition(), 'New Record', 'default', $app['context']->getCurrentWorkspace(), 'default', $app['context']->getCurrentLanguage());
            }

            if ($record) {
                $app['context']->setCurrentRecord($record);

                /** @var ContentTypeDefinition $contentTypeDefinition */
                $contentTypeDefinition = $repository->getContentTypeDefinition();

                /* @var ViewDefinition */
                if ($recordId) {
                    $viewDefinition = $contentTypeDefinition->getEditViewDefinition();
                } else {
                    $viewDefinition = $contentTypeDefinition->getInsertViewDefinition();
                }

                //TODO Attributes ??
                //$values = $app['form']->extractFormElementValuesFromPostRequest($request, $viewDefinition->getFormElementDefinitions(), $record->getProperties(), $record->getAttributes());
                $values = $app['form']->extractFormElementValuesFromPostRequest(
                    $request,
                    $viewDefinition->getFormElementDefinitions(),
                    $record->getProperties(),
                    []
                );

                foreach ($values as $property => $value) {
                    $record->setProperty($property, $value);
                }

                if ($save) // check for unique properties
                {
                    $properties = array();
                    /**
                     * @var $formElementDefinitions FormElementDefinition[]
                     */
                    $formElementDefinitions = $viewDefinition->getFormElementDefinitions();
                    foreach ($formElementDefinitions as $formElementDefinition) {
                        if ($formElementDefinition->isUnique() && $record->getProperty(
                                $formElementDefinition->getName()
                            ) != ''
                        ) {
                            //$filter = new ContentFilter($contentTypeDefinition);
                            //$filter->addCondition($formElementDefinition->getName(), '=', $record->getProperty($formElementDefinition->getName()));

                            $filter = $formElementDefinition->getName().' = '.$record->getProperty(
                                    $formElementDefinition->getName()
                                );

                            //$records = $repository->getRecords($app['context']->getCurrentWorkspace(), $viewDefinition->getName(), $app['context']->getCurrentLanguage(), 'id', array(), 2, 1, $filter);
                            $records = $repository->getRecords($filter);

                            if (count($records) > 1) {
                                $properties[$formElementDefinition->getName()] = $formElementDefinition->getLabel();
                            } elseif (count($records) == 1) {
                                $oldRecord = array_shift($records);

                                if ($oldRecord->getID() != $recordId) {
                                    $properties[$formElementDefinition->getName()] = $formElementDefinition->getLabel();
                                }
                            }
                        }
                    }
                    if (count($properties) > 0) {
                        $message = 'Could not save record. <em>'.join(
                                ',',
                                array_values($properties)
                            ).'</em> must be unique for all records of this content type.';
                        $response = array(
                            'success' => false,
                            'message' => $message,
                            'properties' => array_keys($properties),
                        );

                        return new JsonResponse($response);
                    }
                }

                if ($save) {
                    if ($recordId) {
                        $event = new EditRecordSaveEvent($app, $record);
                        $app['dispatcher']->dispatch(Module::EVENT_EDIT_RECORD_BEFORE_UPDATE, $event);
                    } else {
                        $event = new EditRecordInsertEvent($app, $record);
                        $app['dispatcher']->dispatch(Module::EVENT_EDIT_RECORD_BEFORE_INSERT, $event);
                    }


                    if ($event->hasErrorMessage()) {

                        $response = array(
                            'success' => false,
                            'error' => true,
                            'message' => 'Could not save record: '.$event->getErrorMessage(),
                            'properties' => array(''),
                        );

                        return new JsonResponse($response);
                    }

                    if ($event->hasInfoMessage()) {
                        $app['context']->addInfoMessage($event->getInfoMessage());
                    }

                    if ($event->hasAlertMessage()) {
                        $app['context']->addAlertMessage($event->getAlertMessage());
                    }

                    $recordId = $repository->saveRecord($record);

                    $app['context']->resetTimeShift();
                    if ($recordId) {
                        $app['context']->addSuccessMessage('Record saved.');


                    } else {
                        $response = array(
                            'success' => false,
                            'error' => true,
                            'message' => 'Could not save record. Please check your input.',
                            'properties' => array(''),
                        );

                        return new JsonResponse($response);
                    }
                }
                if ($duplicate) {
                    $record->setName('Duplicate from '.$record->getId().' - '.$record->getName());
                    $record->setId(null);
                    $recordId = $repository->saveRecord(
                        $record,
                        $app['context']->getCurrentWorkspace(),
                        'default',
                        $app['context']->getCurrentLanguage()
                    );
                    $app['context']->resetTimeShift();
                }

                if ($insert) {
                    $url = $this->generateUrl(
                        'addRecord',
                        array('contentTypeAccessHash' => $contentTypeAccessHash)
                    );
                    $response = array('success' => true, 'redirect' => $url);

                    return new JsonResponse($response);
                }

                if ($list) {
                    $url = $this->generateUrl(
                        'listRecords',
                        array(
                            'contentTypeAccessHash' => $contentTypeAccessHash,
                            'page' => $app['context']->getCurrentListingPage(),
                        )
                    );
                    $response = array('success' => true, 'redirect' => $url);

                    return new JsonResponse($response);
                }

                $url = $this->generateUrl(
                    'editRecord',
                    array('contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId)
                );
                $response = array('success' => true, 'redirect' => $url);

                return new JsonResponse($response);

            } else {
                $response = array('success' => false, 'message' => 'Record not found.');

                return new JsonResponse($response);
            }
        }

        $response = array('success' => false, 'message' => '403 Forbidden');

        return new JsonResponse($response);
    }


    public function deleteRecord(
        Application $app,
        Request $request,
                    $contentTypeAccessHash,
                    $recordId,
                    $workspace,
                    $language
    ) {
        /** @var UserManager $user */
        $user = $app['user'];

        $recordId = (int)$recordId;

        if ($recordId) {
            /** @var Repository $repository */
            $repository = $app['repos']->getRepositoryByContentTypeAccessHash($contentTypeAccessHash);

            if ($repository && $user->canDo(
                    'delete',
                    $repository,
                    $repository->getContentTypeDefinition(),
                    $recordId
                )
            ) {
                $app['context']->setCurrentRepository($repository);
                $contentTypeDefinition = $repository->getContentTypeDefinition();
                $app['context']->setCurrentContentType($contentTypeDefinition);

                if ($workspace != null && $contentTypeDefinition->hasWorkspace($workspace)) {
                    $app['context']->setCurrentWorkspace($workspace);
                }
                if ($language != null && $contentTypeDefinition->hasLanguage($language)) {
                    $app['context']->setCurrentLanguage($language);
                }

                $repository->selectWorkspace($app['context']->getCurrentWorkspace());
                $repository->selectLanguage($app['context']->getCurrentLanguage());

                if ($repository->deleteRecord($recordId)) {
                    $app['context']->addSuccessMessage('Record '.$recordId.' deleted.');
                } else {
                    $app['context']->addErrorMessage('Could not delete record.');
                }

                return new RedirectResponse(
                    $this->generateUrl(
                        'listRecords',
                        array(
                            'contentTypeAccessHash' => $contentTypeAccessHash,
                            'page' => $app['context']->getCurrentListingPage(),
                        )
                    ), 303
                );
            }

        }

        return $app->renderPage('forbidden.twig');
    }


    /**
     * Displays the transfer record dialog
     *
     * @param Application $app
     * @param Request $request
     * @param             $contentTypeAccessHash
     * @param             $recordId
     * @param             $workspace
     * @param             $language
     *
     * @return mixed
     */
    public function transferRecordModal(
        Application $app,
        Request $request,
                    $contentTypeAccessHash,
                    $recordId,
                    $workspace,
                    $language
    ) {
        $vars = array();
        $vars['record'] = false;

        $recordId = (int)$recordId;

        if ($recordId) {
            /** @var Repository $repository */
            $repository = $app['repos']->getRepositoryByContentTypeAccessHash($contentTypeAccessHash);

            if ($repository) {
                $contentTypeDefinition = $repository->getContentTypeDefinition();
                $app['context']->setCurrentRepository($repository);
                $app['context']->setCurrentContentType($contentTypeDefinition);

                if ($workspace != null && $contentTypeDefinition->hasWorkspace($workspace)) {
                    $app['context']->setCurrentWorkspace($workspace);
                }
                if ($language != null && $contentTypeDefinition->hasLanguage($language)) {
                    $app['context']->setCurrentLanguage($language);
                }

                $repository->selectWorkspace($app['context']->getCurrentWorkspace());
                $repository->selectLanguage($app['context']->getCurrentLanguage());
                $repository->setTimeShift($app['context']->getCurrentTimeShift());
                $repository->selectView('default');

                /** @var Record $record */
                //$record = $repository->getRecord($recordId, $app['context']->getCurrentWorkspace(), 'default', $app['context']->getCurrentLanguage(), $app['context']->getCurrentTimeShift());
                $record = $repository->getRecord($recordId);

                if ($record) {
                    $app['context']->setCurrentRecord($record);
                    $vars['record'] = $record;

                    /** @var ContentTypeDefinition $contentTypeDefinition */
                    $contentTypeDefinition = $repository->getContentTypeDefinition();

                    $vars['definition'] = $contentTypeDefinition;

                    $records = array();

                    $repository->setTimeShift(0);

                    foreach ($repository->getRecords() as $record) {
                        $records[$record->getID()] = '#'.$record->getID().' '.$record->getName();
                    }
                    $vars['records'] = $records;

                    $vars['links']['transfer'] = $this->generateUrl(
                        'transferRecord',
                        array(
                            'contentTypeAccessHash' => $contentTypeAccessHash,
                            'recordId' => $recordId,
                            "workspace" => $app['context']->getCurrentWorkspace(),
                            "language" => $app['context']->getCurrentLanguage(),
                        )
                    );
                }
            }
        }

        return $app->renderPage('transferrecord-modal.twig', $vars);
    }


    public function transferRecord(
        Application $app,
        Request $request,
                    $contentTypeAccessHash,
                    $recordId,
                    $workspace,
                    $language
    ) {
        $recordId = (int)$recordId;

        if ($recordId) {
            /** @var Repository $repository */
            $repository = $app['repos']->getRepositoryByContentTypeAccessHash($contentTypeAccessHash);

            if ($repository) {
                $app['context']->setCurrentRepository($repository);
                $app['context']->setCurrentContentType($repository->getContentTypeDefinition());

                /** @var ContentTypeDefinition $contentTypeDefinition */
                $contentTypeDefinition = $repository->getContentTypeDefinition();

                if ($workspace != null && $contentTypeDefinition->hasWorkspace($workspace)) {
                    $app['context']->setCurrentWorkspace($workspace);
                }
                if ($language != null && $contentTypeDefinition->hasLanguage($language)) {
                    $app['context']->setCurrentLanguage($language);
                }

                $repository->selectWorkspace($app['context']->getCurrentWorkspace());
                $repository->selectLanguage($app['context']->getCurrentLanguage());
                $repository->setTimeShift($app['context']->getCurrentTimeShift());
                $repository->selectView($contentTypeDefinition->getExchangeViewDefinition()->getName());

                /** @var Record $record */
                $record = $repository->getRecord($recordId);

                if ($record) {
                    $record->setID((int)$request->get('id'));

                    if ($request->request->has('target_workspace')) {
                        $workspace = $request->get('target_workspace');
                        $app['context']->setCurrentWorkspace($workspace);
                    }

                    if ($request->request->has('target_language')) {
                        $language = $request->get('target_language');
                        $app['context']->setCurrentLanguage($language);
                    }

                    $repository->selectWorkspace($app['context']->getCurrentWorkspace());
                    $repository->selectLanguage($app['context']->getCurrentLanguage());
                    $repository->setTimeShift(0);

                    $recordId = $repository->saveRecord($record);
                    $app['context']->resetTimeShift();

                    $app['context']->addSuccessMessage('Record '.$recordId.' transfered.');

                    return new RedirectResponse(
                        $this->generateUrl(
                            'editRecord',
                            array('contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId)
                        ), 303
                    );
                }
            }
        }

        $app['context']->addErrorMessage('Could not load source record.');

        return new RedirectResponse(
            $this->generateUrl(
                'listRecords',
                array(
                    'contentTypeAccessHash' => $contentTypeAccessHash,
                    'page' => $app['context']->getCurrentListingPage(),
                )
            ), 303
        );
    }

}