<?php

namespace AnyContent\Backend\Modules\Start\Controller;


use AnyContent\Backend\Services\RepositoryManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ANYCONTENT')]
class StartController extends AbstractController
{
    public function __construct(private RepositoryManager $repositoryManager){}
    #[Route('/main')]
    public function index(): Response
    {
        $vars = [];

        $vars['menu_mainmenu'] = [];

        $items = array();
        foreach ($this->repositoryManager->listRepositories() as $repositoryName => $repositoryItem)
        {
            $items[] = self::extractRepositoryInfos($repositoryName, $repositoryItem, false);
        }

        $vars['repositories'] = $items;

        return $this->render('@AnyContentBackend/Start/index.html.twig', $vars);
    }


    private function extractRepositoryInfos($repositoryName, $repositoryItem, $definition = false)
    {

        $repository = $this->repositoryManager->getRepositoryByRepositoryAccessHash($repositoryItem['accessHash']);

        $item          = array();
        $item['title'] = $repositoryItem['title'];
        $item['url']   = $repository->getPublicUrl();
        $item['link']  = '';//$app['url_generator']->generate('indexRepository', array( 'repositoryAccessHash' => $repositoryItem['accessHash'] ));
        $item['files'] = false;

        $item['content_types'] = array();

        foreach ($this->repositoryManager->listContentTypes($repositoryName) as $contentTypeName => $contentTypeItem)
        {

            $info = array( 'name' => $contentTypeItem['name'], 'title' => $contentTypeItem['title'], 'link' => '', 'page' => 1  );
            //$info = array( 'name' => $contentTypeItem['name'], 'title' => $contentTypeItem['title'], 'link' => $app['url_generator']->generate('listRecords', array( 'contentTypeAccessHash' => $contentTypeItem['accessHash'], 'page' => 1 )) );

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