<?php

namespace AnyContent\Backend\Modules\Edit\Controller;

use AnyContent\Backend\Controller\AbstractAnyContentBackendController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ANYCONTENT')]
class RecordsController extends AbstractAnyContentBackendController
{
    #[Route('/content/add/{contentTypeAccessHash}/{workspace}/{language}', 'anycontent_record_add', methods: ['GET'])]
    public function addRecord($contentTypeAccessHash, $workspace, $language)
    {
        return $this->editRecord($contentTypeAccessHash, null, $workspace, $language);

//        $vars = array();
//
//        $vars['menu_mainmenu'] = $app['menus']->renderMainMenu();
//
//        /** @var Repository $repository */
//        $repository = $app['repos']->getRepositoryByContentTypeAccessHash($contentTypeAccessHash);
//
//        if ($repository) {
//            $vars['repository'] = $repository;
//            $repositoryAccessHash = $app['repos']->getRepositoryAccessHash($repository);
//            $vars['links']['repository'] = $this->generateUrl(
//                'indexRepository',
//                array('repositoryAccessHash' => $repositoryAccessHash)
//            );
//            $vars['links']['listRecords'] = $this->generateUrl(
//                'listRecords',
//                array(
//                    'contentTypeAccessHash' => $contentTypeAccessHash,
//                    'page' => 1,
//                    'workspace' => $this->contextManager->getCurrentWorkspace(),
//                    'language' => $this->contextManager->getCurrentLanguage(),
//                )
//            );
//
//            $this->contextManager->setCurrentRepository($repository);
//            $this->contextManager->setCurrentContentType($repository->getContentTypeDefinition());
//
//            $formManager->setDataTypeDefinition($repository->getContentTypeDefinition());
//
//            $vars['record'] = false;
//
//            /** @var ContentTypeDefinition $contentTypeDefinition */
//            $contentTypeDefinition = $repository->getContentTypeDefinition();
//
//            $vars['definition'] = $contentTypeDefinition;
//
//            if ($user->canDo(
//                'add',
//                $repository,
//                $contentTypeDefinition
//            )
//            ) {
//                $vars['links']['edit'] = true;
//
//                /* @var ViewDefinition */
//
//                $viewDefinition = $contentTypeDefinition->getInsertViewDefinition();
//
//                $properties = array();
//                foreach ($viewDefinition->getFormElementDefinitions() as $formElementDefinition) {
//                    $properties[$formElementDefinition->getName()] = $formElementDefinition->getDefaultValue();
//                }
//
//                $vars['form'] = $formManager->renderFormElements(
//                    'form_edit',
//                    $viewDefinition->getFormElementDefinitions(),
//                    $properties,
//                    array(
//                        'workspace' => $this->contextManager->getCurrentWorkspace(),
//                        'language' => $this->contextManager->getCurrentLanguage(),
//                    )
//                );
//
//                $buttons = array();
//                $buttons[] = array(
//                    'label' => 'List Records',
//                    'url' => $this->generateUrl(
//                        'listRecords',
//                        array(
//                            'contentTypeAccessHash' => $contentTypeAccessHash,
//                            'page' => 1,
//                            'workspace' => $this->contextManager->getCurrentWorkspace(),
//                            'language' => $this->contextManager->getCurrentLanguage(),
//                        )
//                    ),
//                    'glyphicon' => 'glyphicon-list',
//                );
//                if ($contentTypeDefinition->isSortable()) {
//                    $buttons[] = array(
//                        'label' => 'Sort Records',
//                        'url' => $this->generateUrl(
//                            'sortRecords',
//                            array(
//                                'contentTypeAccessHash' => $contentTypeAccessHash,
//                                'workspace' => $this->contextManager->getCurrentWorkspace(),
//                                'language' => $this->contextManager->getCurrentLanguage(),
//                            )
//                        ),
//                        'glyphicon' => 'glyphicon-move',
//                    );
//                }
//                $buttons[] = array(
//                    'label' => 'Add Record',
//                    'url' => $this->generateUrl(
//                        'addRecord',
//                        array('contentTypeAccessHash' => $contentTypeAccessHash)
//                    ),
//                    'glyphicon' => 'glyphicon-plus',
//                );
//
//                $vars['buttons'] = $app['menus']->renderButtonGroup($buttons);
//
//                $saveoperation = $this->contextManager->getCurrentSaveOperation();
//
//                $vars['save_operation'] = key($saveoperation);
//                $vars['save_operation_title'] = array_shift($saveoperation);
//
//                $vars['links']['search'] = $this->generateUrl(
//                    'listRecords',
//                    array(
//                        'contentTypeAccessHash' => $contentTypeAccessHash,
//                        'page' => 1,
//                        's' => 'name',
//                        'workspace' => $this->contextManager->getCurrentWorkspace(),
//                        'language' => $this->contextManager->getCurrentLanguage(),
//                    )
//                );
//                $vars['links']['timeshift'] = false;
//                $vars['links']['workspaces'] = $this->generateUrl(
//                    'changeWorkspaceAddRecord',
//                    array('contentTypeAccessHash' => $contentTypeAccessHash)
//                );
//                $vars['links']['languages'] = $this->generateUrl(
//                    'changeLanguageAddRecord',
//                    array('contentTypeAccessHash' => $contentTypeAccessHash)
//                );
//
//                $app['layout']->addJsFile('editrecord.js');
//                return $app->renderPage('editrecord.twig', $vars);
//            } else {
//                return $app->renderPage('forbidden.twig', $vars);
//            }
//        }
    }

