<?php

namespace AnyContent\Backend\Modules\Edit\Controller;

use AnyContent\Backend\Controller\AbstractAnyContentBackendController;
use AnyContent\Backend\Modules\Edit\Events\ConfigBeforeSaveEvent;
use AnyContent\Backend\Modules\Edit\Events\ConfigSavedEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ANYCONTENT')]
class ConfigController extends AbstractAnyContentBackendController
{
    #[Route('/config/edit/{configTypeAccessHash}/{workspace}/{language}', name:'anycontent_config_edit', methods: ['GET'])]
    public function editConfig(string $configTypeAccessHash, $workspace = null, $language = null): Response
    {
        $repository = $this->updateContextByConfigTypeAccessHash($configTypeAccessHash, $workspace, $language);
        $configTypeDefinition = $this->repositoryManager->getConfigTypeDefinitionByConfigTypeAccessHash($configTypeAccessHash);

        $record = $repository->getConfig($configTypeDefinition->getName());
        $record->setRepository($repository);
        $this->contextManager->setCurrentConfig($record);

        $vars = [];
        $this->addRepositoryLinks($vars, $repository);

        $vars['definition'] = $configTypeDefinition;
        $vars['record'] = $record;

        $viewDefinition = $configTypeDefinition->getViewDefinition();

        $vars['form'] = $this->formManager->renderFormElements('form_edit', $viewDefinition->getFormElementDefinitions(), $record->getProperties());

        // context links

        $vars['links']['timeshift'] = $this->generateUrl('anycontent_timeshift_config_edit', ['configTypeAccessHash' => $configTypeAccessHash]);
        $vars['links']['workspaces'] = $this->generateUrl('anycontent_config_edit_change_workspace', ['configTypeAccessHash' => $configTypeAccessHash]);
        $vars['links']['languages'] = $this->generateUrl('anycontent_config_edit_change_language', ['configTypeAccessHash' => $configTypeAccessHash]);
        $vars['links']['revisions'] = $this->generateUrl('anycontent_config_revisions', ['configTypeAccessHash' => $configTypeAccessHash, 'workspace' => $this->contextManager->getCurrentWorkspace(), 'language' => $this->contextManager->getCurrentLanguage()]);

        return $this->render('@AnyContentBackend/Content/editconfig.html.twig', $vars);
    }

    #[Route('/config/edit/{configTypeAccessHash}/{workspace}/{language}', name:'anycontent_config_save', methods: ['POST'])]
    public function saveConfig(Request $request, $configTypeAccessHash, $workspace = null, $language = null)
    {
        $hidden = $request->get('$hidden');

        $repository = $this->repositoryManager->getRepositoryByConfigTypeAccessHash($configTypeAccessHash);

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

            $record = $repository->getConfig($configTypeDefinition->getName());

            if ($record) {
                $this->contextManager->setCurrentConfig($record);

                $viewDefinition = $configTypeDefinition->getViewDefinition('default');

                $values = $this->formManager->extractFormElementValuesFromPostRequest($request, $viewDefinition->getFormElementDefinitions(), $record->getProperties());

                foreach ($values as $property => $value) {
                    $record->setProperty($property, $value);
                }

                $repository->selectWorkspace($this->contextManager->getCurrentWorkspace());
                $repository->selectLanguage($this->contextManager->getCurrentLanguage());

                $this->dispatcher->dispatch(new ConfigBeforeSaveEvent($record, 'edit'));

                $result = $repository->saveConfig($record);

                $this->dispatcher->dispatch(new ConfigSavedEvent($record, 'edit'));

                $this->contextManager->resetTimeShift();
                if ($result) {
                    $this->contextManager->addSuccessMessage('Config saved.');
                } else {
                    $this->contextManager->addErrorMessage('Could not save config.');
                }

                $url = $this->generateUrl('anycontent_config_edit', ['configTypeAccessHash' => $configTypeAccessHash]);
                $response = ['success' => true, 'redirect' => $url];

                return new JsonResponse($response);
            } else {
                $response = ['success' => false, 'message' => 'Config not found.'];

                return new JsonResponse($response);
            }
        }
    }
}
