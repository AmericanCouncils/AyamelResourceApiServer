<?php

/*
 * This... totally copy/pasted from Symfony/Bundle/FrameworkBundle/DependencyInjection/Compiler/RegisterKernelListenersPass.php
 */

namespace Ayamel\FilesystemBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class RegisterFilesystemEventListenersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $dispatcherDefinition = $container->findDefinition('event_dispatcher');

        foreach ($container->findTaggedServiceIds('ayamel.filesystem.event_listener') as $id => $events) {
            foreach ($events as $event) {
                $priority = isset($event['priority']) ? $event['priority'] : 0;

                if (!isset($event['event'])) {
                    throw new \InvalidArgumentException(sprintf('Service "%s" must define the "event" attribute on "kernel.event_listener" tags.', $id));
                }

                if (!isset($event['method'])) {
                    $event['method'] = 'on'.preg_replace(array(
                        '/(?<=\b)[a-z]/ie',
                        '/[^a-z0-9]/i'
                    ), array('strtoupper("\\0")', ''), $event['event']);
                }

                $dispatcherDefinition->addMethodCall('addListenerService', array($event['event'], array($id, $event['method']), $priority));
            }
        }

        foreach ($container->findTaggedServiceIds('ayamel.filesystem.event_subscriber') as $id => $attributes) {
            // We must assume that the class value has been correcly filled, even if the service is created by a factory
            $class = $container->getDefinition($id)->getClass();

            $refClass = new \ReflectionClass($class);
            $interface = 'Symfony\Component\EventDispatcher\EventSubscriberInterface';
            if (!$refClass->implementsInterface($interface)) {
                throw new \InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $id, $interface));
            }

            $dispatcherDefinition->addMethodCall('addSubscriberService', array($id, $class));
        }
    }
}
