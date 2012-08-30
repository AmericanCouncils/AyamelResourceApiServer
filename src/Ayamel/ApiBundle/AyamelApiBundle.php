<?php

namespace Ayamel\ApiBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Ayamel\ApiBundle\DependencyInjection\Compiler\RegisterApiEventListenersPass;

class AyamelApiBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterApiEventListenersPass());
    }

}
