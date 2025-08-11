<?php

namespace Modules\Business;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class BusinessBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__) . '/Business';
    }
    
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = $this->createContainerExtension();
        }
        
        return $this->extension;
    }
    
    protected function createContainerExtension()
    {
        return new DependencyInjection\BusinessExtension();
    }
}
