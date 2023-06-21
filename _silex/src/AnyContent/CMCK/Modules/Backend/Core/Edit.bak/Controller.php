<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Edit;

use AnyContent\Client\ContentFilter;
use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

use AnyContent\CMCK\Modules\Backend\Core\User\UserManager;
use CMDL\ContentTypeDefinition;
use CMDL\ViewDefinition;

use AnyContent\Client\Repository;
use AnyContent\Client\Record;

use AnyContent\CMCK\Modules\Backend\Core\Edit\FormManager;

use CMDL\FormElementDefinition;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class Controller
{

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
            $vars['links']['repository'] = $app['url_generator']->generate(
                'indexRepository',
                array('repositoryAccessHash' => $repositoryAccessHash)
            );
            $vars['links']['listRecords'] = $app['url_generator']->generate(
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
                    'url' => $app['url_generator']->generate(
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
                        'url' => $app['url_generator']->generate(
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
                    'url' => $app['url_generator']->generate(
                        'addRecord',
                        array('contentTypeAccessHash' => $contentTypeAccessHash)
                    ),
                    'glyphicon' => 'glyphicon-plus',
                );

                $vars['buttons'] = $app['menus']->renderButtonGroup($buttons);

                $saveoperation = $app['context']->getCurrentSaveOperation();

                $vars['save_operation'] = key($saveoperation);
                $vars['save_operation_title'] = array_shift($saveoperation);

                $vars['links']['search'] = $app['url_generator']->generate(
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
                $vars['links']['workspaces'] = $app['url_generator']->generate(
                    'changeWorkspaceAddRecord',
                    array('contentTypeAccessHash' => $contentTypeAccessHash)
                );
                $vars['links']['languages'] = $app['url_generator']->generate(
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


    public static function editRecord(Application $app, $contentTypeAccessHash, $recordId, $workspace, $language)
    {
        /** @var UserManager $user */
        $user = $app['user'];

        $vars = array();

        $vars['menu_mainmenu'] = $app['menus']->renderMainMenu();
        $vars['links']['search'] = $app['url_generator']->generate(
            'listRecords',
            array('contentTypeAccessHash' => $contentTypeAccessHash, 'page' => 1, 's' => 'name')
        );

        /** @var Repository $repository */
        $repository = $app['repos']->getRepositoryByContentTypeAccessHash($contentTypeAccessHash);

        if ($repository) {

            $vars['repository'] = $repository;
            $repositoryAccessHash = $app['repos']->getRepositoryAccessHash($repository);
            $vars['links']['repository'] = $app['url_generator']->generate(
                'indexRepository',
                array('repositoryAccessHash' => $repositoryAccessHash)
            );
            $vars['links']['listRecords'] = $app['url_generator']->generate(
                'listRecords',
                array(
                    'contentTypeAccessHash' => $contentTypeAccessHash,
                    'page' => 1,
                    'workspace' => $app['context']->getCurrentWorkspace(),
                    'language' => $app['context']->getCurrentLanguage(),
                )
            );

            $app['context']->setCurrentRepository($repository);

            $contentTypeDefinition = $repository->getContentTypeDefinition();
            $app['context']->setCurrentContentType($contentTypeDefinition);
            $app['form']->setDataTypeDefinition($contentTypeDefinition);

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
            $record = $repository->getRecord($recordId);

            $buttons = array();
            $buttons[100] = array(
                'label' => 'List Records',
                'url' => $app['url_generator']->generate(
                    'listRecords',
                    array(
                        'contentTypeAccessHash' => $contentTypeAccessHash,
                        'page' => $app['context']->getCurrentListingPage(),
                        'workspace' => $app['context']->getCurrentWorkspace(),
                        'language' => $app['context']->getCurrentLanguage(),
                    )
                ),
                'glyphicon' => 'glyphicon-list',
            );

            if ($contentTypeDefinition->isSortable() && $user->canDo('sort', $repository, $contentTypeDefinition)) {
                $buttons[200] = array(
                    'label' => 'Sort Records',
                    'url' => $app['url_generator']->generate(
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
            if ($user->canDo('add', $repository, $contentTypeDefinition)) {
                $buttons[300] = array(
                    'label' => 'Add Record',
                    'url' => $app['url_generator']->generate(
                        'addRecord',
                        array(
                            'contentTypeAccessHash' => $contentTypeAccessHash,
                            'workspace' => $app['context']->getCurrentWorkspace(),
                            'language' => $app['context']->getCurrentLanguage(),
                        )
                    ),
                    'glyphicon' => 'glyphicon-plus',
                );
            }

            if ($user->canDo('export', $repository, $contentTypeDefinition)) {
                $buttons[400] = array(
                    'label' => 'Export Records',
                    'url' => $app['url_generator']->generate(
                        'exportRecords',
                        array('contentTypeAccessHash' => $contentTypeAccessHash)
                    ),
                    'glyphicon' => 'glyphicon-cloud-download',
                    'id' => 'listing_button_export',
                );
            }
            if ($user->canDo('import', $repository, $contentTypeDefinition)) {
                $buttons[500] = array(
                    'label' => 'Import Records',
                    'url' => $app['url_generator']->generate(
                        'importRecords',
                        array('contentTypeAccessHash' => $contentTypeAccessHash)
                    ),
                    'glyphicon' => 'glyphicon-cloud-upload',
                    'id' => 'listing_button_import',
                );
            }

            $vars['buttons'] = $app['menus']->renderButtonGroup($buttons);

            $saveoperation = $app['context']->getCurrentSaveOperation();

            $vars['save_operation'] = key($saveoperation);
            $vars['save_operation_title'] = array_shift($saveoperation);

            $vars['links']['search'] = $app['url_generator']->generate(
                'listRecords',
                array(
                    'contentTypeAccessHash' => $contentTypeAccessHash,
                    'page' => 1,
                    's' => 'name',
                    'workspace' => $app['context']->getCurrentWorkspace(),
                    'language' => $app['context']->getCurrentLanguage(),
                )
            );
            if ($user->canDo('edit', $repository, $contentTypeDefinition, $record)) {
                $vars['links']['edit'] = true;
            }
            if ($user->canDo('delete', $repository, $contentTypeDefinition, $recordId)) {
                $vars['links']['delete'] = $app['url_generator']->generate(
                    'deleteRecord',
                    array(
                        'contentTypeAccessHash' => $contentTypeAccessHash,
                        'recordId' => $recordId,
                        'workspace' => $app['context']->getCurrentWorkspace(),
                        'language' => $app['context']->getCurrentLanguage(),
                    )
                );
            }
            if ($user->canDo('add', $repository, $contentTypeDefinition)) {
                $vars['links']['transfer'] = $app['url_generator']->generate(
                    'transferRecordModal',
                    array(
                        'contentTypeAccessHash' => $contentTypeAccessHash,
                        'recordId' => $recordId,
                        'workspace' => $app['context']->getCurrentWorkspace(),
                        'language' => $app['context']->getCurrentLanguage(),
                    )
                );
            }
            $vars['links']['timeshift'] = $app['url_generator']->generate(
                'timeShiftEditRecord',
                array('contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId)
            );
            $vars['links']['workspaces'] = $app['url_generator']->generate(
                'changeWorkspaceEditRecord',
                array('contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId)
            );
            $vars['links']['languages'] = $app['url_generator']->generate(
                'changeLanguageEditRecord',
                array('contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId)
            );
            $vars['links']['addrecordversion'] = $app['url_generator']->generate(
                'addRecordVersion',
                array(
                    'contentTypeAccessHash' => $contentTypeAccessHash,
                    'recordId' => $recordId,
                    'workspace' => $app['context']->getCurrentWorkspace(),
                    'language' => $app['context']->getCurrentLanguage(),
                )
            );

            $vars['links']['revisions'] = $app['url_generator']->generate(
                'listRecordRevisions',
                array(
                    'recordId'=>$recordId,
                    'contentTypeAccessHash' => $contentTypeAccessHash,
                    'workspace' => $app['context']->getCurrentWorkspace(),
                    'language' => $app['context']->getCurrentLanguage(),
                )
            );

            if ($record) {
                $app['context']->setCurrentRecord($record);
                $vars['record'] = $record;

                /** @var ContentTypeDefinition $contentTypeDefinition */
                $contentTypeDefinition = $repository->getContentTypeDefinition();

                $vars['definition'] = $contentTypeDefinition;

                /* @var ViewDefinition */
                $viewDefinition = $contentTypeDefinition->getViewDefinition('default');

                // TODO: Attributes ??
                //$vars['form'] = $app['form']->renderFormElements('form_edit', $viewDefinition->getFormElementDefinitions(), $record->getProperties(), $record->getAttributes());
                $vars['form'] = $app['form']->renderFormElements(
                    'form_edit',
                    $viewDefinition->getFormElementDefinitions(),
                    $record->getProperties(),
                    []
                );

                $app['layout']->addJsFile('editrecord.js');   
                return $app->renderPage('editrecord.twig', $vars);
            } else {
                $vars['id'] = $recordId;

                return $app->renderPage('record-not-found.twig', $vars);
            }
        }



        return $app->renderPage('forbidden.twig', $vars);
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
                    $url = $app['url_generator']->generate(
                        'addRecord',
                        array('contentTypeAccessHash' => $contentTypeAccessHash)
                    );
                    $response = array('success' => true, 'redirect' => $url);

                    return new JsonResponse($response);
                }

                if ($list) {
                    $url = $app['url_generator']->generate(
                        'listRecords',
                        array(
                            'contentTypeAccessHash' => $contentTypeAccessHash,
                            'page' => $app['context']->getCurrentListingPage(),
                        )
                    );
                    $response = array('success' => true, 'redirect' => $url);

                    return new JsonResponse($response);
                }

                $url = $app['url_generator']->generate(
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
                    $app['url_generator']->generate(
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

                    $vars['links']['transfer'] = $app['url_generator']->generate(
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
                        $app['url_generator']->generate(
                            'editRecord',
                            array('contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId)
                        ), 303
                    );
                }
            }
        }

        $app['context']->addErrorMessage('Could not load source record.');

        return new RedirectResponse(
            $app['url_generator']->generate(
                'listRecords',
                array(
                    'contentTypeAccessHash' => $contentTypeAccessHash,
                    'page' => $app['context']->getCurrentListingPage(),
                )
            ), 303
        );
    }
}
