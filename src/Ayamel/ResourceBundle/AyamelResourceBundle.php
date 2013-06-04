<?php

namespace Ayamel\ResourceBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Ayamel\ResourceBundle\DependencyInjection\Compiler\RegisterProviderDelegatesPass;

class AyamelResourceBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterProviderDelegatesPass());
    }
}
