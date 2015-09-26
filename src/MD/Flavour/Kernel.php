<?php
namespace MD\Flavour;

use MD\Foundation\Debug\Debugger;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as HttpKernel;

abstract class Kernel extends HttpKernel
{
    protected $cacheDir;

    protected $logDir;

    protected $configDir;

    protected $projectDir;

    protected function prepareContainer(ContainerBuilder $container)
    {
        parent::prepareContainer($container);
        
        $loader = $this->getContainerLoader($container);

        $loader->load($this->getConfigDir() .'/parameters.yml');
        $loader->load($this->getRootDir() .'/Resources/config/config_'.$this->getEnvironment().'.yml');
        $loader->load($this->getRootDir() .'/Resources/services/services.yml');

        $this->configure($container, $loader);
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        // noop
    }

    protected function configure(ContainerBuilder $container, LoaderInterface $loader)
    {
        // to be implemented in the application
    }

    public function getName()
    {
        if (!$this->name) {
            $this->name = Debugger::getClass($this, true);
        }

        return $this->name;
    }

    public function getProjectDir()
    {
        if (!$this->projectDir) {
            $this->projectDir = realpath($this->getRootDir() .'/../..');
        }

        return $this->projectDir;
    }

    public function getCacheDir()
    {
        if (!$this->cacheDir) {
            $this->cacheDir = $this->getProjectDir() .'/.cache/'. $this->getEnvironment();
        }

        return $this->cacheDir;
    }

    public function getLogDir()
    {
        if (!$this->logDir) {
            $this->logDir = $this->getProjectDir() .'/logs';
        }

        return $this->logDir;
    }

    /**
     * Returns path to the configuration dir (where all config files are stored).
     *
     * @return string
     */
    public function getConfigDir()
    {
        if (!$this->configDir) {
            $this->configDir = $this->getProjectDir() .'/config';
        }

        return $this->configDir;
    }

    protected function getKernelParameters()
    {
        $parameters = parent::getKernelParameters();

        return array_merge($parameters, [
            'kernel.not_debug' => !$this->debug,
            'kernel.project_dir' => $this->getProjectDir(),
            'kernel.config_dir' => $this->getConfigDir()
        ]);
    }
}
