<?php

namespace Ayamel\ResourceBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Ayamel\ResourceBundle\DependencyInjection\Compiler\RegisterProviderDelegatesPass;
use Ayamel\ResourceBundle\DependencyInjection\Compiler\RegisterManagerEventListenersPass;

class AyamelResourceBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterProviderDelegatesPass());
        $container->addCompilerPass(new RegisterManagerEventListenersPass());
    }
}
