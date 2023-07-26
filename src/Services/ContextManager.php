<?php

namespace AnyContent\Backend\Services;

use AnyContent\Backend\Exception\AnyContentBackendException;
use AnyContent\Client\Config;
use AnyContent\Client\Record;
use AnyContent\Client\Repository;
use CMDL\ConfigTypeDefinition;
use CMDL\ContentTypeDefinition;
use CMDL\DataTypeDefinition;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

class ContextManager
{
    protected Session $session;

    protected Repository|null $repository = null;

    protected ContentTypeDefinition|ConfigTypeDefinition|DataTypeDefinition|null $dataTypeDefinition = null;

    protected ?Record $record = null;

    protected ?Config $config = null;

    protected string $prefix = 'anycontent_context_';

    protected ?string $context = null;

    protected $defaultNumberOfItemsPerPage = 10;

    public function __construct(
        RequestStack $requestStack,
        private RepositoryManager $repositoryManager,
    ) {
        $session = $requestStack->getMainRequest()->getSession();
        assert($session instanceof Session);
        $this->session = $session;

        if (!$this->session->has($this->prefix . 'messages')) {
            $this->session->set($this->prefix . 'messages', ['success' => [], 'info' => [], 'alert' => [], 'error' => []]);
        }
        if (!$this->session->has($this->prefix . 'sorting')) {
            $this->session->set($this->prefix . 'sorting', []);
        }
        if (!$this->session->has($this->prefix . 'searchterms')) {
            $this->session->set($this->prefix . 'searchterms', []);
        }
        if (!$this->session->has($this->prefix . 'contentviews')) {
            $this->session->set($this->prefix . 'contentviews', []);
        }
        if (!$this->session->has($this->prefix . 'listing_page')) {
            $this->session->set($this->prefix . 'listing_page', []);
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

        $this->setCurrentDataType($contentTypeDefinition);
    }

    public function setCurrentConfigType(ConfigTypeDefinition $configTypeDefinition)
    {
        $this->context = 'config';

        $this->setCurrentDataType($configTypeDefinition);
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
            $workspace = reset($workspaces);
            $key = key($workspaces);

            $this->setCurrentWorkspace($key);
            $this->addInfoMessage('Switching to workspace ' . $workspace . ' (' . $key . ') for content type ' . $contentType . '.');
        }

        if ($dataTypeDefinition->hasLanguages()) {
            $languages = $dataTypeDefinition->getLanguages();
        } else {
            $languages = ['default' => 'None'];
        }

        if (!array_key_exists($this->getCurrentLanguage(), $languages)) {
            $language = reset($languages);
            $key = key($languages);

            $this->setCurrentLanguage($key);
            $this->addInfoMessage('Switching to language ' . $language . ' (' . $key . ') for content type ' . $contentType . '.');
        }

        if (!$dataTypeDefinition->isTimeShiftable() and $this->getCurrentTimeShift() != 0) {
            $this->resetTimeShift();
        }
    }

    public function getCurrentContentType(): ContentTypeDefinition
    {
        if (!$this->isContentContext() || $this->dataTypeDefinition === null) {
            throw new AnyContentBackendException('Not a content context.');
        }
        assert($this->dataTypeDefinition instanceof ContentTypeDefinition);
        return $this->dataTypeDefinition;
    }

    public function getCurrentContentTypeDefinition(): ContentTypeDefinition
    {
        return $this->getCurrentContentType();
    }

    /**
     * @return bool|string
     */
    public function getCurrentContentTypeAccessHash()
    {
        return $this->repositoryManager
            ->getAccessHash($this->getCurrentRepository(), $this->getCurrentContentType());
    }

    public function getCurrentConfigTypeAccessHash()
    {
        return $this->repositoryManager
            ->getAccessHash($this->getCurrentRepository(), $this->getCurrentConfigType());
    }

    public function getCurrentRepositoryAccessHash()
    {
        return $this->repositoryManager
            ->getRepositoryAccessHash($this->getCurrentRepository());
    }

    public function getCurrentConfigType(): ConfigTypeDefinition
    {
        if (!$this->isConfigContext() || $this->dataTypeDefinition === null) {
            throw new AnyContentBackendException('Not a config context.');
        }
        assert($this->dataTypeDefinition instanceof ConfigTypeDefinition);
        return $this->dataTypeDefinition;
    }

