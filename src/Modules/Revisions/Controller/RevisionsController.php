<?php

namespace AnyContent\Backend\Modules\Revisions\Controller;

use AnyContent\Backend\Controller\AbstractAnyContentBackendController;
use AnyContent\Client\AbstractRecord;
use AnyContent\Client\Config;
use AnyContent\Client\Record;
use AnyContent\Client\Repository;
use CMDL\ConfigTypeDefinition;
use CMDL\DataTypeDefinition;
use FineDiff\Diff;
use FineDiff\Granularity\Word;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Yaml\Yaml;

#[IsGranted('ROLE_ANYCONTENT')]
class RevisionsController extends AbstractAnyContentBackendController
{
    #[Route('/content/revisions/{contentTypeAccessHash}/{recordId}/{workspace}/{language}', 'anycontent_record_revisions')]
    public function listRecordRevisions($contentTypeAccessHash, $recordId, $workspace, $language)
    {
        $vars = [];

        // Menu

        $vars['menu_mainmenu'] = $this->menuManager->renderMainMenu();

        // Context

        $repository = $this->updateContext($contentTypeAccessHash, $workspace, $language);
        $vars['repository'] = $repository;

        $contentTypeDefinition = $repository->getContentTypeDefinition();
        $vars['definition'] = $contentTypeDefinition;

        // Links

        $this->addRepositoryLinks($vars, $repository, 1);

        $vars['links']['timeshift'] = $this->generateUrl('anycontent_timeshift_record_edit', ['contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId]);

        $vars['links']['workspaces'] = $this->generateUrl('anycontent_revisions_content_change_workspace', ['contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId]);
        $vars['links']['languages'] = $this->generateUrl('anycontent_revisions_content_change_language', ['contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId]);

        $vars['id'] = $recordId;
        $vars['repository'] = $repository;

        // Buttons
        $buttons = $this->getButtons($contentTypeAccessHash, $contentTypeDefinition);
        $vars['buttons'] = $this->menuManager->renderButtonGroup($buttons);

        $items = [];

        $revisions = $repository->getRevisionsOfRecord($recordId);
        if ($revisions) {
            $properties = $this->getPropertiesForDiff($contentTypeDefinition);

            /** @var Record|false $compare */
            $compare = false;

            foreach ($revisions as $revision) {
                if ($revision->isADeletedRevision()) {
                    $revision->setProperties([]);
                }

                if ($compare) {
                    $item = ['record' => $compare, 'diff' => $this->diffRecords($compare, $revision, $properties)];

                    $item ['username'] = $compare->getLastChangeUserInfo()->getName();
                    $item ['gravatar'] = md5($compare->getLastChangeUserInfo()->getUsername());
                    $item ['date'] = $compare->getLastChangeUserInfo()->getTimestamp();
                    $item ['deleted'] = $compare->isADeletedRevision();

                    $item ['links']['edit'] = $this->generateUrl(
                        'anycontent_revisions_record_timeshift',
                        [
                            'contentTypeAccessHash' => $contentTypeAccessHash,
                            'recordId' => $compare->getId(),
                            'timeshift' => $compare->getLastChangeUserInfo()->getTimestamp(),
                            'workspace' => $this->contextManager->getCurrentWorkspace(),
                            'language' => $this->contextManager->getCurrentLanguage(),
                        ]
                    );

                    $item ['links']['recreate'] = $this->generateUrl(
                        'anycontent_revisions_record_recreate',
                        [
                            'contentTypeAccessHash' => $contentTypeAccessHash,
                            'recordId' => $compare->getId(),
                            'timeshift' => $compare->getLastChangeUserInfo()->getTimestamp(),
                            'workspace' => $this->contextManager->getCurrentWorkspace(),
                            'language' => $this->contextManager->getCurrentLanguage(),
                        ]
                    );
                    $items[] = $item;
                } else {
                    $vars['record'] = $revision;
                    $this->contextManager->setCurrentRecord($revision);
                }
                if ($revision === end($revisions)) {
                    $item = ['record' => $revision, 'diff' => $this->diffRecords($revision, null, $properties)];
                    $item ['username'] = $revision->getLastChangeUserInfo()->getName();
                    $item ['gravatar'] = md5($revision->getLastChangeUserInfo()->getUsername());
                    $item ['date'] = $revision->getLastChangeUserInfo()->getTimestamp();
                    $item ['deleted'] = $revision->isADeletedRevision();
                    $item ['links']['edit'] = $this->generateUrl(
                        'anycontent_revisions_record_timeshift',
                        [
                            'contentTypeAccessHash' => $contentTypeAccessHash,
                            'recordId' => $revision->getId(),
                            'timeshift' => $revision->getLastChangeUserInfo()->getTimestamp(),
                            'workspace' => $this->contextManager->getCurrentWorkspace(),
                            'language' => $this->contextManager->getCurrentLanguage(),
                        ]
                    );
                    $item ['links']['recreate'] = $this->generateUrl(
                        'anycontent_revisions_record_recreate',
                        [
                            'contentTypeAccessHash' => $contentTypeAccessHash,
                            'recordId' => $revision->getId(),
                            'timeshift' => $revision->getLastChangeUserInfo()->getTimestamp(),
                            'workspace' => $this->contextManager->getCurrentWorkspace(),
                            'language' => $this->contextManager->getCurrentLanguage(),
                        ]
                    );

                    $items[] = $item;
                }

                $compare = $revision;
            }

            $vars['revisions'] = $items;

            return $this->render('@AnyContentBackend\Revisions\editrevision.html.twig', $vars);
        }

        return $this->render('@AnyContentBackend\Content\record-not-found.html.twig', $vars);
    }

    #[Route('/content/revisions/{configTypeAccessHash}/{workspace}/{language}', 'anycontent_config_revisions')]
    public function listConfigRevisions($configTypeAccessHash, $workspace, $language)
    {
        $vars = [];

        $vars['menu_mainmenu'] = $this->menuManager->renderMainMenu();

        /** @var Repository $repository */
        $repository = $this->repositoryManager->getRepositoryByConfigTypeAccessHash($configTypeAccessHash);

        if ($repository) {
            $vars['links']['timeshift'] = $this->generateUrl('anycontent_timeshift_config_edit', ['configTypeAccessHash' => $configTypeAccessHash]);

            $vars['repository'] = $repository;
            $repositoryAccessHash = $this->repositoryManager->getRepositoryAccessHash($repository);
            $vars['links']['repository'] = $this->generateUrl(
                'anycontent_repository',
                ['repositoryAccessHash' => $repositoryAccessHash]
            );

            /** @var ConfigTypeDefinition $configTypeDefinition */
            $configTypeDefinition = $this->repositoryManager->getConfigTypeDefinitionByConfigTypeAccessHash($configTypeAccessHash);

            $this->contextManager->setCurrentRepository($repository);
            $this->contextManager->setCurrentConfigType($configTypeDefinition);

            $this->formManager->setDataTypeDefinition($configTypeDefinition);

            if ($workspace != null && $configTypeDefinition->hasWorkspace($workspace)) {
                $this->contextManager->setCurrentWorkspace($workspace);
            }
            if ($language != null && $configTypeDefinition->hasLanguage($language)) {
                $this->contextManager->setCurrentLanguage($language);
            }

            $repository->selectWorkspace($this->contextManager->getCurrentWorkspace());
            $repository->selectLanguage($this->contextManager->getCurrentLanguage());
            $repository->setTimeShift($this->contextManager->getCurrentTimeShift());
            $repository->selectView('default');

            $buttons = [];

            $vars['buttons'] = $this->menuManager->renderButtonGroup($buttons);

            //$vars['links']['timeshift']  = $this->generateUrl('anycontent_config_revisions_timeshift', ['configTypeAccessHash' => $configTypeAccessHash, 'workspace'=>$workspace,'language'=>$language]);
            $vars['links']['workspaces'] = $this->generateUrl('anycontent_revisions_config_change_workspace', ['configTypeAccessHash' => $configTypeAccessHash]);
            $vars['links']['languages'] = $this->generateUrl('anycontent_revisions_config_change_language', ['configTypeAccessHash' => $configTypeAccessHash]);

            /** @var Config $record */
            $record = $repository->getConfig($configTypeDefinition->getName());

            $items = [];

            if ($record) {
                $this->contextManager->setCurrentConfig($record);
                $vars['record'] = $record;

                $vars['definition'] = $configTypeDefinition;

                $revisions = $repository->getRevisionsOfConfig($configTypeDefinition->getName());

                if (!$revisions) {
                    return $this->render('@AnyContentBackend\Content\record-not-found.html.twig', $vars);
                }

                $properties = $this->getPropertiesForDiff($configTypeDefinition);

                /** @var Record|false $compare */
                $compare = false;
                foreach ($revisions as $revision) {
                    if ($compare) {
                        $item = ['record' => $compare, 'diff' => $this->diffRecords($compare, $revision, $properties)];

                        $item ['username'] = $compare->getLastChangeUserInfo()->getName();
                        $item ['gravatar'] = md5($compare->getLastChangeUserInfo()->getUsername());
                        $item ['date'] = $compare->getLastChangeUserInfo()->getTimestamp();
                        $item ['deleted'] = false;
                        $item ['links']['edit'] = $this->generateUrl(
                            'anycontent_revisions_config_timeshift',
                            [
                                'configTypeAccessHash' => $configTypeAccessHash,
                                'timeshift' => $compare->getLastChangeUserInfo()->getTimestamp(),
                                'workspace' => $this->contextManager->getCurrentWorkspace(),
                                'language' => $this->contextManager->getCurrentLanguage(),
                            ]
                        );
                        $item ['links']['recreate'] = $this->generateUrl(
                            'anycontent_revisions_config_recreate',
                            [
                                'configTypeAccessHash' => $configTypeAccessHash,
                                'timeshift' => $compare->getLastChangeUserInfo()->getTimestamp(),
                                'workspace' => $this->contextManager->getCurrentWorkspace(),
                                'language' => $this->contextManager->getCurrentLanguage(),
                            ]
                        );
                        $items[] = $item;
                    }
                    if ($revision === end($revisions)) {
                        $item = ['record' => $revision, 'diff' => $this->diffRecords($revision, null, $properties)];
                        $item ['username'] = $revision->getLastChangeUserInfo()->getName();
                        $item ['gravatar'] = md5($revision->getLastChangeUserInfo()->getUsername());
                        $item ['date'] = $revision->getLastChangeUserInfo()->getTimestamp();
                        $item ['deleted'] = false;
                        $item ['links']['edit'] = $this->generateUrl(
                            'anycontent_revisions_config_timeshift',
                            [
                                'configTypeAccessHash' => $configTypeAccessHash,
                                'timeshift' => $revision->getLastChangeUserInfo()->getTimestamp(),
                                'workspace' => $this->contextManager->getCurrentWorkspace(),
                                'language' => $this->contextManager->getCurrentLanguage(),
                            ]
                        );
                        $item ['links']['recreate'] = $this->generateUrl(
                            'anycontent_revisions_config_recreate',
                            [
                                'configTypeAccessHash' => $configTypeAccessHash,
                                'timeshift' => $revision->getLastChangeUserInfo()->getTimestamp(),
                                'workspace' => $this->contextManager->getCurrentWorkspace(),
                                'language' => $this->contextManager->getCurrentLanguage(),
                            ]
                        );
                        $items[] = $item;
                    }

                    $compare = $revision;
                }

                $vars['revisions'] = $items;

                return $this->render('@AnyContentBackend\Revisions\editrevision.html.twig', $vars);
            }

            return $this->render('@AnyContentBackend\Content\record-not-found.html.twig', $vars);
        }
    }

    #[Route('/content/revisions/{contentTypeAccessHash}/{recordId}/{workspace}/{language}/edit/{timeshift}', 'anycontent_revisions_record_timeshift')]
    public function editRecordRevision($contentTypeAccessHash, $recordId, $workspace, $language, $timeshift)
    {
        $this->contextManager->setCurrentTimeShift($timeshift + 1);

        return $this->redirect($this->generateUrl('anycontent_record_edit', ['contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId, 'workspace' => $workspace, 'language' => $language]));
    }

    #[Route('/config/revisions/{configTypeAccessHash}/{workspace}/{language}/edit/{timeshift}', 'anycontent_revisions_config_timeshift')]
    public function editConfigRevision($configTypeAccessHash, $workspace, $language, $timeshift)
    {
        $this->contextManager->setCurrentTimeShift($timeshift + 1);

        return $this->redirect($this->generateUrl('anycontent_config_edit', ['configTypeAccessHash' => $configTypeAccessHash, 'workspace' => $workspace, 'language' => $language]));
    }

    #[Route('/content/revisions/{contentTypeAccessHash}/{recordId}/{workspace}/{language}/recreate/{timeshift}', 'anycontent_revisions_record_recreate')]
    public function recreateRecordRevision($contentTypeAccessHash, $recordId, $workspace, $language, $timeshift)
    {
        /** @var Repository $repository */
        $repository = $this->repositoryManager->getRepositoryByContentTypeAccessHash($contentTypeAccessHash);

        if ($repository) {
            $this->contextManager->setCurrentRepository($repository);

            $contentTypeDefinition = $repository->getContentTypeDefinition();
            $this->contextManager->setCurrentContentType($contentTypeDefinition);
            $this->formManager->setDataTypeDefinition($contentTypeDefinition);

            if ($workspace != null && $contentTypeDefinition->hasWorkspace($workspace)) {
                $this->contextManager->setCurrentWorkspace($workspace);
            }
            if ($language != null && $contentTypeDefinition->hasLanguage($language)) {
                $this->contextManager->setCurrentLanguage($language);
            }

            $this->contextManager->setCurrentTimeShift($timeshift + 1);

            $repository->selectWorkspace($this->contextManager->getCurrentWorkspace());
            $repository->selectLanguage($this->contextManager->getCurrentLanguage());
            $repository->setTimeShift($this->contextManager->getCurrentTimeShift());
            $repository->selectView('default');

            /** @var Record $record */
            $record = $repository->getRecord($recordId);

            if ($record) {
                $revisionNumber = $record->getRevision();
                $repository->saveRecord($record);

                $this->contextManager->addAlertMessage('Created new revision based on existing revision ' . $revisionNumber . '.');

                $this->contextManager->resetTimeShift();

                return $this->redirect($this->generateUrl('anycontent_record_edit', ['contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId, 'workspace' => $workspace, 'language' => $language]));
            }
        }

        return $this->render('forbidden.twig', []);
    }

    #[Route('/config/revisions/{configTypeAccessHash}/{workspace}/{language}/recreate/{timeshift}', 'anycontent_revisions_config_recreate')]
    public function recreateConfigRevision($configTypeAccessHash, $workspace, $language, $timeshift)
    {
        /** @var Repository $repository */
        $repository = $this->repositoryManager->getRepositoryByConfigTypeAccessHash($configTypeAccessHash);

        if ($repository) {
            $this->contextManager->setCurrentRepository($repository);

            /** @var ConfigTypeDefinition $configTypeDefinition */
            $configTypeDefinition = $this->repositoryManager->getConfigTypeDefinitionByConfigTypeAccessHash($configTypeAccessHash);

            $this->contextManager->setCurrentRepository($repository);
            $this->contextManager->setCurrentConfigType($configTypeDefinition);

            if ($workspace != null && $configTypeDefinition->hasWorkspace($workspace)) {
                $this->contextManager->setCurrentWorkspace($workspace);
            }
            if ($language != null && $configTypeDefinition->hasLanguage($language)) {
                $this->contextManager->setCurrentLanguage($language);
            }

            $this->contextManager->setCurrentTimeShift($timeshift + 1);

            $repository->selectWorkspace($this->contextManager->getCurrentWorkspace());
            $repository->selectLanguage($this->contextManager->getCurrentLanguage());
            $repository->setTimeShift($this->contextManager->getCurrentTimeShift());
            $repository->selectView('default');

            /** @var Config $record */
            $record = $repository->getConfig($configTypeDefinition->getName());

            if ($record) {
                $revisionNumber = $record->getRevision();
                $repository->saveConfig($record);

                $this->contextManager->addAlertMessage('Created new revision based on existing revision ' . $revisionNumber . '.');

                $this->contextManager->resetTimeShift();

                return $this->redirect($this->generateUrl('anycontent_config_edit', ['configTypeAccessHash' => $configTypeAccessHash, 'workspace' => $workspace, 'language' => $language]));
            }
        }

        return $this->render('forbidden.twig', []);
    }

    protected function getPropertiesForDiff(DataTypeDefinition $dataTypeDefinition)
    {
        $properties = [];

        // First add properties from view definition with labels

        foreach ($dataTypeDefinition->getViewDefinition()->getFormElementDefinitions() as $formElementDefinition) {
            if ($formElementDefinition->getName()) {
                $properties[$formElementDefinition->getName()] = $formElementDefinition->getLabel();
            }
        }

        // Then add all available properties not yet added

        $properties = array_merge(array_combine($dataTypeDefinition->getProperties(), $dataTypeDefinition->getProperties()), $properties);

        return $properties;
    }

    protected function diffRecords(AbstractRecord $record1, ?AbstractRecord $record2, array $properties)
    {
        $granularity = new Word();
        $differ = new Diff($granularity);
        $diff = [];
        foreach ($properties as $property => $label) {
            $value1 = $record1->getProperty($property);
            $value2 = '';
            if ($record2) {
                $value2 = $record2->getProperty($property);
            }
            if ($value1 != $value2) {
                $jsontest = json_decode($value1, true);
                if (json_last_error() == JSON_ERROR_NONE && is_array($jsontest)) {
                    $value1 = Yaml::dump($jsontest, 4);
                    $value2 = Yaml::dump(json_decode($value2, true), 4);
                    if ($value2 == 'null') {
                        $value2 = '';
                    }
                }

                $html = $differ->render($value2, $value1);
                $diff[] = ['label' => $label, 'html' => $html];
            }
        }
        if (count($diff) > 0) {
            return $diff;
        }
    }
}
