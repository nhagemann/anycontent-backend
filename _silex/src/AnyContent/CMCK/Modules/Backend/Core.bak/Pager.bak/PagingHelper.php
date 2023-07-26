<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Pager;

class PagingHelper
{

    protected $twig;
    protected $layout;
    protected $url_generator;


    public function __construct($twig, $layout, $url_generator)
    {
        $this->twig          = $twig;
        $this->layout        = $layout;
        $this->url_generator = $url_generator;
    }


    public function renderPager($nrOfItems, $itemsPerPage, $currentPage, $routeName, $parameters, $pageParameter = 'page', $addLeft = 3, $addRight = 3)
    {

        $maxPages    = ceil($nrOfItems / $itemsPerPage);
        $currentPage = min($currentPage, $maxPages);

        if ($maxPages == 1)
        {
            return '';
        }

        $items = array();

        $start = max(1, $currentPage - $addLeft);
        $stop  = min($currentPage + $addRight, $maxPages);

        $parameters[$pageParameter] = $currentPage - 1;
        $url                        = $this->url_generator->generate($routeName, $parameters);
        $prev                       = array( 'url' => $url, 'disabled' => false );
        if ($currentPage == 1)
        {
            $prev['disabled'] = true;
        }

        $parameters[$pageParameter] = $currentPage + 1;
        $url                        = $this->url_generator->generate($routeName, $parameters);
        $next                       = array( 'url' => $url, 'disabled' => false );
        if ($currentPage == $maxPages)
        {
            $next['disabled'] = true;
        }

        if ($start > 1)
        {
            $parameters[$pageParameter] = 1;
            $url                        = $this->url_generator->generate($routeName, $parameters);
            $items[]                    = array( 'label' => '&laquo;', 'active' => false, 'url' => $url );

        }

        for ($i = $start; $i <= $stop; $i++)
        {

            $active = false;
            if ($currentPage == $i)
            {
                $active = true;
            }
            $parameters[$pageParameter] = $i;
            $url                        = $this->url_generator->generate($routeName, $parameters);
            $items[]                    = array( 'label' => $i, 'active' => $active, 'url' => $url );

        }

        if ($stop < $maxPages)
        {
            $parameters[$pageParameter] = $maxPages;
            $url                        = $this->url_generator->generate($routeName, $parameters);
            $items[]                    = array( 'label' => '&raquo;', 'active' => false, 'url' => $url );

        }

        return $this->twig->render('pager.twig', array( 'items' => $items, 'prev' => $prev, 'next' => $next ));

    }

}