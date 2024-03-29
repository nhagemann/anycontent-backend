<?php

namespace AnyContent\Backend\Modules\Start\Controller;

use AnyContent\Backend\Controller\AbstractAnyContentBackendController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ANYCONTENT')]
class StartController extends AbstractAnyContentBackendController
{
    #[Route('/', name:'anycontent_start', methods: ['GET'])]
    public function start(): Response
    {
        $vars = [];

        $vars['menu_mainmenu'] = $this->menuManager->renderMainMenu();

        $items = [];
        foreach ($this->repositoryManager->listRepositories() as $repositoryName => $repositoryItem) {
            $items[] = $this->extractRepositoryInfos($repositoryName, $repositoryItem, false);
        }

        $vars['repositories'] = $items;

        return $this->render('@AnyContentBackend/Start/index.html.twig', $vars);
    }

    #[Route('/repository/{repositoryAccessHash}', name:'anycontent_repository', methods: ['GET'])]
    public function repository(string $repositoryAccessHash)
    {
        $vars = [];
        $vars['menu_mainmenu'] = $this->menuManager->renderMainMenu();

        foreach ($this->repositoryManager->listRepositories() as $repositoryName => $repositoryItem) {
            if ($repositoryAccessHash == $repositoryItem['accessHash']) {
                $item               = $this->extractRepositoryInfos($repositoryName, $repositoryItem, true);
                $vars['repository'] = $item;
            }
        }

        return $this->render('@AnyContentBackend/Start/index-repository.html.twig', $vars);
    }

    private function extractRepositoryInfos($repositoryName, $repositoryItem, $definition = false)
    {
        $repository = $this->repositoryManager->getRepositoryByRepositoryAccessHash($repositoryItem['accessHash']);

        $item          = [];
        $item['title'] = $repositoryItem['title'];
        $item['url']   = '';//$repository->getPublicUrl();
        $item['link']  = $this->generateUrl('anycontent_repository', ['repositoryAccessHash' => $repositoryItem['accessHash']]);
        $item['files'] = false;

        $item['content_types'] = [];

        foreach ($this->repositoryManager->listContentTypes($repositoryName) as $contentTypeName => $contentTypeItem) {
            //$info = array( 'name' => $contentTypeItem['name'], 'title' => $contentTypeItem['title'], 'link' => '', 'page' => 1  );
            $info = ['name' => $contentTypeItem['name'], 'title' => $contentTypeItem['title'], 'link' => $this->generateUrl('anycontent_records', ['contentTypeAccessHash' => $contentTypeItem['accessHash'], 'page' => 1])];

            if ($definition) {
                $info['definition'] = $repository->getContentTypeDefinition($contentTypeName);
            }

            $item['content_types'][] = $info;
        }

        $item['config_types'] = [];

        foreach ($this->repositoryManager->listConfigTypes($repositoryName) as $configTypeName => $configTypeItem) {
            $info = ['name' => $configTypeItem['name'], 'title' => $configTypeItem['title'], 'link' => $this->generateUrl('anycontent_config_edit', ['configTypeAccessHash' => $configTypeItem['accessHash']])];

            if ($definition) {
                $info['definition'] = $repository->getConfigTypeDefinition($configTypeName);
            }

            $item['config_types'][] = $info;
        }

        if ($this->repositoryManager->hasFiles($repositoryName)) {
            $item['files'] = $this->generateUrl('anycontent_files', ['repositoryAccessHash' => $repositoryItem['accessHash'], 'path' => '']);
        }

        return $item;
    }
}
