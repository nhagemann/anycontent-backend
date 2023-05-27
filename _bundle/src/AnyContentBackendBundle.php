<?php

namespace AnyContent\Backend;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AnyContentBackendBundle extends Bundle
{
    public function getPath(): string
    {
    return \dirname(__DIR__);
    }
}