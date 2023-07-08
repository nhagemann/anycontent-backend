<?php

namespace AnyContent\Backend\Forms\FormElements\LinkFormElement;

use AnyContent\Backend\Controller\AbstractAnyContentBackendController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class Controller extends AbstractAnyContentBackendController
{
    #[Route('/formelement/link/check/', 'anycontent_formelement_link_check', methods: ['GET'])]
    public static function check(Request $request)
    {
        $ch = curl_init($request->get('url', '/'));

        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_exec($ch);
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return new JsonResponse($retcode);
    }
}