    #[Route('/content/edit/{contentTypeAccessHash}/{recordId}/{workspace}/{language}', 'anycontent_record_edit', methods: ['GET'])]
    public function editRecord($contentTypeAccessHash, $recordId, $workspace, $language)
    {
        $vars = array();

        $vars['links']['search'] = $this->generateUrl(
            'anycontent_records',
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
        }

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
//                    'workspace' => $this->contextManager->getCurrentWorkspace(),
//                    'language' => $this->contextManager->getCurrentLanguage(),
//                )
//            );
//
//            $vars['links']['revisions'] = $this->generateUrl(
//                'listRecordRevisions',
//                array(
//                    'recordId'=>$recordId,
//                    'contentTypeAccessHash' => $contentTypeAccessHash,
//                    'workspace' => $this->contextManager->getCurrentWorkspace(),
//                    'language' => $this->contextManager->getCurrentLanguage(),
//                )
//            );

        $contentTypeDefinition = $repository->getContentTypeDefinition();

        $vars['definition'] = $contentTypeDefinition;
        $vars['record'] = false;
        $viewDefinition = $contentTypeDefinition->getInsertViewDefinition();
        $properties = [];

        if ($recordId !== null) {
            $record = $repository->getRecord($recordId);
            $viewDefinition = $contentTypeDefinition->getViewDefinition('default');
            if (!$record) {
                $vars['id'] = $recordId;
                return $this->render('@AnyContentBackend/Content/record-not-found.html.twig', $vars);
            }
            $vars['record'] = $record;
            $vars['links']['delete'] = $this->generateUrl(
                'anycontent_record_delete',
                array(
                    'contentTypeAccessHash' => $contentTypeAccessHash,
                    'recordId' => $recordId,
                    'workspace' => $this->contextManager->getCurrentWorkspace(),
                    'language' => $this->contextManager->getCurrentLanguage(),
                )
            );
            //            if ($user->canDo('add', $repository, $contentTypeDefinition)) {
            $vars['links']['transfer'] = $this->generateUrl(
                'anycontent_record_transfer_modal',
                array(
                    'contentTypeAccessHash' => $contentTypeAccessHash,
                    'recordId' => $recordId,
                    'workspace' => $this->contextManager->getCurrentWorkspace(),
                    'language' => $this->contextManager->getCurrentLanguage(),
                )
            );
            //}
            $this->contextManager->setCurrentRecord($record);
            $properties = $record->getProperties();
        }

        $vars['form'] = $this->formManager->renderFormElements(
            'form_edit',
            $viewDefinition->getFormElementDefinitions(),
            $properties
        );

        return $this->render('@AnyContentBackend/Content/editrecord.html.twig', $vars);
    }

