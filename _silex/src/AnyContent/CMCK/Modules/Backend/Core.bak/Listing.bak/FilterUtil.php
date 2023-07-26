<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Listing;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;
use AnyContent\Filter\PropertyFilter;
use CMDL\ContentTypeDefinition;
use CMDL\Util;

class FilterUtil
{

    public static function normalizeFilterQuery(Application $app, $query, ContentTypeDefinition $contentTypeDefinition)
    {
        $query = str_replace('><', '*=', $query);

        try
        {
            $condition = self::parseCondition($query);
            if (is_array($condition) && count($condition) == 3)
            {
                $property = Util::generateValidIdentifier($condition[0]);
                if (!$contentTypeDefinition->hasProperty($property))
                {
                    $app['context']->addAlertMessage('Cannot filter by property ' . $property . '.');
                    $query = '';
                }

            }
            else
            {

                $query = 'name *= ' . $query;

            }

            $filter = new PropertyFilter($query);
        }
        catch (\Exception $e)
        {
            $app['context']->addAlertMessage('Could not parse query.');
            $app['context']->setCurrentSearchTerm('');
            //$query  = '';
            $filter = '';
        }

        //$app['context']->setCurrentSearchTerm($query);

        return $filter;

    }


    protected static function escape($s)
    {
        $s = str_replace('\\+', '&#43;', $s);
        $s = str_replace('\\,', '&#44;', $s);
        $s = str_replace('\\=', '&#61;', $s);

        return $s;
    }


    protected static function decode($s)
    {
        $s = str_replace('&#43;', '+', $s);
        $s = str_replace('&#44;', ',', $s);
        $s = str_replace('&#61;', '=', $s);

        // remove surrounding quotes
        if (substr($s, 0, 1) == '"')
        {

            $s = trim($s, '"');
        }
        else
        {

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
    protected static function parseCondition($s)
    {

        $match = preg_match("/([^>=|<=|!=|>|<|=|\*=)]*)(>=|<=|!=|>|<|=|\*=)(.*)/", $s, $matches);

        if ($match)
        {
            $condition   = array();
            $condition[] = self::decode(trim($matches[1]));
            $condition[] = trim($matches[2]);
            $condition[] = self::decode(trim($matches[3]));

            return $condition;
        }

        return false;
    }
}