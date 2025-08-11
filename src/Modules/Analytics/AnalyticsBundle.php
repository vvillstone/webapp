<?php

namespace Modules\Analytics;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AnalyticsBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__) . '/Analytics';
    }
}
