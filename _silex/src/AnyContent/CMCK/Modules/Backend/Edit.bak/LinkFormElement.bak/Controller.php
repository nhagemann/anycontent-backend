<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\LinkFormElement;

use AnyContent\Client\ContentFilter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

use AnyContent\Client\Repository;
use AnyContent\Client\Record;
use AnyContent\Client\UserInfo;

use AnyContent\CMCK\Modules\Backend\Edit\LinkFormElement\Module;

class Controller
{

    public static function check(Application $app, Request $request, Module $module = null, $path = '/')
    {
        $ch = curl_init($path);

        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_exec($ch);
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return new JsonResponse($retcode);
    }

}