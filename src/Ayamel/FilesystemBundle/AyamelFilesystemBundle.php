<?php

namespace Ayamel\FilesystemBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Ayamel\FilesystemBundle\DependencyInjection\Compiler\RegisterFilesystemEventListenersPass;
use Ayamel\FilesystemBundle\DependencyInjection\Compiler\RegisterFilesystemAnalyzersPass;

class AyamelFilesystemBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterFilesystemEventListenersPass());
        $container->addCompilerPass(new RegisterFilesystemAnalyzersPass());
    }

}
