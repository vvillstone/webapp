<?php

namespace Modules\Business;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class BusinessBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__) . '/Business';
    }
    
    public function getContainerExtension(): ?\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = $this->createContainerExtension();
        }
        
        return $this->extension;
    }
    
    protected function createContainerExtension(): ?\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        return new DependencyInjection\BusinessExtension();
    }
}
