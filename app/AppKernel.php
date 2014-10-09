<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new AC\WebServicesBundle\ACWebServicesBundle(),
            new Ayamel\ResourceBundle\AyamelResourceBundle(),
            new Ayamel\ApiBundle\AyamelApiBundle(),
            new Ayamel\FilesystemBundle\AyamelFilesystemBundle(),
            new AC\TranscodingBundle\ACTranscodingBundle(),
            new OldSound\RabbitMqBundle\OldSoundRabbitMqBundle(),
            new Ayamel\TranscodingBundle\AyamelTranscodingBundle(),
            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
            new Ayamel\YouTubeBundle\AyamelYouTubeBundle(),
            new AC\MediaInfoBundle\ACMediaInfoBundle(),
            new Ayamel\MediaInfoBundle\AyamelMediaInfoBundle(),
            new Ornicar\ApcBundle\OrnicarApcBundle(),
            new FOS\ElasticaBundle\FOSElasticaBundle(),
            new Ayamel\SearchBundle\AyamelSearchBundle(),
            new Socloz\MonitoringBundle\SoclozMonitoringBundle()
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
