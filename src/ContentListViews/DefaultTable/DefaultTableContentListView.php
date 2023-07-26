<?php

namespace AnyContent\Backend\ContentListViews\DefaultTable;

use AnyContent\Backend\ContentListViews\ContentListViewInterface;
use AnyContent\Backend\DependencyInjection\DefaultImplementation;
use AnyContent\Backend\Modules\Listing\PagingHelper;
use AnyContent\Backend\Services\ContextManager;
use CMDL\CMDLParserException;
use Symfony\Component\HttpFoundation\RequestStack;

class DefaultTableContentListView implements ContentListViewInterface, DefaultImplementation
{
    public function __construct(
        private ContextManager $contextManager,
        private CellRenderer $cellRenderer,
        private PagingHelper $pagingHelper,
        private FilterUtil $filterUtil,
        private RequestStack $requestStack
    ) {
    }

    public function getName(): string
    {
        return 'default';
    }

    public function getTitle()
    {
        return 'Listing';
    }

    public function getTemplate(): string
    {
        return '@AnyContentBackend/Listing/listing-contentview-default.html.twig';
    }

    public function __invoke(&$vars)
    {
        //parent::apply($contextManager,$vars);

        // reset chained save operations (e.g. 'save-insert') to 'save' only upon listing of a content type
        if (key($this->contextManager->getCurrentSaveOperation()) != 'save-list') {
            $this->contextManager->setCurrentSaveOperation('save', 'Save');
        }

        // reset sorting order and search query if listing button has been pressed inside a listing
//        var_dump($this->requestStack->getMainRequest()->get('_route'));
//        if ($this->getRequest()->get('_route') == 'listRecordsReset')
//        {
//            $this->contextManager->setCurrentSortingOrder('.info.lastchange.timestamp-', false);
//            $this->contextManager->setCurrentSearchTerm('');
//        }

        $filter = $this->getFilter();

        $vars['searchTerm'] = $this->contextManager->getCurrentSearchTerm();
        $vars['itemsPerPage'] = $this->contextManager->getCurrentItemsPerPage();

        $vars['table'] = false;
        $vars['pager'] = false;
        $vars['filter'] = false;

        $records = $this->getRecords($filter);

        if (count($records) > 0) {
            $columns = $this->getColumnsDefinition();

            $vars['table'] = $this->buildTable($columns, $records);

            $count = $this->countRecords($filter);

            $vars['pager'] = $this->pagingHelper->renderPager($count, $this->contextManager
                ->getCurrentItemsPerPage(), $this->contextManager
                ->getCurrentListingPage(), 'anycontent_records', ['contentTypeAccessHash' => $this->contextManager->getCurrentContentTypeAccessHash()]);
        }

        $vars['class'] = 'row contenttype-' . strtolower($this->contextManager->getCurrentContentType()->getName());

        return $vars;
    }

    /**
     * backwards compatible converting of sorting instructions
     */
    public function getSortingOrder()
    {
        $sorting = $this->contextManager->getCurrentSortingOrder();

        $map = ['.lastchange' => '.info.lastchange.timestamp', '.lastchange+' => '.info.lastchange.timestamp', '.lastchange-' => '.info.lastchange.timestamp-',
            'change' => '.info.lastchange.timestamp', 'change+' => '.info.lastchange.timestamp', 'change-' => '.info.lastchange.timestamp-',
            'pos' => 'position', 'pos+' => 'position', 'pos-' => 'position-',
        ];

        if (array_key_exists($sorting, $map)) {
            $sorting = $map[$sorting];
        }

        return $sorting;
    }

    public function getFilter()
    {
        $filter = null;

        $searchTerm = $this->contextManager->getCurrentSearchTerm();
        if ($searchTerm != '') {
            $filter = $this->filterUtil->normalizeFilterQuery($searchTerm, $this->contextManager->getCurrentDataTypeDefinition());
        }

        return $filter;
    }

    public function getColumnsDefinition()
    {
        $contentTypeDefinition = $this->contextManager->getCurrentContentTypeDefinition();

        $columns = [];

        $column = new AttributeColumn();
        $column->setTitle('ID');
        $column->setAttribute('id');
        $column->setLinkToRecord(true);
        $columns[] = $column;

        if ($contentTypeDefinition->hasSubtypes()) {
            $column = new SubtypeColumn();
            $column->setTitle('Subtype');
            $columns[] = $column;
        }

        $column = new PropertyColumn();
        $column->setTitle('Name');
        $column->setProperty('name');
        $column->setLinkToRecord(true);
        try {
            $column->setFormElementDefinition($contentTypeDefinition->getViewDefinition('default')
                ->getFormElementDefinition('name'));
        } catch (CMDLParserException $e) {
            // If default view does not have a name form element
        }

        $columns[] = $column;

        $column = new AttributeColumn();
        $column->setTitle('Last Change');
        $column->setAttribute('lastchange');
        $columns[] = $column;

        if ($contentTypeDefinition->hasStatusList()) {
            $column = new StatusColumn();
            $column->setTitle('Status');
            $columns[] = $column;
        }

        if ($contentTypeDefinition->isSortable()) {
            $column = new AttributeColumn();
            $column->setTitle('Pos');
            $column->setAttribute('position');
            $columns[] = $column;
        }

        // Add Edit/Delete-Buttons

        $buttonColumn = new ButtonColumn();
        $buttonColumn->setEditButton(true);
        $buttonColumn->setDeleteButton(true);
        $buttonColumn->setRenderer($this->getCellRenderer());
        $columns[] = $buttonColumn;

        foreach ($columns as $column) {
            $column->setRenderer($this->getCellRenderer());
        }

        return $columns;
    }

    public function buildTable($columns, $records)
    {
        $table = [];

        foreach ($columns as $column) {
            $table['header'][] = $column;
        }

        $table['body'] = [];

        foreach ($records as $record) {
            $line = [];
            foreach ($columns as $column) {
                $line[] = $column->formatValue($record);
            }
            $table['body'][] = $line;
        }

        return $table;
    }

    /**
     * @return CellRenderer
     */
    public function getCellRenderer()
    {
        return $this->cellRenderer;
    }

    public function countRecords($filter)
    {
        $repository = $this->contextManager->getCurrentRepository();
        return $repository->countRecords($filter);
    }

    public function getRecords($filter)
    {
        $repository = $this->contextManager->getCurrentRepository();

        $page = $this->contextManager->getCurrentListingPage();
        $itemsPerPage = $this->contextManager->getCurrentItemsPerPage();
        $sorting = $this->getSortingOrder();

        return $repository->getRecords($filter, $sorting, $page, $itemsPerPage);
    }
}
