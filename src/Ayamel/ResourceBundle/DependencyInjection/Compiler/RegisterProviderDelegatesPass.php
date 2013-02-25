<?php

namespace Ayamel\ResourceBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class RegisterProviderDelegatesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ayamel.resource.provider')) {
            return;
        }

        $definition = $container->getDefinition('ayamel.resource.provider');

        //only process if the provider is a Delegating Provider, otherwise processing tags are pointless
        if ($definition->getClass() !== 'Ayamel\ResourceBundle\Provider\DelegatingProvider') {
            return;
        }

        //register all other providers
        foreach ($container->findTaggedServiceIds('ayamel.resource.provider_delegate') as $id => $attributes) {

            //make sure the tag is a legitimate ProviderInterface
            $class = $container->getDefinition($id)->getClass();
            $refClass = new \ReflectionClass($class);
            if (!$refClass->implementsInterface('Ayamel\ResourceBundle\Provider\ProviderInterface')) {
                throw new \InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $id, $interface));
            }

            $definition->addMethodCall('addProvider', array(new Reference($id)));
        }

    }
}
