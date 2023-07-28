<?php

namespace AnyContent\Backend\Forms\FormElements\CustomFormElements;

use AnyContent\Backend\Forms\FormElements\CustomFormElementInterface;
use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use AnyContent\Backend\Services\ContextManager;
use AnyContent\Backend\Services\FormManager;
use AnyContent\Backend\Services\RepositoryManager;
use CMDL\FormElementDefinition;
use CMDL\FormElementDefinitions\CustomFormElementDefinition;
use DateTime;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * This is an exemplary custom form element
 */
class FormElementWindowsExcelDate extends FormElementDefault implements CustomFormElementInterface
{
    /** @var  CustomFormElementDefinition */
    protected $definition;

    protected string $type = 'exceldate';

    protected string $template = '@AnyContentBackend/Forms/Custom/formelement-exceldate.html.twig';

    public function init(FormElementDefinition $definition, ?string $id, mixed $value = ''): void
    {
        parent::init($definition,$id,$value);

        $this->vars['date'] = '';
        if (is_numeric($this->value)) {
            $t = $this->excelToTimeStamp($this->value);

                $d = new DateTime();
                $d->setTimestamp($t);
                $this->vars['date'] = $d->format('d.m.Y');
        }
    }

    /**
     * http://stackoverflow.com/questions/11172644/php-convert-the-full-excel-date-serial-format-to-unix-timestamp
     *
     * Assuming Windows Excel and ignoring the difference to Mac Excel, since we don't know the origin of the data
     */
    protected function excelToTimeStamp(int $dateValue = 0)
    {
        $myExcelBaseDate = 25569;
        //    Adjust for the spurious 29-Feb-1900 (Day 60)
        if ($dateValue < 60) {
            --$myExcelBaseDate;
        }

        // Perform conversion
        if ($dateValue >= 1) {
            $utcDays = $dateValue - $myExcelBaseDate;
            $timestap = round($utcDays * 86400);
            if (($timestap <= PHP_INT_MAX) && ($timestap >= -PHP_INT_MAX)) {
                $timestap = (int)$timestap;
            }
        } else {
            $hours = round($dateValue * 24);
            $mins = round($dateValue * 1440) - round($hours * 60);
            $secs = round($dateValue * 86400) - round($hours * 3600) - round($mins * 60);
            $timestap = (int)mktime((int)$hours, (int)$mins, (int)$secs);
        }

        return $timestap;
    }
}
