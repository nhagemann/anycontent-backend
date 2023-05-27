<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Config;

use AnyContent\Client\Config;
use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

use CMDL\ConfigTypeDefinition;
use CMDL\ContentTypeDefinition;
use CMDL\ViewDefinition;

use AnyContent\Client\Repository;
use AnyContent\Client\Record;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class Controller
{

    public static function editConfig(Application $app, $configTypeAccessHash)
    {
        $vars = array();

        $vars['menu_mainmenu'] = $app['menus']->renderMainMenu();

        /** @var Repository $repository */
        $repository = $app['repos']->getRepositoryByConfigTypeAccessHash($configTypeAccessHash);

        /** @var ConfigTypeDefinition $configTypeDefinition */
        $configTypeDefinition = $app['repos']->getConfigTypeDefinitionByConfigTypeAccessHash($configTypeAccessHash);

        if ($repository)
        {
            $vars['repository']          = $repository;
            $repositoryAccessHash        = $app['repos']->getRepositoryAccessHash($repository);
            $vars['links']['repository'] = $app['url_generator']->generate('indexRepository', array( 'repositoryAccessHash' => $repositoryAccessHash ));


            $app['context']->setCurrentRepository($repository);
            $app['context']->setCurrentConfigType($configTypeDefinition);

            $app['form']->setDataTypeDefinition($configTypeDefinition);


            $repository->selectWorkspace($app['context']->getCurrentWorkspace());
            $repository->selectLanguage($app['context']->getCurrentLanguage());
            $repository->setTimeShift($app['context']->getCurrentTimeShift());
            $repository->selectView('default');

            /** @var Config $record */
            $record = $repository->getConfig($configTypeDefinition->getName());

            if ($record)
            {
                $app['context']->setCurrentConfig($record);
                $vars['record'] = $record;

                $vars['definition'] = $configTypeDefinition;

                /* @var ViewDefinition */
                $viewDefinition = $configTypeDefinition->getViewDefinition('default');

                $vars['form'] = $app['form']->renderFormElements('form_edit', $viewDefinition->getFormElementDefinitions(), $record->getProperties());

                $vars['links']['timeshift']  = $app['url_generator']->generate('timeShiftEditConfig', array( 'configTypeAccessHash' => $configTypeAccessHash ));
                $vars['links']['workspaces'] = $app['url_generator']->generate('changeWorkspaceEditConfig', array( 'configTypeAccessHash' => $configTypeAccessHash ));
                $vars['links']['languages']  = $app['url_generator']->generate('changeLanguageEditConfig', array( 'configTypeAccessHash' => $configTypeAccessHash ));
                $vars['links']['revisions']  = $app['url_generator']->generate('listConfigRevisions', array( 'configTypeAccessHash' => $configTypeAccessHash,'workspace'=>$app['context']->getCurrentWorkspace(),'language'=>$app['context']->getCurrentLanguage() ));

                $app['layout']->addJsFile('editrecord.js');
                return $app->renderPage('editconfig.twig', $vars);

            }
        }

        return $app->renderPage('config-not-found.twig', $vars);

    }


    public static function saveConfig(Application $app, Request $request, $configTypeAccessHash)
    {
        $hidden = $request->get('$hidden');

        /** @var Repository $repository */
        $repository = $app['repos']->getRepositoryByConfigTypeAccessHash($configTypeAccessHash);

        /** @var ConfigTypeDefinition $configTypeDefinition */
        $configTypeDefinition = $app['repos']->getConfigTypeDefinitionByConfigTypeAccessHash($configTypeAccessHash);

        if ($repository)
        {
            $app['context']->setCurrentRepository($repository);
            $app['context']->setCurrentConfigType($configTypeDefinition);

            $app['form']->setDataTypeDefinition($configTypeDefinition);

            $app['context']->setCurrentWorkspace($hidden['workspace']);
            $app['context']->setCurrentLanguage($hidden['language']);


            $repository->selectWorkspace($app['context']->getCurrentWorkspace());
            $repository->selectLanguage($app['context']->getCurrentLanguage());
            $repository->setTimeShift($app['context']->getCurrentTimeShift());
            $repository->selectView('default');


            /** @var Config $record */
            $record = $repository->getConfig($configTypeDefinition->getName());

            if ($record)
            {
                $app['context']->setCurrentConfig($record);
                /* @var ViewDefinition */
                $viewDefinition = $configTypeDefinition->getViewDefinition('default');

                $values = $app['form']->extractFormElementValuesFromPostRequest($request, $viewDefinition->getFormElementDefinitions(), $record->getProperties());

                foreach ($values as $property => $value)
                {
                    $record->setProperty($property, $value);
                }

                $repository->selectWorkspace($app['context']->getCurrentWorkspace());
                $repository->selectLanguage($app['context']->getCurrentLanguage());

                $result = $repository->saveConfig($record);

                $app['context']->resetTimeShift();
                if ($result)
                {
                    $app['context']->addSuccessMessage('Config saved.');
                }
                else
                {
                    $app['context']->addErrorMessage('Could not save config.');

                }

                $url      = $app['url_generator']->generate('editConfig', array( 'configTypeAccessHash' => $configTypeAccessHash ));
                $response = array( 'success' => true, 'redirect' => $url );

                return new JsonResponse($response);
            }
            else
            {
                $response = array( 'success' => false, 'message' => 'Config not found.' );

                return new JsonResponse($response);
            }
        }
    }

}