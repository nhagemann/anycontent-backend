<?php

namespace AnyContent\Backend\Forms\FormElements\TableFormElement;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use Twig\Environment;

class FormElementTable extends FormElementDefault
{
    protected string $template = '@AnyContentBackend/Forms/formelement-table.html.twig';

    public function render(Environment $twig)
    {
        $nrOfColumns = count($this->definition->getColumnHeadings());

        if ($nrOfColumns > 0) {
            $columns = [];
            $i       = 0;
            $sum     = 0;
            foreach ($this->definition->getColumnHeadings() as $heading) {
                $item               = [];
                $item['heading']    = $heading;
                $item['percentage'] = 1;
                $columns[$i++]      = $item;

                $sum = $sum + 1;
            }

            if ($i == count($this->definition->getWidths())) {
                $i   = 0;
                $sum = 0;
                foreach ($this->definition->getWidths() as $percentage) {
                    $item               = $columns[$i];
                    $item['percentage'] = $percentage;
                    $columns[$i++]      = $item;

                    $sum = $sum + $percentage;
                    $percentage;
                }
            }

            $i = 0;
            foreach ($columns as $item) {
                $item['percentage'] = (int)($item['percentage'] / $sum * 100);
                $columns[$i++]      = $item;
            }

            $this->vars['columns'] = $columns;

            $rows  = [];
            $value = json_decode($this->value, true);

            if (!$value or count($value) == 0) {
                $rows[] = array_fill(0, $i, '');
            } else {
                $rows = $value;
            }

            $this->vars['rows']  = $rows;
            $this->vars['count'] = count($rows);

            return $twig->render($this->template, $this->vars);
        }

        return '';
    }

    public function parseFormInput($input)
    {
        $c = count($this->definition->getColumnHeadings());

        if (is_array($input)) {
            $value = array_chunk($input, $c);
            $value = json_encode($value);
        } else {
            // Received invalid data
            $value = null;
        }

        return $value;
    }
}
