<?php

namespace AnyContent\CMCK\Modules\Backend\Admin\CMDL;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

use AnyContent\CMCK\Modules\Backend\Core\User\UserManager;

use Symfony\Component\HttpKernel\KernelEvents;

class Module extends \AnyContent\CMCK\Modules\Backend\Core\Core\Module
{

    public function init(Application $app, $options = array())
    {

        parent::init($app, $options);

        $app->addTemplatesFolders(__DIR__ . '/views/');

        $app->get('admin', 'AnyContent\CMCK\Modules\Backend\Admin\CMDL\Controller::admin')->bind('admin');

        $app
            ->get('/admin/edit/content_type/{contentTypeAccessHash}', 'AnyContent\CMCK\Modules\Backend\Admin\CMDL\Controller::adminEditContentType')
            ->bind('adminEditContentType');

        $app
            ->post('/admin/edit/content_type/{contentTypeAccessHash}', 'AnyContent\CMCK\Modules\Backend\Admin\CMDL\Controller::postEditContentType')
            ->bind('postEditContentType');

        $app
            ->get('/admin/delete/content_type/{contentTypeAccessHash}', 'AnyContent\CMCK\Modules\Backend\Admin\CMDL\Controller::adminDeleteContentType')
            ->bind('adminDeleteContentType');

        $app
            ->post('/admin/add/content_type/{repositoryAccessHash}', 'AnyContent\CMCK\Modules\Backend\Admin\CMDL\Controller::adminAddContentType')
            ->bind('adminAddContentType');

        $app
            ->get('/admin/edit/config_type/{configTypeAccessHash}', 'AnyContent\CMCK\Modules\Backend\Admin\CMDL\Controller::adminEditConfigType')
            ->bind('adminEditConfigType');

        $app
            ->post('/admin/edit/config_type/{configTypeAccessHash}', 'AnyContent\CMCK\Modules\Backend\Admin\CMDL\Controller::postEditConfigType')
            ->bind('postEditConfigType');

        $app
            ->get('/admin/delete/config_type/{configTypeAccessHash}', 'AnyContent\CMCK\Modules\Backend\Admin\CMDL\Controller::adminDeleteConfigType')
            ->bind('adminDeleteConfigType');

        $app
            ->post('/admin/add/config_type/{repositoryAccessHash}', 'AnyContent\CMCK\Modules\Backend\Admin\CMDL\Controller::adminAddConfigType')
            ->bind('adminAddConfigType');


    }

}