<?php

namespace AnyContent\Backend\Modules\Listing;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class PagingHelper
{
    public function __construct(
        private Environment $twig,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function renderPager($nrOfItems, $itemsPerPage, $currentPage, $routeName, $parameters, $pageParameter = 'page', $addLeft = 3, $addRight = 3)
    {
        $maxPages    = ceil($nrOfItems / $itemsPerPage);
        $currentPage = min($currentPage, $maxPages);

        if ($maxPages == 1) {
            return '';
        }

        $items = [];

        $start = max(1, $currentPage - $addLeft);
        $stop  = min($currentPage + $addRight, $maxPages);

        $parameters[$pageParameter] = $currentPage - 1;
        $url                        = $this->urlGenerator->generate($routeName, $parameters);
        $prev                       = ['url' => $url, 'disabled' => false];
        if ($currentPage == 1) {
            $prev['disabled'] = true;
        }

        $parameters[$pageParameter] = $currentPage + 1;
        $url                        = $this->urlGenerator->generate($routeName, $parameters);
        $next                       = ['url' => $url, 'disabled' => false];
        if ($currentPage == $maxPages) {
            $next['disabled'] = true;
        }

        if ($start > 1) {
            $parameters[$pageParameter] = 1;
            $url                        = $this->urlGenerator->generate($routeName, $parameters);
            $items[]                    = ['label' => '&laquo;', 'active' => false, 'url' => $url];
        }

        for ($i = $start; $i <= $stop; $i++) {
            $active = false;
            if ($currentPage == $i) {
                $active = true;
            }
            $parameters[$pageParameter] = $i;
            $url                        = $this->urlGenerator->generate($routeName, $parameters);
            $items[]                    = ['label' => $i, 'active' => $active, 'url' => $url];
        }

        if ($stop < $maxPages) {
            $parameters[$pageParameter] = $maxPages;
            $url                        = $this->urlGenerator->generate($routeName, $parameters);
            $items[]                    = ['label' => '&raquo;', 'active' => false, 'url' => $url];
        }

        return $this->twig->render('@AnyContentBackend/Listing/pager.html.twig', ['items' => $items, 'prev' => $prev, 'next' => $next]);
    }
}
