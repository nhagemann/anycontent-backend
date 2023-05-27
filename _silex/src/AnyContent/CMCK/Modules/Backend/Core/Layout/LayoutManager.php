<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Layout;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;
use AnyContent\CMCK\Modules\Backend\Core\Context\ContextManager;

class LayoutManager
{

    /** @var  Application */
    protected $app;

    protected $twig;

    /** @var  ContextManager */
    protected $context;

    protected $vars = array();

    protected $cssFiles = array();

    protected $jsFiles = array();

    protected $jsLinks = array( 'head' => array(), 'body' => array() );

    protected $cssLinks = array( 'head' => array(), 'body' => array() );

    protected $brand = [ 'name' => 'AnyContent', 'logo' => '/img/anycontent-logo.png' ];


    public function __construct(Application $app, $twig, $context)
    {
        $this->app     = $app;
        $this->twig    = $twig;
        $this->context = $context;
    }


    public function addVar($key, $value)
    {
        $this->vars[$key] = $value;
    }


    public function getVar($key, $default = '')
    {
        if (array_key_exists($key, $this->vars))
        {
            return $this->vars[$key];
        }

        return $default;
    }


    public function addCssFile($filename)
    {
        if (!in_array($filename, $this->cssFiles))
        {
            $this->cssFiles[] = $filename;

            $path = APPLICATION_PATH . '/web/css/add';

            if (!file_exists($path))
            {
                mkdir($path);
            }

            $path = APPLICATION_PATH . '/web/css/add/' . $filename;

            if ($this->app['debug'] == true || !file_exists($path))
            {
                $data = $this->app['twig']->render($filename);
                file_put_contents($path, $data);
            }

            $this->addCssLinkToHead('/css/add/' . $filename . '?' . @filemtime($path));
        }

    }


    public function addJsFile($filename)
    {

        if (!in_array($filename, $this->jsFiles))
        {
            $this->jsFiles[] = $filename;
        }

        $path = APPLICATION_PATH . '/web/js/add';

        if (!file_exists($path))
        {
            mkdir($path);
        }

        $path = APPLICATION_PATH . '/web/js/add/' . $filename;

        if ($this->app['debug'] == true || !file_exists($path))
        {
            $data = $this->app['twig']->render($filename);
            file_put_contents($path, $data);
        }

        $this->addJsLinkToEndOfBody('/js/add/' . $filename . '?' . @filemtime($path));
    }


    public function addJsLinkToHead($link)
    {
        if (!in_array($link, $this->jsLinks['head']))
        {
            $this->jsLinks['head'][] = $link;
        }
    }


    public function addJsLinkToEndOfBody($link)
    {
        if (!in_array($link, $this->jsLinks['body']))
        {
            $this->jsLinks['body'][] = $link;
        }
    }


    public function addCssLinkToHead($link)
    {
        if (!in_array($link, $this->cssLinks['head']))
        {
            $this->cssLinks['head'][] = $link;
        }
    }


    public function addCssLinkToEndOfBody($link)
    {
        if (!in_array($link, $this->cssLinks['body']))
        {
            $this->cssLinks['body'][] = $link;
        }
    }


    /**
     * @return array
     */
    public function getBrand()
    {
        return $this->brand;
    }


    /**
     * @param  $brand
     */
    public function setBrand($name, $logo)
    {
        $this->brand = [ 'name' => $name, 'logo' => $logo ];
    }


    public function render($templateFilename, $vars = array(), $displayMessages = true)
    {
        $app = $this->getApplication();

        $vars['brand'] = $this->getBrand();

        $vars = array_merge($this->vars, $vars);

        $jsheadlinks = '';
        foreach ($this->jsLinks['head'] as $link)
        {
            $jsheadlinks .= '<script src="' . $link . '"></script>' . PHP_EOL;
        }
        $vars['jsheadlinks'] = $jsheadlinks;

        $jsbodylinks = '';
        foreach ($this->jsLinks['body'] as $link)
        {
            $jsbodylinks .= '<script src="' . $link . '"></script>' . PHP_EOL;
        }
        $vars['jsbodylinks'] = $jsbodylinks;
        $cssheadlinks        = '';
        foreach ($this->cssLinks['head'] as $link)
        {
            $cssheadlinks .= '<link rel="stylesheet" href="' . $link . '"></link>' . PHP_EOL;
        }
        $vars['cssheadlinks'] = $cssheadlinks;

        $cssbodylinks = '';
        foreach ($this->cssLinks['body'] as $link)
        {
            $cssbodylinks .= '<link rel="stylesheet" href="' . $link . '"></link>' . PHP_EOL;
        }
        $vars['cssbodylinks'] = $cssbodylinks;

        if ($displayMessages)
        {
            $messages            = array();
            $messages['success'] = $this->context->getSuccessMessages();
            $messages['info']    = $this->context->getInfoMessages();
            $messages['alert']   = $this->context->getAlertMessages();
            $messages['error']   = $this->context->getErrorMessages();
            $vars['messages']    = $messages;
        }

        $csscontext = [ ];

        $repository = $this->context->getCurrentRepository();

        if ($repository)
        {

            $csscontext[] = 'repository-' . strtolower($repository->getName());

            $record = $this->context->getCurrentRecord();

            if ($record)
            {
                $csscontext[] = 'contenttype-' . strtolower($record->getContentTypeName());

                $definition = $record->getContentTypeDefinition();

                if ($definition->hasSubtypes() && $record->getSubtype() != '')
                {
                    $csscontext[] = 'subtype-' . strtolower($record->getSubtype());
                }

                if ($definition->hasStatusList() && $record->getStatus() != '')
                {
                    $csscontext[] = 'status-' . strtolower($record->getStatus());
                }
            }

            $config = $this->context->getCurrentConfig();

            if ($config)
            {
                $csscontext[] = 'configtype-' . strtolower($config->getConfigTypeName());
            }

        }

        $vars['csscontext'] = join(' ', $csscontext);

        $event = new LayoutTemplateRenderEvent($app, $templateFilename, $vars);

        /** @var LayoutTemplateRenderEvent $event */
        $event = $app['dispatcher']->dispatch(Module::EVENT_LAYOUT_TEMPLATE_RENDER, $event);

        $templateFilename = $event->getTemplate();
        $vars             = $event->getVars();

        return $this->twig->render($templateFilename, $vars);
    }


    /**
     * @return Application
     */
    protected function getApplication()
    {
        return $this->app;
    }

}