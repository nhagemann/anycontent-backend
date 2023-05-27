<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Context;

use AnyContent\Client\Repository;
use AnyContent\CMCK\Modules\Backend\Core\Repositories\RepositoryManager;
use CMDL\DataTypeDefinition;
use CMDL\ConfigTypeDefinition;
use CMDL\ContentTypeDefinition;
use AnyContent\Client\Record;
use AnyContent\Client\Config;

use Silex\Application;
use Symfony\Component\HttpFoundation\Session\Session;

class ContextManager
{

    /** @var  Application */
    protected $app;

    /** @var  Session */
    protected $session;

    /** @var Repository */
    protected $repository = null;

    /** @var DataTypeDefinition */
    protected $dataTypeDefinition = null;

    /** @var Record */
    protected $record = null;

    protected $config = null;

    protected $prefix = 'context_';

    protected $context = null;

    protected $defaultNumberOfItemsPerPage = 10;

    public function __construct(Application $app)
    {
        $this->app     = $app;
        $this->session = $app['session'];
        $this->init();
    }

    /**
     * @return RepositoryManager
     */
    protected function getRepositoryManager()
    {
        return $this->app['repos'];
    }

    public function init()
    {

        if (!$this->session->has($this->prefix . 'messages')) {
            $this->session->set($this->prefix . 'messages', array('success' => array(), 'info' => array(), 'alert' => array(), 'error' => array()));
        }
        if (!$this->session->has($this->prefix . 'sorting')) {
            $this->session->set($this->prefix . 'sorting', array());
        }
        if (!$this->session->has($this->prefix . 'searchterms')) {
            $this->session->set($this->prefix . 'searchterms', array());
        }
        if (!$this->session->has($this->prefix . 'contentviews')) {
            $this->session->set($this->prefix . 'contentviews', array());
        }
        if (!$this->session->has($this->prefix . 'listing_page')) {
            $this->session->set($this->prefix . 'listing_page', array());
        }
        if (!$this->session->has($this->prefix . 'timeshift')) {
            $this->session->set($this->prefix . 'timeshift', 0);
        }
        if (!$this->session->has($this->prefix . 'workspace')) {
            $this->session->set($this->prefix . 'workspace', 'default');
        }
        if (!$this->session->has($this->prefix . 'language')) {
            $this->session->set($this->prefix . 'language', 'default');
        }
    }

