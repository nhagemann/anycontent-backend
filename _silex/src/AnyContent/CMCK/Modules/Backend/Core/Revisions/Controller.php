<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Revisions;

use AnyContent\Client\AbstractRecord;
use AnyContent\Client\Config;
use AnyContent\Client\ContentFilter;
use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

use AnyContent\CMCK\Modules\Backend\Core\Edit\EditRecordSaveEvent;
use AnyContent\CMCK\Modules\Backend\Core\User\UserManager;
use CMDL\ConfigTypeDefinition;
use CMDL\ContentTypeDefinition;
use CMDL\DataTypeDefinition;
use CMDL\ViewDefinition;

use AnyContent\Client\Repository;
use AnyContent\Client\Record;

use AnyContent\CMCK\Modules\Backend\Core\Edit\FormManager;

use CMDL\FormElementDefinition;

use cogpowered\FineDiff\Diff;

use cogpowered\FineDiff\Granularity\Word;
use Symfony\Component\Yaml\Yaml;

class Controller
{

    public static function listRecordRevisions(Application $app, $contentTypeAccessHash, $recordId, $workspace, $language)
    {
        /** @var UserManager $user */
        $user = $app['user'];

        $vars = array();

        $vars['menu_mainmenu']   = $app['menus']->renderMainMenu();
        $vars['links']['search'] = $app['url_generator']->generate(
            'listRecords',
            array('contentTypeAccessHash' => $contentTypeAccessHash, 'page' => 1, 's' => 'name')
        );

        /** @var Repository $repository */
        $repository = $app['repos']->getRepositoryByContentTypeAccessHash($contentTypeAccessHash);

        if ($repository) {
            $vars['id']                   = $recordId;
            $vars['repository']           = $repository;
            $repositoryAccessHash         = $app['repos']->getRepositoryAccessHash($repository);
            $vars['links']['repository']  = $app['url_generator']->generate(
                'indexRepository',
                array('repositoryAccessHash' => $repositoryAccessHash)
            );
            $vars['links']['listRecords'] = $app['url_generator']->generate(
                'listRecords',
                array(
                    'contentTypeAccessHash' => $contentTypeAccessHash,
                    'page'                  => 1,
                    'workspace'             => $app['context']->getCurrentWorkspace(),
                    'language'              => $app['context']->getCurrentLanguage(),
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

            $buttons      = array();
            $buttons[100] = array(
                'label'     => 'List Records',
                'url'       => $app['url_generator']->generate(
                    'listRecords',
                    array(
                        'contentTypeAccessHash' => $contentTypeAccessHash,
                        'page'                  => $app['context']->getCurrentListingPage(),
                        'workspace'             => $app['context']->getCurrentWorkspace(),
                        'language'              => $app['context']->getCurrentLanguage(),
                    )
                ),
                'glyphicon' => 'glyphicon-list',
            );

            $vars['buttons'] = $app['menus']->renderButtonGroup($buttons);

            $vars['links']['timeshift']  = $app['url_generator']->generate(
                'timeShiftEditRecord',
                array('contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId)
            );
            $vars['links']['workspaces'] = $app['url_generator']->generate(
                'changeWorkspaceEditRecord',
                array('contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId)
            );
            $vars['links']['languages']  = $app['url_generator']->generate(
                'changeLanguageEditRecord',
                array('contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId)
            );

            /** @var ContentTypeDefinition $contentTypeDefinition */
            $contentTypeDefinition = $repository->getContentTypeDefinition();

            $vars['definition'] = $contentTypeDefinition;

            $revisions = $repository->getRevisionsOfRecord($recordId);
            if ($revisions) {
                $properties = self::getPropertiesForDiff($contentTypeDefinition);

                /** @var Record|false $compare */
                $compare = false;

                foreach ($revisions as $revision) {

                    if ($revision->isADeletedRevision()) {
                        $revision->setProperties([]);
                    }

                    if ($compare) {

                        $item = ['record' => $compare, 'diff' => self::diffRecords($compare, $revision, $properties)];

                        $item ['username'] = $compare->getLastChangeUserInfo()->getName();
                        $item ['gravatar'] = md5($compare->getLastChangeUserInfo()->getUsername());
                        $item ['date']     = $compare->getLastChangeUserInfo()->getTimestamp();
                        $item ['deleted']  = $compare->isADeletedRevision();

                        $item ['links']['edit'] = $app['url_generator']->generate(
                            'timeShiftIntoRecordRevision',
                            [
                                'contentTypeAccessHash' => $contentTypeAccessHash,
                                'recordId'              => $compare->getId(),
                                'timeshift'             => $compare->getLastChangeUserInfo()->getTimestamp(),
                                'workspace'             => $app['context']->getCurrentWorkspace(),
                                'language'              => $app['context']->getCurrentLanguage(),
                            ]);

                        $item ['links']['recreate'] = $app['url_generator']->generate(
                            'recreateRecordRevision',
                            [
                                'contentTypeAccessHash' => $contentTypeAccessHash,
                                'recordId'              => $compare->getId(),
                                'timeshift'             => $compare->getLastChangeUserInfo()->getTimestamp(),
                                'workspace'             => $app['context']->getCurrentWorkspace(),
                                'language'              => $app['context']->getCurrentLanguage(),
                            ]);
                        $items[]                    = $item;
                    }
                    else {
                        $vars['record'] = $revision;
                        $app['context']->setCurrentRecord($revision);
                    }
                    if ($revision === end($revisions)) {
                        $item                       = ['record' => $revision, 'diff' => self::diffRecords($revision, null, $properties)];
                        $item ['username']          = $revision->getLastChangeUserInfo()->getName();
                        $item ['gravatar']          = md5($revision->getLastChangeUserInfo()->getUsername());
                        $item ['date']              = $revision->getLastChangeUserInfo()->getTimestamp();
                        $item ['deleted']           = $revision->isADeletedRevision();
                        $item ['links']['edit']     = $app['url_generator']->generate(
                            'timeShiftIntoRecordRevision',
                            [
                                'contentTypeAccessHash' => $contentTypeAccessHash,
                                'recordId'              => $revision->getId(),
                                'timeshift'             => $revision->getLastChangeUserInfo()->getTimestamp(),
                                'workspace'             => $app['context']->getCurrentWorkspace(),
                                'language'              => $app['context']->getCurrentLanguage(),
                            ]);
                        $item ['links']['recreate'] = $app['url_generator']->generate(
                            'recreateRecordRevision',
                            [
                                'contentTypeAccessHash' => $contentTypeAccessHash,
                                'recordId'              => $revision->getId(),
                                'timeshift'             => $revision->getLastChangeUserInfo()->getTimestamp(),
                                'workspace'             => $app['context']->getCurrentWorkspace(),
                                'language'              => $app['context']->getCurrentLanguage(),
                            ]);

                        $items[] = $item;
                    }

                    $compare = $revision;
                }

                $vars['revisions'] = $items;

                return $app->renderPage('editrecordrevision.twig', $vars);
            }

            return $app->renderPage('forbidden.twig', $vars);
        }
    }

    public static function listConfigRevisions(Application $app, $configTypeAccessHash, $workspace, $language)
    {
        /** @var UserManager $user */
        $user = $app['user'];

        $vars = array();

        $vars['menu_mainmenu'] = $app['menus']->renderMainMenu();

        /** @var Repository $repository */
        $repository = $app['repos']->getRepositoryByConfigTypeAccessHash($configTypeAccessHash);

        if ($repository) {
            $vars['repository']          = $repository;
            $repositoryAccessHash        = $app['repos']->getRepositoryAccessHash($repository);
            $vars['links']['repository'] = $app['url_generator']->generate(
                'indexRepository',
                array('repositoryAccessHash' => $repositoryAccessHash)
            );

            /** @var ConfigTypeDefinition $configTypeDefinition */
            $configTypeDefinition = $app['repos']->getConfigTypeDefinitionByConfigTypeAccessHash($configTypeAccessHash);

            $app['context']->setCurrentRepository($repository);
            $app['context']->setCurrentConfigType($configTypeDefinition);

            $app['form']->setDataTypeDefinition($configTypeDefinition);

            if ($workspace != null && $configTypeDefinition->hasWorkspace($workspace)) {
                $app['context']->setCurrentWorkspace($workspace);
            }
            if ($language != null && $configTypeDefinition->hasLanguage($language)) {
                $app['context']->setCurrentLanguage($language);
            }

            $repository->selectWorkspace($app['context']->getCurrentWorkspace());
            $repository->selectLanguage($app['context']->getCurrentLanguage());
            $repository->setTimeShift($app['context']->getCurrentTimeShift());
            $repository->selectView('default');

            $buttons = array();

            $vars['buttons'] = $app['menus']->renderButtonGroup($buttons);

            $vars['links']['timeshift']  = $app['url_generator']->generate('timeShiftEditConfig', array('configTypeAccessHash' => $configTypeAccessHash));
            $vars['links']['workspaces'] = $app['url_generator']->generate('changeWorkspaceEditConfig', array('configTypeAccessHash' => $configTypeAccessHash));
            $vars['links']['languages']  = $app['url_generator']->generate('changeLanguageEditConfig', array('configTypeAccessHash' => $configTypeAccessHash));

            /** @var Config $record */
            $record = $repository->getConfig($configTypeDefinition->getName());

            if ($record) {

                $app['context']->setCurrentConfig($record);
                $vars['record'] = $record;

                $vars['definition'] = $configTypeDefinition;

                $revisions = $repository->getRevisionsOfConfig($configTypeDefinition->getName());

                $properties = self::getPropertiesForDiff($configTypeDefinition);

                /** @var Record|false $compare */
                $compare = false;
                foreach ($revisions as $revision) {

                    if ($compare) {
                        $item = ['record' => $compare, 'diff' => self::diffRecords($compare, $revision, $properties)];

                        $item ['username']          = $compare->getLastChangeUserInfo()->getName();
                        $item ['gravatar']          = md5($compare->getLastChangeUserInfo()->getUsername());
                        $item ['date']              = $compare->getLastChangeUserInfo()->getTimestamp();
                        $item ['deleted']           = false;
                        $item ['links']['edit']     = $app['url_generator']->generate(
                            'timeShiftIntoConfigRevision',
                            [
                                'configTypeAccessHash' => $configTypeAccessHash,
                                'timeshift'            => $compare->getLastChangeUserInfo()->getTimestamp(),
                                'workspace'            => $app['context']->getCurrentWorkspace(),
                                'language'             => $app['context']->getCurrentLanguage(),
                            ]);
                        $item ['links']['recreate'] = $app['url_generator']->generate(
                            'recreateConfigRevision',
                            [
                                'configTypeAccessHash' => $configTypeAccessHash,
                                'timeshift'            => $compare->getLastChangeUserInfo()->getTimestamp(),
                                'workspace'            => $app['context']->getCurrentWorkspace(),
                                'language'             => $app['context']->getCurrentLanguage(),
                            ]);
                        $items[]                    = $item;
                    }
                    if ($revision === end($revisions)) {
                        $item                       = ['record' => $revision, 'diff' => self::diffRecords($revision, null, $properties)];
                        $item ['username']          = $revision->getLastChangeUserInfo()->getName();
                        $item ['gravatar']          = md5($revision->getLastChangeUserInfo()->getUsername());
                        $item ['date']              = $revision->getLastChangeUserInfo()->getTimestamp();
                        $item ['deleted']           = false;
                        $item ['links']['edit']     = $app['url_generator']->generate(
                            'timeShiftIntoConfigRevision',
                            [
                                'configTypeAccessHash' => $configTypeAccessHash,
                                'timeshift'            => $revision->getLastChangeUserInfo()->getTimestamp(),
                                'workspace'            => $app['context']->getCurrentWorkspace(),
                                'language'             => $app['context']->getCurrentLanguage(),
                            ]);
                        $item ['links']['recreate'] = $app['url_generator']->generate(
                            'recreateConfigRevision',
                            [
                                'configTypeAccessHash' => $configTypeAccessHash,
                                'timeshift'            => $revision->getLastChangeUserInfo()->getTimestamp(),
                                'workspace'            => $app['context']->getCurrentWorkspace(),
                                'language'             => $app['context']->getCurrentLanguage(),
                            ]);
                        $items[]                    = $item;
                    }

                    $compare = $revision;
                }

                $vars['revisions'] = $items;

                return $app->renderPage('editrecordrevision.twig', $vars);
            }

            return $app->renderPage('forbidden.twig', $vars);
        }
    }

    public static function editRecordRevision(Application $app, $contentTypeAccessHash, $recordId, $workspace, $language, $timeshift)
    {
        $app['context']->setCurrentTimeShift($timeshift + 1);

        return $app->redirect($app['url_generator']->generate('editRecord', array('contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId, 'workspace' => $workspace, 'language' => $language)));
    }

    public static function editConfigRevision(Application $app, $configTypeAccessHash, $workspace, $language, $timeshift)
    {
        $app['context']->setCurrentTimeShift($timeshift + 1);

        return $app->redirect($app['url_generator']->generate('editConfig', array('configTypeAccessHash' => $configTypeAccessHash, 'workspace' => $workspace, 'language' => $language)));
    }

    public static function recreateRecordRevision(Application $app, $contentTypeAccessHash, $recordId, $workspace, $language, $timeshift)
    {

        /** @var Repository $repository */
        $repository = $app['repos']->getRepositoryByContentTypeAccessHash($contentTypeAccessHash);

        if ($repository) {
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

            $app['context']->setCurrentTimeShift($timeshift + 1);

            $repository->selectWorkspace($app['context']->getCurrentWorkspace());
            $repository->selectLanguage($app['context']->getCurrentLanguage());
            $repository->setTimeShift($app['context']->getCurrentTimeShift());
            $repository->selectView('default');

            /** @var Record $record */
            $record = $repository->getRecord($recordId);

            if ($record) {
                $revisionNumber = $record->getRevision();
                $repository->saveRecord($record);

                $event = new EditRecordSaveEvent($app, $record);
                $app['dispatcher']->dispatch(\AnyContent\CMCK\Modules\Backend\Core\Edit\Module::EVENT_EDIT_RECORD_BEFORE_UPDATE, $event);

                if ($event->hasInfoMessage()) {
                    $app['context']->addInfoMessage($event->getInfoMessage());
                }

                if ($event->hasAlertMessage()) {
                    $app['context']->addAlertMessage($event->getAlertMessage());
                }

                $app['context']->addAlertMessage('Created new revision based on existing revision ' . $revisionNumber . '.');

                $app['context']->resetTimeShift();

                return $app->redirect($app['url_generator']->generate('editRecord', array('contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId, 'workspace' => $workspace, 'language' => $language)));
            }
        }

        return $app->renderPage('forbidden.twig', []);
    }

    public static function recreateConfigRevision(Application $app, $configTypeAccessHash, $workspace, $language, $timeshift)
    {

        /** @var Repository $repository */
        $repository = $app['repos']->getRepositoryByConfigTypeAccessHash($configTypeAccessHash);

        if ($repository) {
            $app['context']->setCurrentRepository($repository);

            /** @var ConfigTypeDefinition $configTypeDefinition */
            $configTypeDefinition = $app['repos']->getConfigTypeDefinitionByConfigTypeAccessHash($configTypeAccessHash);

            $app['context']->setCurrentRepository($repository);
            $app['context']->setCurrentConfigType($configTypeDefinition);

            if ($workspace != null && $configTypeDefinition->hasWorkspace($workspace)) {
                $app['context']->setCurrentWorkspace($workspace);
            }
            if ($language != null && $configTypeDefinition->hasLanguage($language)) {
                $app['context']->setCurrentLanguage($language);
            }

            $app['context']->setCurrentTimeShift($timeshift + 1);

            $repository->selectWorkspace($app['context']->getCurrentWorkspace());
            $repository->selectLanguage($app['context']->getCurrentLanguage());
            $repository->setTimeShift($app['context']->getCurrentTimeShift());
            $repository->selectView('default');

            /** @var Config $record */
            $record = $repository->getConfig($configTypeDefinition->getName());

            if ($record) {
                $revisionNumber = $record->getRevision();
                $repository->saveConfig($record);

                $app['context']->addAlertMessage('Created new revision based on existing revision ' . $revisionNumber . '.');

                $app['context']->resetTimeShift();

                return $app->redirect($app['url_generator']->generate('editConfig', array('configTypeAccessHash' => $configTypeAccessHash, 'workspace' => $workspace, 'language' => $language)));
            }
        }

        return $app->renderPage('forbidden.twig', []);
    }

    protected static function getPropertiesForDiff(DataTypeDefinition $dataTypeDefinition)
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

    /**
     * @param AbstractRecord      $record1
     * @param AbstractRecord|null $record2
     * @param                     $properties
     */
    protected static function diffRecords(AbstractRecord $record1, $record2 = null, $properties)
    {
        $granularity = new Word();
        $differ      = new Diff($granularity);
        $diff        = [];
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

                $html   = $differ->render($value2, $value1);
                $diff[] = ['label' => $label, 'html' => $html];
            }
        }
        if (count($diff) > 0) {
            return $diff;
        }
    }
}