<?php

namespace AC\WebServicesBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use AC\WebServicesBundle\DependencyInjection\RegisterWebserviceListenersPass;

class ACWebServicesBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterWebserviceListenersPass());
    }
}
