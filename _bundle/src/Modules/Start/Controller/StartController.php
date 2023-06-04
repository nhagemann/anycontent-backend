<?php

namespace AnyContent\Backend\Modules\Start\Controller;


use AnyContent\Backend\Controller\AbstractAnyContentBackendController;
use AnyContent\Backend\Services\RepositoryManager;
use AnyContent\CMCK\Modules\Backend\Core\Application\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ANYCONTENT')]
class StartController extends AbstractAnyContentBackendController
{
    #[Route('/','anycontent_start')]
    public function start(): Response
    {
        $vars = [];

        $vars['menu_mainmenu'] = [];

        $items = array();
        foreach ($this->repositoryManager->listRepositories() as $repositoryName => $repositoryItem)
        {
            $items[] = $this->extractRepositoryInfos($repositoryName, $repositoryItem, false);
        }

        $vars['repositories'] = $items;

        return $this->render('@AnyContentBackend/Start/index.html.twig', $vars);
    }

    #[Route('/{repositoryAccessHash}','anycontent_repository')]
    public function repository(string $repositoryAccessHash)
    {

        $vars['menu_mainmenu'] = [];

       foreach ($this->repositoryManager->listRepositories() as $repositoryName => $repositoryItem)
        {
            if ($repositoryAccessHash == $repositoryItem['accessHash'])
            {
                $item               = $this->extractRepositoryInfos($repositoryName, $repositoryItem, true);
                $vars['repository'] = $item;
            }
        }

        return $this->render('@AnyContentBackend/Start/index-repository.html.twig', $vars);
    }


    private function extractRepositoryInfos($repositoryName, $repositoryItem, $definition = false)
    {

        $repository = $this->repositoryManager->getRepositoryByRepositoryAccessHash($repositoryItem['accessHash']);

        $item          = array();
        $item['title'] = $repositoryItem['title'];
        $item['url']   = $repository->getPublicUrl();
        $item['link']  = $this->generateUrl('anycontent_repository',['repositoryAccessHash' => $repositoryItem['accessHash'] ]);
        $item['files'] = false;

        $item['content_types'] = array();

        foreach ($this->repositoryManager->listContentTypes($repositoryName) as $contentTypeName => $contentTypeItem)
        {

            //$info = array( 'name' => $contentTypeItem['name'], 'title' => $contentTypeItem['title'], 'link' => '', 'page' => 1  );
            $info = array( 'name' => $contentTypeItem['name'], 'title' => $contentTypeItem['title'], 'link' => $this->generateUrl('anycontent_records', [ 'contentTypeAccessHash' => $contentTypeItem['accessHash'], 'page' => 1 ]) );

            if ($definition)
            {
                $info['definition'] = $repository->getContentTypeDefinition($contentTypeName);
            }

            $item['content_types'][] = $info;
        }

        $item['config_types'] = array();

        foreach ($this->repositoryManager->listConfigTypes($repositoryName) as $configTypeName => $configTypeItem)
        {
            $info = array( 'name' => $configTypeItem['name'], 'title' => $configTypeItem['title'], 'link' => $app['url_generator']->generate('editConfig', array( 'configTypeAccessHash' => $configTypeItem['accessHash'] )) );

            if ($definition)
            {
                $info['definition'] = $repository->getConfigTypeDefinition($configTypeName);
            }

            $item['config_types'][] = $info;
        }

        if ($this->repositoryManager->hasFiles($repositoryName))
        {
            $item['files'] = $app['url_generator']->generate('listFiles', array( 'repositoryAccessHash' => $repositoryItem['accessHash'], 'path' => '' ));
        }

        return $item;

    }

}