    public function getCurrentConfigTypeDefinition(): ConfigTypeDefinition
    {
        return $this->getCurrentConfigType();
    }

    public function getCurrentDataTypeDefinition(): ?DataTypeDefinition
    {
        return $this->dataTypeDefinition;
//        if ($this->isContentContext()) {
//            return $this->getCurrentContentType();
//        } else {
//            return $this->getCurrentConfigType();
//        }
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

    public function isContentContext(): bool
    {
        if ($this->context === 'content') {
            return true;
        }

        return false;
    }

    public function isConfigContext(): bool
    {
        if ($this->context === 'config') {
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
        $this->session->set($this->prefix . 'workspace', $workspace);
    }

    public function setCurrentLanguage($language)
    {
        $this->session->set($this->prefix . 'language', $language);
    }

    public function getCurrentLanguageName()
    {
        $dataTypeDefinition = $this->getCurrentDataTypeDefinition();

        $languages = $dataTypeDefinition->getLanguages();

        if (array_key_exists($this->getCurrentLanguage(), $languages)) {
            return $languages[$this->getCurrentLanguage()];
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

        $workspaces = $dataTypeDefinition->getWorkspaces();
        if (array_key_exists($this->getCurrentWorkspace(), $workspaces)) {
            return $workspaces[$this->getCurrentWorkspace()];
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
            return [$this->session->get($this->prefix . 'save_operation') => $this->session->get($this->prefix . 'save_operation_title')];
        }

        return ['save' => 'Save'];
    }

    public function setCurrentSaveOperation($operation, $title)
    {
        $this->session->set($this->prefix . 'save_operation', $operation);
        $this->session->set($this->prefix . 'save_operation_title', $title);
    }

    public function setCurrentTimeShift($timestamp)
    {
        $date = new \DateTime();
        if ($timestamp > $date->getTimestamp()) {
            $this->addErrorMessage('Cannot time shift into the future! - "Jesus, George, it was a wonder I was even born." (Marty McFly)');
        } else {
            $this->session->set($this->prefix . 'timeshift', $timestamp);
        }
    }

    public function resetTimeShift()
    {
        if ($this->getCurrentTimeShift() != 0) {
            if ($this->isContentContext() and $this->getCurrentContentType()->isTimeShiftable() == false) {
                $contentType = $this->getCurrentContentType()->getTitle();
                if (!$contentType) {
                    $contentType = $this->getCurrentContentType()->getName();
                }

                $this->addInfoMessage('Content type ' . $contentType . ' doesn\'t support time shifting. Switching back to real time.');
            } else {
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

        $sorting = $this->session->get($this->prefix . 'sorting');
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
        $listing = $this->session->get($this->prefix . 'listing_page');
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
        $searchTerms = $this->session->get($this->prefix . 'searchterms');
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
        $contentTypeAccessHash = $this->repositoryManager
            ->getAccessHash($this->getCurrentRepository(), $this->getCurrentContentType());
        $contentViews = $this->session->get($this->prefix . 'contentviews');
        $contentViews[$contentTypeAccessHash] = $type;
        $this->session->set($this->prefix . 'contentviews', $contentViews);
    }

    public function getCurrentContentViewNr()
    {
        $contentTypeAccessHash = $this->repositoryManager
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
        $contentTypeAccessHash = $this->repositoryManager
            ->getAccessHash($this->getCurrentRepository(), $this->getCurrentContentType());
        $itemsPerPage = $this->session->get($this->prefix . 'itemsperpage');
        $itemsPerPage[$contentTypeAccessHash] = $c;
        $this->session->set($this->prefix . 'itemsperpage', $itemsPerPage);
    }

    public function getCurrentItemsPerPage()
    {
        $contentTypeAccessHash = $this->repositoryManager
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
        $this->session->getFlashBag()->add('success', $message);
    }

    public function addInfoMessage($message, $errorCode = null)
    {
        $this->session->getFlashBag()->add('info', $message);
    }

    public function addAlertMessage($message, $errorCode = null)
    {
        $this->session->getFlashBag()->add('alert', $message);
    }

    public function addErrorMessage($message, $errorCode = null)
    {
        $this->session->getFlashBag()->add('error', $message);
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

    public function canDoTimeshift(): bool
    {
        $definition = $this->getCurrentDataTypeDefinition();
        if ($definition === null) {
            return false;
        }
        return $definition->isTimeShiftable();
    }

    public function canDoSearch(): bool
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