    #[Route('/content/edit/{contentTypeAccessHash}/{recordId}/{workspace}/{language}', 'anycontent_record_save', methods: ['POST'])]
    #[Route('/content/add/{contentTypeAccessHash}/{workspace}/{language}', 'anycontent_record_insert', methods: ['POST'])]
    public function saveRecord(Request $request, $contentTypeAccessHash, $recordId = null)
    {
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
        }

        if (isset($hidden['duplicate']) && $hidden['duplicate'] == 1) {
            $duplicate = true;
        }

        $repository = $this->repositoryManager->getRepositoryByContentTypeAccessHash($contentTypeAccessHash);

        if ($repository) {
            $this->contextManager->setCurrentRepository($repository);
            $this->contextManager->setCurrentContentType($repository->getContentTypeDefinition());

            $this->formManager->setDataTypeDefinition($repository->getContentTypeDefinition());

            $this->contextManager->setCurrentSaveOperation($saveOperation, $saveOperationTitle);

            $this->contextManager->setCurrentWorkspace($hidden['workspace']);
            $this->contextManager->setCurrentLanguage($hidden['language']);

            $repository->selectWorkspace($this->contextManager->getCurrentWorkspace());
            $repository->selectLanguage($this->contextManager->getCurrentLanguage());
            $repository->setTimeShift($this->contextManager->getCurrentTimeShift());
            $repository->selectView('default');

            if ($recordId) {
                //$record = $repository->getRecord($recordId, $this->contextManager->getCurrentWorkspace(), 'default', $this->contextManager->getCurrentLanguage(), $this->contextManager->getCurrentTimeShift());
                $record = $repository->getRecord($recordId);

                if (!$record) { // if we don't have a record with the given id (there never was one, or it has been deleted), create a new one with the given id.
                    $record = $repository->createRecord('New Record');
                    //$record = new Record($repository->getContentTypeDefinition(), 'New Record', 'default', $this->contextManager->getCurrentWorkspace(), 'default', $this->contextManager->getCurrentLanguage());
                    $record->setId($recordId);
                }
            } else {
                $record = $repository->createRecord('New Record');
                //$record = new Record($repository->getContentTypeDefinition(), 'New Record', 'default', $this->contextManager->getCurrentWorkspace(), 'default', $this->contextManager->getCurrentLanguage());
            }

            if ($record) {
                $this->contextManager->setCurrentRecord($record);

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
                $values = $this->formManager->extractFormElementValuesFromPostRequest(
                    $request,
                    $viewDefinition->getFormElementDefinitions(),
                    $record->getProperties(),
                    []
                );

                foreach ($values as $property => $value) {
                    $record->setProperty($property, $value);
                }

                if ($save) { // check for unique properties
                    $properties = array();
                    /**
                     * @var $formElementDefinitions FormElementDefinition[]
                     */
                    $formElementDefinitions = $viewDefinition->getFormElementDefinitions();
                    foreach ($formElementDefinitions as $formElementDefinition) {
                        if (
                            $formElementDefinition->isUnique() && $record->getProperty(
                                $formElementDefinition->getName()
                            ) != ''
                        ) {
                            //$filter = new ContentFilter($contentTypeDefinition);
                            //$filter->addCondition($formElementDefinition->getName(), '=', $record->getProperty($formElementDefinition->getName()));

                            $filter = $formElementDefinition->getName() . ' = ' . $record->getProperty(
                                $formElementDefinition->getName()
                            );

                            //$records = $repository->getRecords($this->contextManager->getCurrentWorkspace(), $viewDefinition->getName(), $this->contextManager->getCurrentLanguage(), 'id', array(), 2, 1, $filter);
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
                        $message = 'Could not save record. <em>' . join(
                            ',',
                            array_values($properties)
                        ) . '</em> must be unique for all records of this content type.';
                        $response = array(
                            'success' => false,
                            'message' => $message,
                            'properties' => array_keys($properties),
                        );

                        return new JsonResponse($response);
                    }
                }

                if ($save) {
//                    if ($recordId) {
//                        $event = new EditRecordSaveEvent($app, $record);
//                        $app['dispatcher']->dispatch(Module::EVENT_EDIT_RECORD_BEFORE_UPDATE, $event);
//                    } else {
//                        $event = new EditRecordInsertEvent($app, $record);
//                        $app['dispatcher']->dispatch(Module::EVENT_EDIT_RECORD_BEFORE_INSERT, $event);
//                    }
//
//
//                    if ($event->hasErrorMessage()) {
//
//                        $response = array(
//                            'success' => false,
//                            'error' => true,
//                            'message' => 'Could not save record: '.$event->getErrorMessage(),
//                            'properties' => array(''),
//                        );
//
//                        return new JsonResponse($response);
//                    }
//
//                    if ($event->hasInfoMessage()) {
//                        $this->contextManager->addInfoMessage($event->getInfoMessage());
//                    }
//
//                    if ($event->hasAlertMessage()) {
//                        $this->contextManager->addAlertMessage($event->getAlertMessage());
//                    }

                    $recordId = $repository->saveRecord($record);

                    $this->contextManager->resetTimeShift();
                    if ($recordId) {
                        $this->contextManager->addSuccessMessage('Record saved.');
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
                    $record->setName('Duplicate from ' . $record->getId() . ' - ' . $record->getName());
                    $record->setId(null);
                    $recordId = $repository->saveRecord(
                        $record,
                        $this->contextManager->getCurrentWorkspace(),
                        'default',
                        $this->contextManager->getCurrentLanguage()
                    );
                    $this->contextManager->resetTimeShift();
                }

                if ($insert) {
                    $url = $this->generateUrl(
                        'anycontent_record_add',
                        array('contentTypeAccessHash' => $contentTypeAccessHash)
                    );
                    $response = array('success' => true, 'redirect' => $url);

                    return new JsonResponse($response);
                }

                if ($list) {
                    $url = $this->generateUrl(
                        'anycontent_records',
                        array(
                            'contentTypeAccessHash' => $contentTypeAccessHash,
                            'page' => $this->contextManager->getCurrentListingPage(),
                        )
                    );
                    $response = array('success' => true, 'redirect' => $url);

                    return new JsonResponse($response);
                }

                $url = $this->generateUrl(
                    'anycontent_record_edit',
                    array('contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId, 'workspace' => $this->contextManager->getCurrentWorkspace(), 'language' => $this->contextManager->getCurrentLanguage())
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

    #[Route('/content/delete/{contentTypeAccessHash}/{recordId}/{workspace}/{language}', 'anycontent_record_delete', methods: ['GET'])]
    public function deleteRecord(
        $contentTypeAccessHash,
        $recordId,
        $workspace,
        $language
    ) {
        $recordId = (int)$recordId;

        if ($recordId) {
            $repository = $this->updateContext($contentTypeAccessHash, $workspace, $language);

            if ($repository->deleteRecord($recordId)) {
                $this->contextManager->addSuccessMessage('Record ' . $recordId . ' deleted.');
            } else {
                $this->contextManager->addErrorMessage('Could not delete record.');
            }

            return new RedirectResponse(
                $this->generateUrl(
                    'anycontent_records',
                    array(
                        'contentTypeAccessHash' => $contentTypeAccessHash,
                        'page' => $this->contextManager->getCurrentListingPage(),
                    )
                ),
                303
            );
        }
    }

    private function updateContext($contentTypeAccessHash, $workspace, $language)
    {
        $repository = $this->repositoryManager->getRepositoryByContentTypeAccessHash($contentTypeAccessHash);

        if (!$repository) {
            throw new NotFoundHttpException();
        }
        $this->contextManager->setCurrentRepository($repository);

        $contentTypeDefinition = $repository->getContentTypeDefinition();
        $this->contextManager->setCurrentContentType($contentTypeDefinition);

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
        return $repository;
    }

    #[Route('/modal/content/transfer/{contentTypeAccessHash}/{recordId}/{workspace}/{language}', 'anycontent_record_transfer_modal', methods: ['GET'])]
    public function transferRecordModal(
        $contentTypeAccessHash,
        $recordId,
        $workspace,
        $language
    ) {
        $repository = $this->updateContext($contentTypeAccessHash, $workspace, $language);

        $vars = array();
        $vars['record'] = false;

        $recordId = (int)$recordId;

          $record = $repository->getRecord($recordId);

        if ($record) {
            $this->contextManager->setCurrentRecord($record);
            $vars['record'] = $record;

            $contentTypeDefinition = $repository->getContentTypeDefinition();

            $vars['definition'] = $contentTypeDefinition;

            $records = array();

            $repository->setTimeShift(0);

            foreach ($repository->getRecords() as $record) {
                $records[$record->getID()] = '#' . $record->getID() . ' ' . $record->getName();
            }
            $vars['records'] = $records;

            $vars['links']['transfer'] = $this->generateUrl(
                'anycontent_record_transfer',
                array(
                    'contentTypeAccessHash' => $contentTypeAccessHash,
                    'recordId' => $recordId,
                    "workspace" => $this->contextManager->getCurrentWorkspace(),
                    "language" => $this->contextManager->getCurrentLanguage(),
                )
            );
        }

        return $this->render('@AnyContentBackend/Content/transferrecord-modal.twig', $vars);
    }

    #[Route('/content/transfer/{contentTypeAccessHash}/{recordId}/{workspace}/{language}', 'anycontent_record_transfer', methods: ['POST'])]
    public function transferRecord(
        Request $request,
        $contentTypeAccessHash,
        $recordId,
        $workspace,
        $language
    ) {
        $repository = $this->updateContext($contentTypeAccessHash, $workspace, $language);

        $recordId = (int)$recordId;

        $contentTypeDefinition = $repository->getContentTypeDefinition();

                $repository->selectView($contentTypeDefinition->getExchangeViewDefinition()->getName());

                $record = $repository->getRecord($recordId);

        if ($record) {
            $record->setID((int)$request->get('id'));

            if ($request->request->has('target_workspace')) {
                $workspace = $request->get('target_workspace');
                $this->contextManager->setCurrentWorkspace($workspace);
            }

            if ($request->request->has('target_language')) {
                $language = $request->get('target_language');
                $this->contextManager->setCurrentLanguage($language);
            }

            $repository->selectWorkspace($this->contextManager->getCurrentWorkspace());
            $repository->selectLanguage($this->contextManager->getCurrentLanguage());
            $repository->setTimeShift(0);

            $recordId = $repository->saveRecord($record);

            $this->contextManager->resetTimeShift();

            $this->contextManager->addSuccessMessage('Record ' . $recordId . ' transfered.');

            return new RedirectResponse(
                $this->generateUrl(
                    'anycontent_record_edit',
                    array('contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId,
                        'workspace' => $this->contextManager->getCurrentWorkspace(),
                        'language' => $this->contextManager->getCurrentLanguage())
                ),
                303
            );
        }

        $this->contextManager->addErrorMessage('Could not load source record.');

        return new RedirectResponse(
            $this->generateUrl(
                'anycontent_records',
                array(
                    'contentTypeAccessHash' => $contentTypeAccessHash,
                    'page' => $this->contextManager->getCurrentListingPage(),
                )
            ),
            303
        );
    }
}
