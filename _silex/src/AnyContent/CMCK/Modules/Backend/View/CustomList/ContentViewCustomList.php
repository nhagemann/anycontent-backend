<?php

namespace AnyContent\CMCK\Modules\Backend\View\CustomList;

use AnyContent\CMCK\Modules\Backend\Core\Listing\AttributeColumn;

use AnyContent\CMCK\Modules\Backend\Core\Listing\ButtonColumn;
use AnyContent\CMCK\Modules\Backend\Core\Listing\ContentViewDefault;
use AnyContent\CMCK\Modules\Backend\Core\Listing\PropertyColumn;
use AnyContent\CMCK\Modules\Backend\Core\Listing\SelectionColumn;
use AnyContent\CMCK\Modules\Backend\Core\Listing\StatusColumn;
use AnyContent\CMCK\Modules\Backend\Core\Listing\SubtypeColumn;

use CMDL\CMDLParserException;
use Silex\Application;

class ContentViewCustomList extends ContentViewDefault
{

    public function apply($vars)
    {
        $vars = parent::apply($vars);

        if ($this->getCustomAnnotation()->hasList(2) && $this->getCustomAnnotation()->hasList(3))
        {
            $filter = array_combine(array_values($this->getCustomAnnotation()
                                                      ->getList(3)), array_values($this->getCustomAnnotation()
                                                                                       ->getList(2)));

            $vars['filter'] = $filter;

        }

        return $vars;
    }


    public function getColumnsDefinition()
    {
        $annotation = $this->getCustomAnnotation();
        $definition = $this->getContentTypeDefinition();

        $list = $annotation->getList(1);

        $columns = [ ];

        foreach ($list as $key => $title)
        {
            if ($definition->hasProperty($key))
            {
                $formelementDefinition = null;
                $column                = new PropertyColumn();

                try
                {
                    $formelementDefinition = $definition->getViewDefinition('default')->getFormElementDefinition($key);
                }
                catch (CMDLParserException $e)
                {
                    // If view does not have a form element definition, but still knows the property
                }

                if ($key == 'status' && $definition->hasStatusList())
                {
                    $column = new StatusColumn();
                }
                elseif ($key == 'subtype' && $definition->hasSubtypes())
                {
                    $column = new SubtypeColumn();
                }
                else
                {

                    if ($formelementDefinition)
                    {
                        switch ($formelementDefinition->getFormElementType())
                        {
                            case 'selection':
                                $column = new SelectionColumn();
                                break;
                            default:
                                $column = new PropertyColumn();
                                break;
                        }
                    }

                }

                $column->setProperty($key);


                $column->setFormElementDefinition($formelementDefinition);

                if ($key == 'name')
                {
                    $column->setLinkToRecord(true);
                }
            }
            else
            {
                $column = new AttributeColumn();
                $column->setAttribute($key);

                if (trim($key, '.') == 'id')
                {
                    $column->setLinkToRecord(true);
                }
            }

            $column->setTitle($title);

            $column->setRenderer($this->getCellRenderer());

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

        return $columns;
    }

}