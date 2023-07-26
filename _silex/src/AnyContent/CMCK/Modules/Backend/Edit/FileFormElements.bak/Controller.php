<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\FileFormElements;

use AnyContent\Client\ContentFilter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

use AnyContent\Client\Repository;
use AnyContent\Client\Record;
use AnyContent\Client\UserInfo;

use AnyContent\CMCK\Modules\Backend\Edit\FileFormElements\Module;

class Controller
{

    public static function modal(Application $app, Request $request, Module $module = null, $repositoryAccessHash, $path ='/')
    {

        $vars['url_file_select'] = $app['url_generator']->generate('listFilesSelect', array( 'repositoryAccessHash' => $repositoryAccessHash, 'path' => $path ));

        return $app['twig']->render('formelement-file-modal.twig', $vars);

    }

}