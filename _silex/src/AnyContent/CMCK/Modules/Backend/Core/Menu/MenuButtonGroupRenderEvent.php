<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Menu;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;
use Symfony\Component\EventDispatcher\Event;

class MenuButtonGroupRenderEvent extends Event
{

    protected $app;
    protected $buttons;


    function __construct(Application $app, $buttons)
    {
        $this->app     = $app;
        $this->buttons = $buttons;
    }


    /**
     * Array of buttons with keys label, url, glyphicon, id
     *
     * @return array
     */
    public function getButtons()
    {
        return $this->buttons;
    }


    /**
     * Array of buttons with keys label, url, glyphicon, id
     *
     * @param array $buttons
     */
    public function setButtons($buttons)
    {
        $this->buttons = $buttons;
    }


    /**
     * @param null $key
     *
     * @return Application|mixed
     */
    public function getApp($key = null)
    {
        if ($key != null)
        {
            return $this->app[$key];
        }

        return $this->app;
    }
}