    public function setCurrentRepository(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function getCurrentRepository()
    {
        return $this->repository;
    }

    public function setCurrentContentType(ContentTypeDefinition $contentTypeDefinition)
    {
        $this->context = 'content';

        return $this->setCurrentDataType($contentTypeDefinition);
    }

    public function setCurrentConfigType(ConfigTypeDefinition $configTypeDefinition)
    {
        $this->context = 'config';

        return $this->setCurrentDataType($configTypeDefinition);
    }

    public function setCurrentDataType(DataTypeDefinition $dataTypeDefinition)
    {
        $this->dataTypeDefinition = $dataTypeDefinition;

        $contentType = $dataTypeDefinition->getTitle();
        if (!$contentType) {
            $contentType = $dataTypeDefinition->getName();
        }
        // check workspaces

        $workspaces = $dataTypeDefinition->getWorkspaces();

        if (!array_key_exists($this->getCurrentWorkspace(), $workspaces)) {
            reset($workspaces);
            list($key, $workspace) = each($workspaces);

            $this->setCurrentWorkspace($key);
            $this->addInfoMessage('Switching to workspace ' . $workspace . ' (' . $key . ') for content type ' . $contentType . '.');
        }

        if ($dataTypeDefinition->hasLanguages()) {
            $languages = $dataTypeDefinition->getLanguages();
        }
        else {
            $languages = array('default' => 'None');
        }

        if (!array_key_exists($this->getCurrentLanguage(), $languages)) {
            reset($languages);
            list($key, $language) = each($languages);

            $this->setCurrentLanguage($key);
            $this->addInfoMessage('Switching to language ' . $language . ' (' . $key . ') for content type ' . $contentType . '.');
        }

        if (!$dataTypeDefinition->isTimeShiftable() AND $this->getCurrentTimeShift() != 0) {
            $this->resetTimeShift();
        }
    }

    /**
     * @return ContentTypeDefinition
     */
    public function getCurrentContentType()
    {
        return $this->dataTypeDefinition;
    }

    /**
     * @return bool|string
     */
    public function getCurrentContentTypeAccessHash()
    {
        return $this->getRepositoryManager()
                    ->getAccessHash($this->getCurrentRepository(), $this->getCurrentContentType());
    }

    /**
     * @return ConfigTypeDefinition
     */
    public function getCurrentConfigType()
    {
        return $this->dataTypeDefinition;
    }

    /**
     * @return DataTypeDefinition
     */
    public function getCurrentDataTypeDefinition()
    {
        if ($this->isContentContext()) {
            return $this->getCurrentContentType();
        }
        else {
            return $this->getCurrentConfigType();
        }
    }

    public function setCurrentRecord(Record $record)
    {
        $this->record = $record;
    }

    /**
     * @return Record
     */
    public function getCurrentRecord()
    {
        return $this->record;
    }

    public function setCurrentConfig(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return Config
     */
    public function getCurrentConfig()
    {
        return $this->config;
    }

    public function setFilesContext()
    {
        $this->context = 'files';
    }

    public function isContentContext()
    {
        if ($this->context == 'content') {
            return true;
        }

        return false;
    }

    public function isConfigContext()
    {
        if ($this->context == 'config') {
            return true;
        }

        return false;
    }

    public function isFilesContext()
    {
        if ($this->context == 'files') {
            return true;
        }

        return false;
    }

    public function setCurrentWorkspace($workspace)
    {
        return $this->session->set($this->prefix . 'workspace', $workspace);
    }

    public function setCurrentLanguage($language)
    {
        return $this->session->set($this->prefix . 'language', $language);
    }

    public function getCurrentLanguageName()
    {
        $dataTypeDefinition = $this->getCurrentDataTypeDefinition();

        if ($dataTypeDefinition) {
            $languages = $dataTypeDefinition->getLanguages();

            if (array_key_exists($this->getCurrentLanguage(), $languages)) {
                return $languages[$this->getCurrentLanguage()];
            }
        }

        return false;
    }

    public function getCurrentWorkspace()
    {
        return $this->session->get($this->prefix . 'workspace');
    }

    public function getCurrentWorkspaceName()
    {
        $dataTypeDefinition = $this->getCurrentDataTypeDefinition();

        if ($dataTypeDefinition) {
            $workspaces = $dataTypeDefinition->getWorkspaces();
            if (array_key_exists($this->getCurrentWorkspace(), $workspaces)) {
                return $workspaces[$this->getCurrentWorkspace()];
            }
        }

        return false;
    }

    public function getCurrentLanguage()
    {
        return $this->session->get($this->prefix . 'language');
    }

    public function getCurrentTimeShift()
    {
        return $this->session->get($this->prefix . 'timeshift');
    }

    public function getCurrentSaveOperation()
    {
        if ($this->session->has($this->prefix . 'save_operation')) {
            return array($this->session->get($this->prefix . 'save_operation') => $this->session->get($this->prefix . 'save_operation_title'));
        }

        return array('save' => 'Save');
    }

    public function setCurrentSaveOperation($operation, $title)
    {

        $this->session->set($this->prefix . 'save_operation', $operation);
        $this->session->set($this->prefix . 'save_operation_title', $title);
    }

    public function setCurrentTimeShift($timestamp)
    {
        $date = New \DateTime();
        if ($timestamp > $date->getTimestamp()) {
            $this->addErrorMessage('Cannot time shift into the future! - "Jesus, George, it was a wonder I was even born." (Marty McFly)');
        }
        else {
            $this->session->set($this->prefix . 'timeshift', $timestamp);
        }
    }

    public function resetTimeShift()
    {
        if ($this->getCurrentTimeShift() != 0) {
            if ($this->isContentContext() AND $this->getCurrentContentType()->isTimeShiftable() == false) {
                $contentType = $this->getCurrentContentType()->getTitle();
                if (!$contentType) {
                    $contentType = $this->getCurrentContentType()->getName();
                }

                $this->addInfoMessage('Content type ' . $contentType . ' doesn\'t support time shifting. Switching back to real time.');
            }
            else {
                $this->addInfoMessage('Switching back to real time.');
            }
        }
        $this->session->set($this->prefix . 'timeshift', 0);
    }

    public function setCurrentSortingOrder($order, $switch = true)
    {

        if ($switch == true) {
            if ($this->getCurrentSortingOrder() == $order) {
                $order = $order . '-';
            }
            if ($this->getCurrentSortingOrder() == $order . '-') {
                $order = trim($order, '-');
            }
        }

        $sorting                                           = $this->session->get($this->prefix . 'sorting');
        $sorting[$this->getCurrentContentTypeAccessHash()] = $order;
        $this->session->set($this->prefix . 'sorting', $sorting);
    }

    public function getCurrentSortingOrder()
    {
        if ($this->session->has($this->prefix . 'sorting')) {
            $sorting = $this->session->get($this->prefix . 'sorting');
            if (array_key_exists($this->getCurrentContentTypeAccessHash(), $sorting)) {
                return $sorting[$this->getCurrentContentTypeAccessHash()];
            }
        }

        return 'name';
    }

    public function setCurrentListingPage($page)
    {
        $listing                                           = $this->session->get($this->prefix . 'listing_page');
        $listing[$this->getCurrentContentTypeAccessHash()] = $page;
        $this->session->set($this->prefix . 'listing_page', $listing);
    }

    public function getCurrentListingPage()
    {
        if ($this->session->has($this->prefix . 'listing_page')) {
            $listing = $this->session->get($this->prefix . 'listing_page');
            if (array_key_exists($this->getCurrentContentTypeAccessHash(), $listing)) {
                return $listing[$this->getCurrentContentTypeAccessHash()];
            }
        }

        return '1';
    }

    public function setCurrentSearchTerm($searchTerm)
    {
        $searchTerms                                           = $this->session->get($this->prefix . 'searchterms');
        $searchTerms[$this->getCurrentContentTypeAccessHash()] = $searchTerm;
        $this->session->set($this->prefix . 'searchterms', $searchTerms);
    }

    public function getCurrentSearchTerm()
    {
        if ($this->session->has($this->prefix . 'searchterms')) {
            $searchTerms = $this->session->get($this->prefix . 'searchterms');
            if (array_key_exists($this->getCurrentContentTypeAccessHash(), $searchTerms)) {
                return $searchTerms[$this->getCurrentContentTypeAccessHash()];
            }
        }

        return '';
    }

    public function setCurrentContentViewNr($type)
    {
        $contentTypeAccessHash                = $this->getRepositoryManager()
                                                     ->getAccessHash($this->getCurrentRepository(), $this->getCurrentContentType());
        $contentViews                         = $this->session->get($this->prefix . 'contentviews');
        $contentViews[$contentTypeAccessHash] = $type;
        $this->session->set($this->prefix . 'contentviews', $contentViews);
    }

    public function getCurrentContentViewNr()
    {
        $contentTypeAccessHash = $this->getRepositoryManager()
                                      ->getAccessHash($this->getCurrentRepository(), $this->getCurrentContentType());
        if ($this->session->has($this->prefix . 'contentviews')) {
            $contentViews = $this->session->get($this->prefix . 'contentviews');
            if (array_key_exists($contentTypeAccessHash, $contentViews)) {
                return $contentViews[$contentTypeAccessHash];
            }
        }

        return 1;
    }

    public function setCurrentItemsPerPage($c)
    {
        $contentTypeAccessHash                = $this->getRepositoryManager()
                                                     ->getAccessHash($this->getCurrentRepository(), $this->getCurrentContentType());
        $itemsPerPage                         = $this->session->get($this->prefix . 'itemsperpage');
        $itemsPerPage[$contentTypeAccessHash] = $c;
        $this->session->set($this->prefix . 'itemsperpage', $itemsPerPage);
    }

    public function getCurrentItemsPerPage()
    {
        $contentTypeAccessHash = $this->getRepositoryManager()
                                      ->getAccessHash($this->getCurrentRepository(), $this->getCurrentContentType());
        if ($this->session->has($this->prefix . 'itemsperpage')) {
            $itemsPerPage = $this->session->get($this->prefix . 'itemsperpage');
            if (array_key_exists($contentTypeAccessHash, $itemsPerPage)) {
                return $itemsPerPage[$contentTypeAccessHash];
            }
        }

        return $this->getDefaultNumberOfItemsPerPage();
    }

    public function addSuccessMessage($message, $errorCode = null)
    {
        $messages              = $this->session->get($this->prefix . 'messages');
        $messages['success'][] = array('errorCode' => $errorCode, 'message' => $message);
        $this->session->set($this->prefix . 'messages', $messages);
    }

    public function addInfoMessage($message, $errorCode = null)
    {
        $messages           = $this->session->get($this->prefix . 'messages');
        $messages['info'][] = array('errorCode' => $errorCode, 'message' => $message);
        $this->session->set($this->prefix . 'messages', $messages);
    }

    public function addAlertMessage($message, $errorCode = null)
    {
        $messages            = $this->session->get($this->prefix . 'messages');
        $messages['alert'][] = array('errorCode' => $errorCode, 'message' => $message);
        $this->session->set($this->prefix . 'messages', $messages);
    }

    public function addErrorMessage($message, $errorCode = null)
    {
        $messages            = $this->session->get($this->prefix . 'messages');
        $messages['error'][] = array('errorCode' => $errorCode, 'message' => $message);
        $this->session->set($this->prefix . 'messages', $messages);
    }

    public function getSuccessMessages()
    {
        $messages            = $this->session->get($this->prefix . 'messages');
        $result              = $messages['success'];
        $messages['success'] = array();
        $this->session->set($this->prefix . 'messages', $messages);

        return $result;
    }

    public function getInfoMessages()
    {
        $messages         = $this->session->get($this->prefix . 'messages');
        $result           = $messages['info'];
        $messages['info'] = array();
        $this->session->set($this->prefix . 'messages', $messages);

        return $result;
    }

    public function getAlertMessages()
    {
        $messages          = $this->session->get($this->prefix . 'messages');
        $result            = $messages['alert'];
        $messages['alert'] = array();
        $this->session->set($this->prefix . 'messages', $messages);

        return $result;
    }

    public function getErrorMessages()
    {
        $messages          = $this->session->get($this->prefix . 'messages');
        $result            = $messages['error'];
        $messages['error'] = array();
        $this->session->set($this->prefix . 'messages', $messages);

        return $result;
    }

    /**
     * @return int
     */
    public function getDefaultNumberOfItemsPerPage()
    {
        return $this->defaultNumberOfItemsPerPage;
    }

    /**
     * @param int $defaultNumberOfItemsPerPage
     */
    public function setDefaultNumberOfItemsPerPage($defaultNumberOfItemsPerPage)
    {
        $this->defaultNumberOfItemsPerPage = $defaultNumberOfItemsPerPage;
    }

    public function canDoTimeshift()
    {
        if ($this->isContentContext()) {
            if ($this->getCurrentContentType()) {
                return $this->getCurrentContentType()->isTimeShiftable();
            }
        }
        if ($this->isConfigContext()) {
            if ($this->getCurrentConfigType()) {
                return $this->getCurrentConfigType()->isTimeShiftable();
            }
        }

        return false;
    }

    public function canDoSearch()
    {
        if ($this->isContentContext()) {
            return true;
        }

        return false;
    }

    public function canChangeWorkspace()
    {
        if ($this->isContentContext()) {
            return $this->getCurrentContentType()->hasWorkspaces();
        }

        if ($this->isConfigContext()) {
            return $this->getCurrentConfigType()->hasWorkspaces();
        }

        return false;
    }

    public function canChangeLanguage()
    {
        if ($this->isContentContext()) {
            return $this->getCurrentContentType()->hasLanguages();
        }
        if ($this->isConfigContext()) {
            return $this->getCurrentConfigType()->hasLanguages();
        }

        return false;
    }
}