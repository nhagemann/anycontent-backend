<?php

namespace AnyContent\Backend\ContentListViews;

interface ContentListViewInterface
{
    public function getName(): string;

    public function getTitle();

    public function getTemplate(): string;
}
