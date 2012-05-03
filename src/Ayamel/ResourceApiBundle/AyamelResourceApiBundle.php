<?php

namespace Ayamel\ResourceApiBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Ayamel\ResourceApiBundle\DependencyInjection\Compiler\RegisterApiEventListenersPass;

class AyamelResourceApiBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterApiEventListenersPass());
    }

}
