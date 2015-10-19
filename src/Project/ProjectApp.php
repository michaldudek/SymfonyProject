<?php
namespace Project;

use MD\Flavour\Kernel;

/**
 * Project application kernel.
 *
 * @author Michał Pałys-Dudek <michal@michaldudek.pl>
 */
class ProjectApp extends Kernel
{
    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function registerBundles()
    {
        $bundles = array(
            new \MD\Flavour\FlavourBundle(),
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Knit\Bundle\KnitBundle(),

            // put any bundles that your application depends on here
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new \Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new \Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
        }

        return $bundles;
    }
}
