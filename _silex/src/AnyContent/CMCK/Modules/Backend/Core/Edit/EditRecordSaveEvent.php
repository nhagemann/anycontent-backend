<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Edit;

use AnyContent\Client\Record;
use AnyContent\CMCK\Modules\Backend\Core\Application\Application;
use Symfony\Component\EventDispatcher\Event;

class EditRecordSaveEvent extends Event
{

    protected $app;

    protected $infoMessage = false;
    protected $alertMessage = false;
    protected $errorMessage = false;

    /** @var  Record */
    protected $record;

    function __construct(Application $app, $record)
    {
        $this->app = $app;
        $this->record = $record;
    }

    /**
     * @return Record
     */
    public function getRecord()
    {
        return $this->record;
    }

    /**
     * @param Record $record
     */
    public function setRecord($record)
    {
        $this->record = $record;
    }


    /**
     * @param null $key
     *
     * @return Application|mixed
     */
    public function getApp($key = null)
    {
        if ($key != null) {
            return $this->app[$key];
        }

        return $this->app;
    }

    public function hasInfoMessage()
    {
        return (boolean)$this->infoMessage;
    }

    public function getInfoMessage()
    {
        return $this->infoMessage;
    }


    public function setInfoMessage($infoMessage)
    {
        $this->infoMessage = $infoMessage;
    }


    public function hasAlertMessage()
    {
        return (boolean)$this->alertMessage;
    }

    public function getAlertMessage()
    {
        return $this->alertMessage;
    }


    public function setAlertMessage($alertMessage)
    {
        $this->alertMessage = $alertMessage;
    }

    public function hasErrorMessage()
    {
        return (boolean)$this->errorMessage;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }


    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }


}