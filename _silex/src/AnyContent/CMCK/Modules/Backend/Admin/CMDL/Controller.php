<?php

namespace AnyContent\CMCK\Modules\Backend\Admin\CMDL;

use AnyContent\CMCK\Modules\Backend\Core\Repositories\RepositoryManager;
use CMDL\ConfigTypeDefinition;
use CMDL\Parser;
use CMDL\Util;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

use AnyContent\Client\Repository;

use CMDL\ContentTypeDefinition;
use Symfony\Component\HttpFoundation\Session\Session;

class Controller
{

    public static function admin(Application $app)
    {

        $app['layout']->addJsFile('admin.js');

        $vars = array();

        $vars['menu_mainmenu'] = $app['menus']->renderMainMenu();

        /** @var RepositoryManager $repositoryManager */
        $repositoryManager = $app['repos'];

        $items = array();

        foreach ($repositoryManager->listRepositories() as $repositoryName => $repositoryItem) {
            $repository = $repositoryManager->getRepositoryById($repositoryName);
            if ($repository->isAdministrable()) {
                $items[] = self::extractRepositoryInfos($app, $repositoryName, $repositoryItem, false);
            }
        }

        $vars['repositories'] = $items;

        return $app->renderPage('admin.twig', $vars);
    }


    public static function adminEditContentType(Application $app, $contentTypeAccessHash)
    {
        $vars = array();
        $vars['menu_mainmenu'] = $app['menus']->renderMainMenu();
        $vars['links']['home'] = $app['url_generator']->generate('index');
        $vars['links']['admin'] = $app['url_generator']->generate('admin');
        $vars['links']['delete'] = $app['url_generator']->generate(
            'adminDeleteContentType',
            array('contentTypeAccessHash' => $contentTypeAccessHash)
        );

        /** @var Repository $repository */
        $repository = $app['repos']->getRepositoryByContentTypeAccessHash($contentTypeAccessHash);

        if ($repository) {
            $app['layout']->addJsFile('admin.js');

            $vars['record'] = false;

            /** @var ContentTypeDefinition $contentTypeDefinition */
            $contentTypeDefinition = $repository->getContentTypeDefinition();

            $cmdl = $contentTypeDefinition->getCMDL();

            $vars['definition'] = $contentTypeDefinition;
            $vars['cmdl'] = $cmdl;
            $vars['data_type'] = 'content';

            return $app->renderPage('admin-data-type.twig', $vars);

        }
    }


    public static function adminEditConfigType(Application $app, $configTypeAccessHash)
    {
        $vars = array();
        $vars['menu_mainmenu'] = $app['menus']->renderMainMenu();
        $vars['links']['home'] = $app['url_generator']->generate('index');
        $vars['links']['admin'] = $app['url_generator']->generate('admin');
        $vars['links']['delete'] = $app['url_generator']->generate(
            'adminDeleteConfigType',
            array('configTypeAccessHash' => $configTypeAccessHash)
        );

        /** @var Repository $repository */
        $repository = $app['repos']->getRepositoryByConfigTypeAccessHash($configTypeAccessHash);

        if ($repository) {
            $app['layout']->addJsFile('admin.js');

            $vars['record'] = false;

            $configTypeDefinition = $app['repos']->getConfigTypeDefinitionByConfigTypeAccessHash($configTypeAccessHash);

            $cmdl = $configTypeDefinition->getCMDL();

            $vars['definition'] = $configTypeDefinition;
            $vars['cmdl'] = $cmdl;
            $vars['data_type'] = 'config';

            return $app->renderPage('admin-data-type.twig', $vars);

        }
    }


    public static function postEditContentType(Application $app, Request $request, $contentTypeAccessHash)
    {
        /** @var Repository $repository */
        $repository = $app['repos']->getRepositoryByContentTypeAccessHash($contentTypeAccessHash);

        $response = array();
        $response['success'] = false;

        if ($repository) {

            $cmdl = $request->get('cmdl');

            try {
                /** @var ContentTypeDefinition $contentTypeDefinition */
                $contentTypeDefinition = Parser::parseCMDLString($cmdl);

                try {
                    /** @var ContentTypeDefinition $contentTypeDefinition */
                    $contentTypeDefinition = $repository->getContentTypeDefinition();

                    $connection = $repository->getWriteConnection();

                    if ($connection->saveContentTypeCMDL($contentTypeDefinition->getName(), $cmdl)) {
                        $response['success'] = true;
                        $app['menus']->clearCache();
                    }
                } catch (\Exception $e) {

                }
            } catch (\Exception $e) {
                $response['message'] = $e->getMessage();
            }

        }

        return new JsonResponse($response);
    }


