<?php

namespace Modules\Api;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ApiBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__) . '/Api';
    }
}
