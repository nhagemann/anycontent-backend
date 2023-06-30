<?php

namespace AnyContent\Backend\Modules\Edit\Controller;

use AnyContent\Backend\Controller\AbstractAnyContentBackendController;
use AnyContent\Backend\Modules\Edit\Events\RecordBeforeSaveEvent;
use AnyContent\Backend\Modules\Edit\Events\RecordSavedEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ANYCONTENT')]
class RecordsController extends AbstractAnyContentBackendController
{
    #[Route('/content/add/{contentTypeAccessHash}/{workspace}/{language}', 'anycontent_record_add', methods: ['GET'])]
    #[Route('/content/add/{contentTypeAccessHash}/{recordId}/{workspace}/{language}', 'anycontent_record_add_distinct', methods: ['GET'])]
    public function addRecord($contentTypeAccessHash, ?int $recordId = null, $workspace = null, $language = null)
    {
        return $this->editRecord($contentTypeAccessHash, $recordId, $workspace, $language, true);
    }

    #[Route('/content/edit/{contentTypeAccessHash}/{recordId}/{workspace}/{language}', 'anycontent_record_edit', methods: ['GET'])]
    public function editRecord($contentTypeAccessHash, ?int $recordId, $workspace = null, $language = null, bool $addRecord = false)
    {
        $repository = $this->updateContext($contentTypeAccessHash, $workspace, $language);
        $contentTypeDefinition = $repository->getContentTypeDefinition();
        $repositoryAccessHash = $this->repositoryManager->getRepositoryAccessHash($repository);

        $vars = [];
        $vars['repository'] = $repository;
        $vars['definition'] = $contentTypeDefinition;

        // Links
        $vars['links']['edit'] = true;
        $vars['links']['search'] = $this->generateUrl('anycontent_records', ['contentTypeAccessHash' => $contentTypeAccessHash, 'page' => 1, 's' => 'name', 'workspace' => $this->contextManager->getCurrentWorkspace(), 'language' => $this->contextManager->getCurrentLanguage()]);
        $vars['links']['repository'] = $this->generateUrl('anycontent_repository', ['repositoryAccessHash' => $repositoryAccessHash]);
        $vars['links']['listRecords'] = $this->generateUrl('anycontent_records', ['contentTypeAccessHash' => $contentTypeAccessHash, 'page' => 1, 'workspace' => $this->contextManager->getCurrentWorkspace(), 'language' => $this->contextManager->getCurrentLanguage()]);
        $vars['links']['workspaces'] = $this->generateUrl('anycontent_record_add_change_workspace', ['contentTypeAccessHash' => $contentTypeAccessHash]);
        $vars['links']['languages'] = $this->generateUrl('anycontent_record_add_change_language', ['contentTypeAccessHash' => $contentTypeAccessHash]);

        // Buttons
        $buttons = $this->getButtons($contentTypeAccessHash, $contentTypeDefinition);
        $vars['buttons'] = $this->menuManager->renderButtonGroup($buttons);

        // Save Operation
        $saveOperation = $this->contextManager->getCurrentSaveOperation();
        $vars['save_operation'] = key($saveOperation);
        $vars['save_operation_title'] = array_shift($saveOperation);

        $viewDefinition = $contentTypeDefinition->getInsertViewDefinition();
        $properties = [];
        foreach ($viewDefinition->getFormElementDefinitions() as $formElementDefinition) {
                    $properties[$formElementDefinition->getName()] = $formElementDefinition->getDefaultValue();
        }

        // Try to fetch record by id - if given
        $record = false;
        if ($addRecord == false) {
            $record = $repository->getRecord($recordId);
            if (!$record) {
                $vars['id'] = $recordId;
                $vars['links']['addrecordversion'] = $this->generateUrl(
                    'anycontent_record_add_distinct',
                    ['contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId, 'workspace' => $this->contextManager->getCurrentWorkspace(), 'language' => $this->contextManager->getCurrentLanguage()]
                );

                return $this->render('@AnyContentBackend/Content/record-not-found.html.twig', $vars);
            }
            $this->contextManager->setCurrentRecord($record);
        }
        $vars['record'] = $record;

        if ($record) {
            $viewDefinition = $contentTypeDefinition->getViewDefinition('default');
            $properties = $record->getProperties();

            // Adjust Links
            $vars['links']['workspaces'] = $this->generateUrl(
                'anycontent_record_edit_change_workspace',
                ['contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId]
            );
            $vars['links']['languages'] = $this->generateUrl(
                'anycontent_record_edit_change_language',
                ['contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId]
            );
            $vars['links']['delete'] = $this->generateUrl(
                'anycontent_record_delete',
                [
                    'contentTypeAccessHash' => $contentTypeAccessHash,                    'recordId' => $recordId,                    'workspace' => $this->contextManager->getCurrentWorkspace(),                    'language' => $this->contextManager->getCurrentLanguage(),
                ]
            );
            $vars['links']['transfer'] = $this->generateUrl(
                'anycontent_record_transfer_modal',
                [
                    'contentTypeAccessHash' => $contentTypeAccessHash,                    'recordId' => $recordId,                    'workspace' => $this->contextManager->getCurrentWorkspace(),                    'language' => $this->contextManager->getCurrentLanguage(),
                ]
            );


            $vars['links']['revisions'] = $this->generateUrl(
                'anycontent_records_revisions',
                array(
                    'recordId'=>$recordId,
                    'contentTypeAccessHash' => $contentTypeAccessHash,
                    'workspace' => $this->contextManager->getCurrentWorkspace(),
                    'language' => $this->contextManager->getCurrentLanguage(),
                )
            );
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
    #[Route('/content/add/{contentTypeAccessHash}/{recordId}/{workspace}/{language}', 'anycontent_record_insert_distinct', methods: ['POST'])]
    //#[Route('/content/add/{contentTypeAccessHash}', 'anycontent_record_insert', methods: ['POST'])]
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
                $record = $repository->getRecord($recordId);

                if (!$record) { // if we don't have a record with the given id (there never was one, or it has been deleted), create a new one with the given id.
                    $record = $repository->createRecord('New Record');
                    $record->setId($recordId);
                }
            } else {
                $record = $repository->createRecord('New Record');
            }

            if ($record) {
                $this->contextManager->setCurrentRecord($record);

                $contentTypeDefinition = $repository->getContentTypeDefinition();

                if ($recordId) {
                    $viewDefinition = $contentTypeDefinition->getEditViewDefinition();
                } else {
                    $viewDefinition = $contentTypeDefinition->getInsertViewDefinition();
                }

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
                    $properties = [];

                    $formElementDefinitions = $viewDefinition->getFormElementDefinitions();
                    foreach ($formElementDefinitions as $formElementDefinition) {
                        if ($formElementDefinition->isUnique() && $record->getProperty($formElementDefinition->getName()) != '') {
                            $filter = $formElementDefinition->getName() . ' = ' . $record->getProperty(
                                $formElementDefinition->getName()
                            );
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
                        $response = [
                            'success' => false,
                            'message' => $message,
                            'properties' => array_keys($properties),
                        ];

                        return new JsonResponse($response);
                    }
                }

                if ($save) {
                    $this->dispatcher->dispatch(new RecordBeforeSaveEvent($record));

                    $recordId = $repository->saveRecord($record);

                    $this->dispatcher->dispatch(new RecordSavedEvent($record));

                    $this->contextManager->resetTimeShift();
                    if ($recordId) {
                        $this->contextManager->addSuccessMessage('Record saved.');
                    } else {
                        $response = [
                            'success' => false,
                            'error' => true,
                            'message' => 'Could not save record. Please check your input.',
                            'properties' => [''],
                        ];

                        return new JsonResponse($response);
                    }
                }
                if ($duplicate) {
                    $record->setName('Duplicate from ' . $record->getId() . ' - ' . $record->getName());
                    $record->setId(null);
                    $recordId = $repository->saveRecord($record);
                    $this->contextManager->resetTimeShift();
                }

                if ($insert) {
                    $url = $this->generateUrl(
                        'anycontent_record_add',
                        ['contentTypeAccessHash' => $contentTypeAccessHash]
                    );
                    $response = ['success' => true, 'redirect' => $url];

                    return new JsonResponse($response);
                }

                if ($list) {
                    $url = $this->generateUrl(
                        'anycontent_records',
                        [
                            'contentTypeAccessHash' => $contentTypeAccessHash,
                            'page' => $this->contextManager->getCurrentListingPage(),
                        ]
                    );
                    $response = ['success' => true, 'redirect' => $url];

                    return new JsonResponse($response);
                }

                $url = $this->generateUrl(
                    'anycontent_record_edit',
                    ['contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId, 'workspace' => $this->contextManager->getCurrentWorkspace(), 'language' => $this->contextManager->getCurrentLanguage()]
                );
                $response = ['success' => true, 'redirect' => $url];

                return new JsonResponse($response);
            } else {
                $response = ['success' => false, 'message' => 'Record not found.'];

                return new JsonResponse($response);
            }
        }

        $response = ['success' => false, 'message' => '403 Forbidden'];

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
                    [
                        'contentTypeAccessHash' => $contentTypeAccessHash,
                        'page' => $this->contextManager->getCurrentListingPage(),
                    ]
                ),
                303
            );
        }
    }