    public static function postEditConfigType(Application $app, Request $request, $configTypeAccessHash)
    {
        /** @var Repository $repository */
        $repository = $app['repos']->getRepositoryByConfigTypeAccessHash($configTypeAccessHash);

        $response = array();
        $response['success'] = false;

        if ($repository) {

            $cmdl = $request->get('cmdl');

            try {
                /** @var ConfigTypeDefinition $contentTypeDefinition */
                $configTypeDefinition = Parser::parseCMDLString($cmdl, null, null, 'config');

                try {
                    /** @var ConfigTypeDefinition $contentTypeDefinition */
                    $configTypeDefinition = $app['repos']->getConfigTypeDefinitionByConfigTypeAccessHash(
                        $configTypeAccessHash
                    );

                    $connection = $repository->getWriteConnection();

                    if ($connection->saveConfigTypeCMDL($configTypeDefinition->getName(), $cmdl)) {
                        $response['success'] = true;
                        $app['menus']->clearCache();
                    }
                } catch (\Exception $e) {

                }
            } catch (\Exception $e) {
                $response['message'] = $e->getMessage();
            }

        }

        return new JsonResponse($response);
    }


    public static function adminAddContentType(Application $app, Request $request, $repositoryAccessHash)
    {
        $url = $app['url_generator']->generate('admin');

        /** @var RepositoryManager $repositoryManager */
        $repositoryManager = $app['repos'];

        /** @var Repository $repository */
        $repository = $repositoryManager->getRepositoryByRepositoryAccessHash($repositoryAccessHash);

        if ($repository) {
            $contentTypeName = substr(trim($request->request->get('create_content_type')), 0, 32);

            $contentTypeName = Util::generateValidIdentifier($contentTypeName);

            if ($contentTypeName != '') {
                if (!$repository->hasContentType($contentTypeName)) {
                    $connection = $repository->getWriteConnection();

                    self::clearDataTypesSessionCache($app, $repository);

                    if ($connection->saveContentTypeCMDL(
                        $contentTypeName,
                        '### definition of content type '.$contentTypeName.' ###'.PHP_EOL.PHP_EOL.'Name'
                    )
                    ) {
                        $app['context']->addSuccessMessage('Content Type '.$contentTypeName.' created.');
                        $app['menus']->clearCache();

                        return new RedirectResponse($url);
                    }
                } else {
                    $app['context']->addAlertMessage('Content Type '.$contentTypeName.' already exists.');
                }
            }

        }

        $app['context']->addErrorMessage('Error generating new content type.');

        return new RedirectResponse($url);

    }


    public static function adminAddConfigType(Application $app, Request $request, $repositoryAccessHash)
    {
        $url = $app['url_generator']->generate('admin');

        /** @var RepositoryManager $repositoryManager */
        $repositoryManager = $app['repos'];

        /** @var Repository $repository */
        $repository = $repositoryManager->getRepositoryByRepositoryAccessHash($repositoryAccessHash);

        if ($repository) {

            $configTypeName = substr(trim($request->request->get('create_config_type')), 0, 32);

            $configTypeName = Util::generateValidIdentifier($configTypeName);

            if ($configTypeName != '') {
                if (!$repository->hasConfigType($configTypeName)) {

                    self::clearDataTypesSessionCache($app, $repository);

                    $connection = $repository->getWriteConnection();

                    if ($connection->saveConfigTypeCMDL(
                        $configTypeName,
                        '### definition of config type '.$configTypeName.' ###'.PHP_EOL
                    )
                    ) {
                        $app['context']->addSuccessMessage('Config Type '.$configTypeName.' created.');
                        $app['menus']->clearCache();

                        return new RedirectResponse($url);
                    }
                } else {
                    $app['context']->addAlertMessage('Config Type '.$configTypeName.' already exists.');
                }
            }

        }

        $app['context']->addErrorMessage('Error generating new config type.');

        return new RedirectResponse($url);

    }


    public static function adminDeleteContentType(Application $app, Request $request, $contentTypeAccessHash)
    {
        $url = $app['url_generator']->generate('admin');

        /** @var Repository $repository */
        $repository = $app['repos']->getRepositoryByContentTypeAccessHash($contentTypeAccessHash);

        if ($repository) {
            /** @var ContentTypeDefinition $contentTypeDefinition */
            $contentTypeDefinition = $repository->getContentTypeDefinition();

            $contentTypeName = $contentTypeDefinition->getName();

            $connection = $repository->getWriteConnection();

            self::clearDataTypesSessionCache($app, $repository);

            if ($connection->deleteContentTypeCMDL($contentTypeName)) {
                $app['context']->addSuccessMessage('Content Type '.$contentTypeName.' deleted.');
                $app['menus']->clearCache();

                return new RedirectResponse($url);
            }

        }

        $app['context']->addErrorMessage('Error deleting content type.');

        return new RedirectResponse($url);
    }


