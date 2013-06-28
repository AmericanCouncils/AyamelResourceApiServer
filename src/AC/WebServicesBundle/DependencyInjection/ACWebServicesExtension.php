<?php
namespace AC\WebServicesBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ACWebServicesExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        foreach ($config as $key => $val) {
            $container->setParameter('ac_web_services.'.$key, $val);
        }
    }
}