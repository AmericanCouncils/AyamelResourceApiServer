<?php

namespace Ayamel\FilesystemBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers analyzers for the filesystem analyzer.  These analyzers fill in file reference attributes.
 *
 * @package AyamelFilesystemBundle
 * @author Evan Villemez
 */
class RegisterFilesystemAnalyzersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $analyzerDefinition = $container->getDefinition('ayamel.filesystem.analyzer');

        if ('Ayamel\FilesystemBundle\Analyzer\DelegatingAnalyzer' === $analyzerDefinition->getClass()) {
            foreach ($container->findTaggedServiceIds('ayamel.filesystem.analyzer') as $id => $attributes) {
                $class = $container->getDefinition($id)->getClass();
                $refClass = new \ReflectionClass($class);
                if (!$refClass->implementsInterface('Ayamel\FilesystemBundle\Analyzer\AnalyzerInterface')) {
                    throw new \InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $id, $interface));
                }

                $analyzerDefinition->addMethodCall('registerAnalyzer', array(new Reference($id)));
            }
        }
    }
}