    public static function adminDeleteConfigType(Application $app, Request $request, $configTypeAccessHash)
    {
        $url = $app['url_generator']->generate('admin');

        /** @var Repository $repository */
        $repository = $app['repos']->getRepositoryByConfigTypeAccessHash($configTypeAccessHash);

        if ($repository) {

            self::clearDataTypesSessionCache($app, $repository);

            /** @var ConfigTypeDefinition $configTypeDefinition */
            $configTypeDefinition = $app['repos']->getConfigTypeDefinitionByConfigTypeAccessHash($configTypeAccessHash);

            $configTypeName = $configTypeDefinition->getName();

            $connection = $repository->getWriteConnection();

            if ($connection->deleteConfigTypeCMDL($configTypeName)) {
                $app['context']->addSuccessMessage('Config Type '.$configTypeName.' deleted.');
                $app['menus']->clearCache();

                return new RedirectResponse($url);
            }

        }

        $app['context']->addErrorMessage('Error deleting config type.');

        return new RedirectResponse($url);
    }


    protected static function extractRepositoryInfos($app, $repositoryName, $repositoryItem, $definition = false)
    {
        /** @var RepositoryManager $repositoryManager */
        $repositoryManager = $app['repos'];

        if ($definition) {
            $repository = $repositoryManager->getRepositoryByRepositoryAccessHash($repositoryItem['accessHash']);
        }

        $item = array();
        $item['title'] = $repositoryItem['title'];
        $item['url'] = '';
        $item['link'] = $app['url_generator']->generate(
            'indexRepository',
            array('repositoryAccessHash' => $repositoryItem['accessHash'])
        );
        $item['links']['backup_repository'] = $app['url_generator']->generate(
            'adminBackupRepository',
            array('repositoryAccessHash' => $repositoryItem['accessHash'])
        );
        $item['links']['post_backup_repository'] = $app['url_generator']->generate(
            'adminPostBackupRepository',
            array('repositoryAccessHash' => $repositoryItem['accessHash'])
        );



        $item['links']['add_content_type'] = $app['url_generator']->generate(
            'adminAddContentType',
            array('repositoryAccessHash' => $repositoryItem['accessHash'])
        );
        $item['links']['add_config_type'] = $app['url_generator']->generate(
            'adminAddConfigType',
            array('repositoryAccessHash' => $repositoryItem['accessHash'])
        );

        $item['content_types'] = array();

        foreach ($repositoryManager->listContentTypes($repositoryName) as $contentTypeName => $contentTypeItem) {
            $info = array(
                'name' => $contentTypeName,
                'title' => $contentTypeItem['title'],
                'edit' => $app['url_generator']->generate(
                    'adminEditContentType',
                    array('contentTypeAccessHash' => $contentTypeItem['accessHash'])
                ),
                'backup' => $app['url_generator']->generate(
                    'adminBackupContentType',
                    array('contentTypeAccessHash' => $contentTypeItem['accessHash'])
                ),
                'delete' => $app['url_generator']->generate(
                    'adminDeleteContentType',
                    array('contentTypeAccessHash' => $contentTypeItem['accessHash'])
                ),
            );

            if ($definition) {
                $info['definition'] = $repository->getContentTypeDefinition($contentTypeName);
            }

            $item['content_types'][] = $info;
        }

        $item['config_types'] = array();

        foreach ($repositoryManager->listConfigTypes($repositoryName) as $configTypeName => $configTypeItem) {
            $info = array(
                'name' => $configTypeName,
                'title' => $configTypeItem['title'],
                'edit' => $app['url_generator']->generate(
                    'adminEditConfigType',
                    array('configTypeAccessHash' => $configTypeItem['accessHash'])
                ),
                'delete' => $app['url_generator']->generate(
                    'adminDeleteConfigType',
                    array('configTypeAccessHash' => $configTypeItem['accessHash'])
                ),
            );

            if ($definition) {
                $info['definition'] = $repository->getConfigTypeDefinition($configTypeName);
            }

            $item['config_types'][] = $info;
        }

        return $item;

    }


    protected function clearDataTypesSessionCache(Application $app, Repository $repository)
    {
        // remove list of available content type from session cache
        $cacheKeyContentTypes = 'sessioncache.repository.'.$repository->getName().'.contenttypes';
        $cacheKeyConfigTypes = 'sessioncache.repository.'.$repository->getName().'.configtypes';

        /** @var Session $session */
        $session = $app['session'];
        $session->remove($cacheKeyContentTypes);
        $session->remove($cacheKeyConfigTypes);
    }
}