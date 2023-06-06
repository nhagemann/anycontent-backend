<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Layout;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;
use Symfony\Component\EventDispatcher\Event;

class LayoutTemplateRenderEvent extends Event
{

    protected $app;
    protected $template;
    protected $vars;

    function __construct(Application $app, $template, $vars)
    {
        $this->app      = $app;
        $this->template = $template;
        $this->vars     = $vars;
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return mixed
     */
    public function getVars()
    {
        return $this->vars;
    }

    /**
     * @param mixed $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @param mixed $vars
     */
    public function setVars($vars)
    {
        $this->vars = $vars;
    }

    /**
     * @param null $key
     *
     * @return Application|mixed
     */
    public function getApp($key=null)
    {
        if ($key!=null)
        {
            return $this->app[$key];
        }
        return $this->app;
    }
}