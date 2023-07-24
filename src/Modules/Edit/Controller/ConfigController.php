<?php

namespace AnyContent\Backend\Modules\Edit\Controller;

use AnyContent\Backend\Controller\AbstractAnyContentBackendController;
use AnyContent\Client\Config;
use AnyContent\Client\Repository;
use CMDL\ConfigTypeDefinition;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ANYCONTENT')]
class ConfigController extends AbstractAnyContentBackendController
{
    #[Route('/config/edit/{configTypeAccessHash}/{workspace}/{language}', 'anycontent_config_edit', methods: ['GET'])]
    public function editConfig(string $configTypeAccessHash, $workspace = null, $language = null): Response
    {
        $repository = $this->updateContextByConfigTypeAccessHash($configTypeAccessHash, $workspace, $language);
        $configTypeDefinition = $this->repositoryManager->getConfigTypeDefinitionByConfigTypeAccessHash($configTypeAccessHash);
        $repositoryAccessHash = $this->repositoryManager->getRepositoryAccessHash($repository);

        $vars = [];
        $vars['repository'] = $repository;
        $vars['definition'] = $configTypeDefinition;

        $vars['links']['repository'] = $this->generateUrl('anycontent_repository', ['repositoryAccessHash' => $repositoryAccessHash]);
        $vars['links']['timeshift']  = $this->generateUrl('anycontent_timeshift_config_edit', ['configTypeAccessHash' => $configTypeAccessHash]);

        //$vars['menu_mainmenu'] = $app['menus']->renderMainMenu();

//
//            $vars['repository']          = $repository;
//            $repositoryAccessHash        = $app['repos']->getRepositoryAccessHash($repository);
//            $vars['links']['repository'] = $app['url_generator']->generate('indexRepository', array( 'repositoryAccessHash' => $repositoryAccessHash ));


//            $this->contextManager->setCurrentRepository($repository);
//            $this->contextManager->setCurrentConfigType($configTypeDefinition);
//
//            $this->formManager->setDataTypeDefinition($configTypeDefinition);


//            $repository->selectWorkspace($this->contextManager->getCurrentWorkspace());
//            $repository->selectLanguage($this->contextManager->getCurrentLanguage());
//            $repository->setTimeShift($this->contextManager->getCurrentTimeShift());
//            $repository->selectView('default');

            /** @var Config $record */
            $record = $repository->getConfig($configTypeDefinition->getName());
            $record->setRepository($repository);

            $this->contextManager->setCurrentConfig($record);
             $vars['record'] = $record;

                //$vars['definition'] = $configTypeDefinition;

                /* @var ViewDefinition */
                $viewDefinition = $configTypeDefinition->getViewDefinition('default');

                $vars['form'] = $this->formManager->renderFormElements('form_edit', $viewDefinition->getFormElementDefinitions(), $record->getProperties());

                //$vars['links']['timeshift']  = $this->generateUrl('timeShiftEditConfig', array( 'configTypeAccessHash' => $configTypeAccessHash ));
                $vars['links']['workspaces'] = $this->generateUrl('anycontent_config_edit_change_workspace', ['configTypeAccessHash' => $configTypeAccessHash]);
                $vars['links']['languages']  = $this->generateUrl('anycontent_config_edit_change_language', ['configTypeAccessHash' => $configTypeAccessHash]);
                $vars['links']['revisions']  = $this->generateUrl('anycontent_config_revisions', ['configTypeAccessHash' => $configTypeAccessHash, 'workspace' => $this->contextManager->getCurrentWorkspace(), 'language' => $this->contextManager->getCurrentLanguage()]);

                //$app['layout']->addJsFile('editrecord.js');
                //return $app->renderPage('editconfig.twig', $vars);

        return $this->render('@AnyContentBackend/Content/editconfig.html.twig', $vars);
    }

    #[Route('/config/edit/{configTypeAccessHash}/{workspace}/{language}', 'anycontent_config_save', methods: ['POST'])]
    public function saveConfig(Request $request, $configTypeAccessHash, $workspace = null, $language = null)
    {
        $hidden = $request->get('$hidden');

        /** @var Repository $repository */
        $repository = $this->repositoryManager->getRepositoryByConfigTypeAccessHash($configTypeAccessHash);

        /** @var ConfigTypeDefinition $configTypeDefinition */
        $configTypeDefinition = $this->repositoryManager->getConfigTypeDefinitionByConfigTypeAccessHash($configTypeAccessHash);

        if ($repository) {
            $this->contextManager->setCurrentRepository($repository);
            $this->contextManager->setCurrentConfigType($configTypeDefinition);

            $this->formManager->setDataTypeDefinition($configTypeDefinition);

            $this->contextManager->setCurrentWorkspace($hidden['workspace']);
            $this->contextManager->setCurrentLanguage($hidden['language']);

            $repository->selectWorkspace($this->contextManager->getCurrentWorkspace());
            $repository->selectLanguage($this->contextManager->getCurrentLanguage());
            $repository->setTimeShift($this->contextManager->getCurrentTimeShift());
            $repository->selectView('default');

            /** @var Config $record */
            $record = $repository->getConfig($configTypeDefinition->getName());

            if ($record) {
                $this->contextManager->setCurrentConfig($record);

                /* @var ViewDefinition */
                $viewDefinition = $configTypeDefinition->getViewDefinition('default');

                $values = $this->formManager->extractFormElementValuesFromPostRequest($request, $viewDefinition->getFormElementDefinitions(), $record->getProperties());

                foreach ($values as $property => $value) {
                    $record->setProperty($property, $value);
                }

                $repository->selectWorkspace($this->contextManager->getCurrentWorkspace());
                $repository->selectLanguage($this->contextManager->getCurrentLanguage());

                $result = $repository->saveConfig($record);

                $this->contextManager->resetTimeShift();
                if ($result) {
                    $this->contextManager->addSuccessMessage('Config saved.');
                } else {
                    $this->contextManager->addErrorMessage('Could not save config.');
                }

                $url      = $this->generateUrl('anycontent_config_edit', ['configTypeAccessHash' => $configTypeAccessHash]);
                $response = ['success' => true, 'redirect' => $url];

                return new JsonResponse($response);
            } else {
                $response = ['success' => false, 'message' => 'Config not found.'];

                return new JsonResponse($response);
            }
        }
    }
}