    #[Route('/modal/content/transfer/{contentTypeAccessHash}/{recordId}/{workspace}/{language}', 'anycontent_record_transfer_modal', methods: ['GET'])]
    public function transferRecordModal(
        $contentTypeAccessHash,
        $recordId,
        $workspace,
        $language
    ) {
        $repository = $this->updateContext($contentTypeAccessHash, $workspace, $language);

        $vars = [];
        $vars['record'] = false;

        $recordId = (int)$recordId;

        $record = $repository->getRecord($recordId);

        if ($record) {
            $this->contextManager->setCurrentRecord($record);
            $vars['record'] = $record;

            $contentTypeDefinition = $repository->getContentTypeDefinition();

            $vars['definition'] = $contentTypeDefinition;

            $records = [];

            $repository->setTimeShift(0);

            foreach ($repository->getRecords() as $record) {
                $records[$record->getID()] = '#' . $record->getID() . ' ' . $record->getName();
            }
            $vars['records'] = $records;

            $vars['links']['transfer'] = $this->generateUrl(
                'anycontent_record_transfer',
                [
                    'contentTypeAccessHash' => $contentTypeAccessHash,
                    'recordId' => $recordId,
                    "workspace" => $this->contextManager->getCurrentWorkspace(),
                    "language" => $this->contextManager->getCurrentLanguage(),
                ]
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
                    ['contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId,
                        'workspace' => $this->contextManager->getCurrentWorkspace(),
                        'language' => $this->contextManager->getCurrentLanguage()]
                ),
                303
            );
        }

        $this->contextManager->addErrorMessage('Could not load source record.');

        return new RedirectResponse(
            $this->generateUrl(
                'anycontent_records',
                [
                    'contentTypeAccessHash' => $contentTypeAccessHash,
                    'page' => $this->contextManager->getCurrentListingPage(),
                ]
            ),
            303
        );
    }
}
