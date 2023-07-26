<?php

namespace AnyContent\Backend\ContentListViews\DefaultTable;

use AnyContent\Backend\Services\ContextManager;
use AnyContent\Filter\PropertyFilter;
use CMDL\ContentTypeDefinition;
use CMDL\Util;

use function AnyContent\Backend\Modules\Listing\ContentViews\DefaultTable\count;

class FilterUtil
{
    public function __construct(private ContextManager $contextManager)
    {
    }

    public function normalizeFilterQuery($query, ContentTypeDefinition $contentTypeDefinition)
    {
        $query = str_replace('><', '*=', $query);

        try {
            $condition = $this->parseCondition($query);
            if (is_array($condition) && count($condition) == 3) {
                $property = Util::generateValidIdentifier($condition[0]);
                if (!$contentTypeDefinition->hasProperty($property)) {
                    $this->contextManager->addAlertMessage('Cannot filter by property ' . $property . '.');
                    $query = '';
                }
            } else {
                $query = 'name *= ' . $query;
            }

            $filter = new PropertyFilter($query);
        } catch (\Exception $e) {
            $this->contextManager->addAlertMessage('Could not parse query.');
            $this->contextManager->setCurrentSearchTerm('');
            //$query  = '';
            $filter = '';
        }

        //$this->contextManager->setCurrentSearchTerm($query);

        return $filter;
    }

    protected function escape($s)
    {
        $s = str_replace('\\+', '&#43;', $s);
        $s = str_replace('\\,', '&#44;', $s);
        $s = str_replace('\\=', '&#61;', $s);

        return $s;
    }

    protected function decode($s)
    {
        $s = str_replace('&#43;', '+', $s);
        $s = str_replace('&#44;', ',', $s);
        $s = str_replace('&#61;', '=', $s);

        // remove surrounding quotes
        if (substr($s, 0, 1) == '"') {
            $s = trim($s, '"');
        } else {
            $s = trim($s, "'");
        }

        return $s;
    }

    /**
     * http://stackoverflow.com/questions/4955433/php-multiple-delimiters-in-explode
     *
     * @param $s
     *
     * @return bool
     */
    protected function parseCondition($s)
    {
        $match = preg_match("/([^>=|<=|!=|>|<|=|\*=)]*)(>=|<=|!=|>|<|=|\*=)(.*)/", $s, $matches);

        if ($match) {
            $condition   = [];
            $condition[] = $this->decode(trim($matches[1]));
            $condition[] = trim($matches[2]);
            $condition[] = $this->decode(trim($matches[3]));

            return $condition;
        }

        return false;
    }
}
