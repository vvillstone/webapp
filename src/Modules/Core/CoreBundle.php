<?php

namespace Modules\Core;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class CoreBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__) . '/Core';
    }
}
