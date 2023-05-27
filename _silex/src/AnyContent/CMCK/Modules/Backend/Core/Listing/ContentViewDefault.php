<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Listing;

use CMDL\CMDLParserException;

class ContentViewDefault extends BaseContentView
{

    /** @var  CellRenderer */
    protected $cellRenderer;


    public function getTemplate()
    {
        return 'listing-contentview-default.twig';
    }


    public function doesProcessSearch()
    {
        return true;
    }


    public function apply($vars)
    {

        // reset chained save operations (e.g. 'save-insert') to 'save' only upon listing of a content type
        if (key($this->getContext()->getCurrentSaveOperation()) != 'save-list')
        {
            $this->getContext()->setCurrentSaveOperation('save', 'Save');
        }

        // reset sorting order and search query if listing button has been pressed inside a listing
        if ($this->getRequest()->get('_route') == 'listRecordsReset')
        {
            $this->getContext()->setCurrentSortingOrder('.info.lastchange.timestamp-', false);
            $this->getContext()->setCurrentSearchTerm('');
        }

        $filter = $this->getFilter();

        $vars['searchTerm']   = $this->getContext()->getCurrentSearchTerm();
        $vars['itemsPerPage'] = $this->getContext()->getCurrentItemsPerPage();

        $vars['table']  = false;
        $vars['pager']  = false;
        $vars['filter'] = false;

        $records = $this->getRecords($filter);

        if (count($records) > 0)
        {
            $columns = $this->getColumnsDefinition();

            $vars['table'] = $this->buildTable($columns, $records);

            $count = $this->countRecords($filter);

            $vars['pager'] = $this->getPager()->renderPager($count, $this->getContext()
                                                                         ->getCurrentItemsPerPage(), $this->getContext()
                                                                                                          ->getCurrentListingPage(), 'listRecords', array( 'contentTypeAccessHash' => $this->getContentTypeAccessHash() ));

        }

        $vars['class']='row contenttype-'.strtolower($this->getContext()->getCurrentContentType()->getName());

        return $vars;
    }


    /**
     * backwards compatible converting of sorting instructions
     */
    public function getSortingOrder()
    {
        $sorting = $this->getContext()->getCurrentSortingOrder();

        $map = [ '.lastchange' => '.info.lastchange.timestamp', '.lastchange+' => '.info.lastchange.timestamp', '.lastchange-' => '.info.lastchange.timestamp-',
                 'change'      => '.info.lastchange.timestamp', 'change+' => '.info.lastchange.timestamp', 'change-' => '.info.lastchange.timestamp-',
                 'pos'         => 'position', 'pos+' => 'position', 'pos-' => 'position-'
        ];

        if (array_key_exists($sorting, $map))
        {
            $sorting = $map[$sorting];
        }

        return $sorting;
    }


    public function getFilter()
    {
        $filter = null;

        $searchTerm = $this->getContext()->getCurrentSearchTerm();
        if ($searchTerm != '')
        {
            $filter = FilterUtil::normalizeFilterQuery($this->app, $searchTerm, $this->getContentTypeDefinition());
        }

        return $filter;
    }


    public function getColumnsDefinition()
    {
        $contentTypeDefinition = $this->getContentTypeDefinition();

        $columns = [ ];

        $column = new AttributeColumn();
        $column->setTitle('ID');
        $column->setAttribute('id');
        $column->setLinkToRecord(true);
        $columns[] = $column;

        if ($contentTypeDefinition->hasSubtypes())
        {
            $column = new SubtypeColumn();
            $column->setTitle('Subtype');
            $columns[] = $column;
        }

        $column = new PropertyColumn();
        $column->setTitle('Name');
        $column->setProperty('name');
        $column->setLinkToRecord(true);
        try
        {
            $column->setFormElementDefinition($contentTypeDefinition->getViewDefinition('default')
                                                                    ->getFormElementDefinition('name'));
        }
        catch (CMDLParserException $e)
        {
            // If default view does not have a name form element
        }

        $columns[] = $column;

        $column = new AttributeColumn();
        $column->setTitle('Last Change');
        $column->setAttribute('lastchange');
        $columns[] = $column;

        if ($contentTypeDefinition->hasStatusList())
        {
            $column = new StatusColumn();
            $column->setTitle('Status');
            $columns[] = $column;
        }

        if ($contentTypeDefinition->isSortable())
        {
            $column = new AttributeColumn();
            $column->setTitle('Pos');
            $column->setAttribute('position');
            $columns[] = $column;
        }

        // Add Edit/Delete-Buttons

        $buttonColumn = new ButtonColumn();
        $buttonColumn->setEditButton(true);
        if ($this->canDo('delete', $this->getRepository(), $this->getContentTypeDefinition()))
        {
            $buttonColumn->setDeleteButton(true);
        }
        $buttonColumn->setRenderer($this->getCellRenderer());
        $columns[] = $buttonColumn;

        foreach ($columns as $column)
        {
            $column->setRenderer($this->getCellRenderer());
        }

        return $columns;
    }


    public function buildTable($columns, $records)
    {
        $table = [ ];

        foreach ($columns as $column)
        {
            $table['header'][] = $column;
        }

        $table['body'] = [ ];

        foreach ($records as $record)
        {
            $line = [ ];
            foreach ($columns as $column)
            {
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
        if (!$this->cellRenderer)
        {
            $this->cellRenderer = new CellRenderer($this->app);
        }

        return $this->cellRenderer;
    }


    public function countRecords($filter)
    {
        $repository = $this->getRepository();

        $page         = $this->getContext()->getCurrentListingPage();
        $itemsPerPage = $this->getContext()->getCurrentItemsPerPage();
        //$viewName     = 'default';

        $sorting = $this->getSortingOrder();

        //$count = $repository->countRecords($this->getContext()->getCurrentWorkspace(), $viewName, $this->getContext()
        //->getCurrentLanguage(), $sorting[0], $sorting[1], $itemsPerPage, $page, $filter, null, $this->getContext()->getCurrentTimeShift());

        $count = $repository->countRecords($filter);

        return $count;
    }


    public function getRecords($filter)
    {
        $repository = $this->getRepository();

        $page         = $this->getContext()->getCurrentListingPage();
        $itemsPerPage = $this->getContext()->getCurrentItemsPerPage();
        //$viewName     = 'default';

        $sorting = $this->getSortingOrder();

        return $repository->getRecords($filter, $sorting, $page, $itemsPerPage);

        //return $repository->getRecords($this->getContext()->getCurrentWorkspace(), $viewName, $this->getContext()
        //->getCurrentLanguage(), $sorting[0], $sorting[1], $itemsPerPage, $page, $filter, null, $this->getContext()
    }
}

