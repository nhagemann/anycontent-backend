<?php

namespace AnyContent\Backend\Forms\FormElements\NumberFormElement;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use CMDL\FormElementDefinitions\NumberFormElementDefinition;
use Twig\Environment;

class FormElementNumber extends FormElementDefault
{
    /** @var  NumberFormElementDefinition */
    protected $definition;

    protected string $template = '@AnyContentBackend/Forms/formelement-number.html.twig';

    public function render(Environment $twig)
    {
        $leftAddOn  = '#';
        $rightAddOn = null;

        if ($this->definition->getUnit()) {
            $leftAddOn  = null;
            $rightAddOn = $this->definition->getUnit();
        }

        $this->vars['leftAddOn']  = $leftAddOn;
        $this->vars['rightAddOn'] = $rightAddOn;

        $this->vars['value'] = trim($this->vars['value']);
        if ($this->vars['value'] != '') {
            $this->vars['value'] = number_format((float)$this->vars['value'], $this->definition->getDigits(), '.', '');
        }

        return parent::render($twig);
    }

    /**
     * This form field implementation is not reflecting the user's locale. To be as robust as possible it
     * interprets '.' as well as ',' as decimal separator, reflects them and constructs a computer readable
     * "number string" with '.'
     *
     * @param $input
     *
     * @return mixed|string
     */
    public function parseFormInput($input)
    {
        $value = str_replace(',', '.', $input);

        $tokens = explode('.', $value);

        if (count($tokens) > 1) {
            $digits = array_pop($tokens);
            $value  = join('', $tokens) . '.' . $digits;
        }

        $value = trim($value);
        if ($value != '') {
            $value = number_format($value, $this->definition->getDigits(), '.', '');
        }

        return $value;
    }
}
