<?php

namespace AC\MutateBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class RegisterTranscoderTagsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('mutate.transcoder')) {
            return;
        }

        $definition = $container->getDefinition('mutate.transcoder');
		
		foreach (array('adapter','preset','job', 'listener') as $tagType) {
			$tagName = 'mutate.transcoder.'.$tagType;
			$method = 'register'.ucfirst($tagType);
			
			//call registration method
			foreach ($container->findTaggedServiceIds($tagName) as $id => $attributes) {
				$definition->addMethodCall($method, array(new Reference($id)));
			}
		}
    }
}
