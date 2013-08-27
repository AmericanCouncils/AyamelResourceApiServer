<?php
namespace Ayamel\TranscodingBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
 * Extension merges in some extra configuration to use during testing, because
 * testing video transcodes all the time takes a lot of time.
 *
 * @package AyamelTranscodingBundle
 * @author Evan Villemez
 **/
class AyamelTranscodingExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('config.presets.yml');
        $loader->load('config.preset_map.yml');

        //if in test environment, modify the config for presets and preset maps
    	if ('test' === $container->getParameter('kernel.environment')) {
    		//add test preset/adapter
    		$def = new Definition('AC\Transcoding\Tests\Mock\PhpTextAdapter');
    		$def->addTag('transcoding.adapter');
    		$container->setDefinition('ayamel.test.transcoding_adapter', $def);

    		$def = new Definition('AC\Transcoding\Tests\Mock\TextToLowerCasePreset');
    		$def->addTag('transcoding.preset');
            $def->setScope('prototype');
    		$container->setDefinition('ayamel.test.transcoding_preset', $def);

    		//append fake config
    		$presets = $container->getParameter('ayamel.transcoding.presets');
    		$presets['text_to_lower'] = array(
		        "preset_service" => "ayamel.test.transcoding_preset",
		        "tag" => "low",
		        "extension" => "txt",
		        "representation" => "transcoding",
		        "quality" => "0"    			
    		);
    		$container->setParameter('ayamel.transcoding.presets', $presets);

    		$map = $container->getParameter('ayamel.transcoding.preset_map');
    		$map['text/plain'] = array('text_to_lower');
    		$container->setParameter('ayamel.transcoding.preset_map', $map);
    	}
    }
}
