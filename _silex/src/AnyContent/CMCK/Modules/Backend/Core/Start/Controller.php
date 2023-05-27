<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Start;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

use CMDL\ContentTypeDefinition;
use CMDL\ViewDefinition;

use AnyContent\Client\Repository;
use AnyContent\Client\Record;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use AnyContent\CMCK\Modules\Backend\Core\Repositories\RepositoryManager;

class Controller
{

    public static function index(Application $app)
    {

        $vars = array();

        $vars['menu_mainmenu'] = $app['menus']->renderMainMenu();

        /** @var RepositoryManager $repositoryManager */
        $repositoryManager = $app['repos'];

        $items = array();
        foreach ($repositoryManager->listRepositories() as $repositoryName => $repositoryItem)
        {
            try
            {
                $items[] = self::extractRepositoryInfos($app, $repositoryName, $repositoryItem, false);
            }
            catch (\Exception $e)
            {

            }
        }

        $vars['repositories'] = $items;

        return $app->renderPage('index.twig', $vars);
    }


    public static function indexRepository(Application $app, $repositoryAccessHash)
    {

        $vars = array();

        $vars['menu_mainmenu'] = $app['menus']->renderMainMenu();

        /** @var RepositoryManager $repositoryManager */
        $repositoryManager = $app['repos'];

        foreach ($repositoryManager->listRepositories() as $repositoryName => $repositoryItem)
        {
            if ($repositoryAccessHash == $repositoryItem['accessHash'])
            {
                $item               = self::extractRepositoryInfos($app, $repositoryName, $repositoryItem, true);
                $vars['repository'] = $item;
            }
        }

        return $app->renderPage('index-repository.twig', $vars);
    }


    protected static function extractRepositoryInfos($app, $repositoryName, $repositoryItem, $definition = false)
    {
        /** @var RepositoryManager $repositoryManager */
        $repositoryManager = $app['repos'];

        $repository = $repositoryManager->getRepositoryByRepositoryAccessHash($repositoryItem['accessHash']);

        $item          = array();
        $item['title'] = $repositoryItem['title'];
        $item['url']   = $repository->getPublicUrl();
        $item['link']  = $app['url_generator']->generate('indexRepository', array( 'repositoryAccessHash' => $repositoryItem['accessHash'] ));
        $item['files'] = false;

        $item['content_types'] = array();

        foreach ($repositoryManager->listContentTypes($repositoryName) as $contentTypeName => $contentTypeItem)
        {

            $info = array( 'name' => $contentTypeItem['name'], 'title' => $contentTypeItem['title'], 'link' => $app['url_generator']->generate('listRecords', array( 'contentTypeAccessHash' => $contentTypeItem['accessHash'], 'page' => 1 )) );

            if ($definition)
            {
                $info['definition'] = $repository->getContentTypeDefinition($contentTypeName);
            }

            $item['content_types'][] = $info;
        }

        $item['config_types'] = array();

        foreach ($repositoryManager->listConfigTypes($repositoryName) as $configTypeName => $configTypeItem)
        {
            $info = array( 'name' => $configTypeItem['name'], 'title' => $configTypeItem['title'], 'link' => $app['url_generator']->generate('editConfig', array( 'configTypeAccessHash' => $configTypeItem['accessHash'] )) );

            if ($definition)
            {
                $info['definition'] = $repository->getConfigTypeDefinition($configTypeName);
            }

            $item['config_types'][] = $info;
        }

        if ($repositoryManager->hasFiles($repositoryName))
        {
            $item['files'] = $app['url_generator']->generate('listFiles', array( 'repositoryAccessHash' => $repositoryItem['accessHash'], 'path' => '' ));
        }

        return $item;

    }

}