<?php

namespace AC\MutateBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Ayamel\ACMutateBundle\DependencyInjection\RegisterTranscoderTagsPass;
use Ayamel\ACMutateBundle\DependencyInjection\RegisterApplicationTagsPass;

class ACMutateBundle extends Bundle {
	
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterTranscoderTagsPass());
        $container->addCompilerPass(new RegisterApplicationTagsPass());
    }

}