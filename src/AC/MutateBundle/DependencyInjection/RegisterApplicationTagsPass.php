<?php

namespace AC\MutateBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class RegisterApplicationTagsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('mutate.transcoder')) {
            return;
        }

        $definition = $container->getDefinition('mutate.application');
		
		//call registration method
		foreach ($container->findTaggedServiceIds('mutate.application.command') as $id => $attributes) {
			$definition->addMethodCall('addCommand', array(new Reference($id)));
		}
    }
}
