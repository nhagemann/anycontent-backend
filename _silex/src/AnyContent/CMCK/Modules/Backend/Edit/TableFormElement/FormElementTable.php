<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\TableFormElement;

class FormElementTable extends \AnyContent\CMCK\Modules\Backend\Core\Edit\FormElementDefault
{

    public function __construct($id, $name, $formElementDefinition, $app, $value = '')
    {
        parent::__construct($id, $name, $formElementDefinition, $app, $value);
    }


    public function render($layout)
    {
        $nrOfColumns = count($this->definition->getColumnHeadings());

        if ($nrOfColumns > 0)
        {
            $columns = array();
            $i       = 0;
            $sum     = 0;
            foreach ($this->definition->getColumnHeadings() as $heading)
            {
                $item               = array();
                $item['heading']    = $heading;
                $item['percentage'] = 1;
                $columns[$i++]      = $item;

                $sum = $sum + 1;
            }

            if ($i == count($this->definition->getWidths()))
            {
                $i   = 0;
                $sum = 0;
                foreach ($this->definition->getWidths() as $percentage)
                {
                    $item               = $columns[$i];
                    $item['percentage'] = $percentage;
                    $columns[$i++]      = $item;

                    $sum = $sum + $percentage;
                    $percentage;
                }

            }

            $i = 0;
            foreach ($columns as $item)
            {
                $item['percentage'] = (int)($item['percentage'] / $sum * 100);
                $columns[$i++]      = $item;
            }

            $this->vars['columns'] = $columns;

            $rows  = array();
            $value = json_decode($this->value, true);

            if (!$value OR count($value) == 0)
            {
                $rows[] = array_fill(0, $i, '');
            }
            else
            {
                $rows = $value;
            }

            $this->vars['rows']  = $rows;
            $this->vars['count'] = count($rows);

            return $this->twig->render('formelement-table.twig', $this->vars);
        }

        return '';
    }


    public function parseFormInput($input)
    {
        $c = count($this->definition->getColumnHeadings());

        if (is_array($input))
        {
            $value = array_chunk($input, $c);
            $value = json_encode($value);
        }
        else
        {
            // Received invalid data
            $value = null;
        }

        return $value;
    }
}