<?php

namespace SlmCmfKernel;

use Zend\ModuleManager\Feature;
use Zend\EventManager\Event;

use SlmCmfKernel\Listener;
use SlmCmfKernel\Router\Parser;

class Module implements
    Feature\InitProviderInterface,
    Feature\AutoloaderProviderInterface,
    Feature\ServiceProviderInterface,
    Feature\ConfigProviderInterface,
    Feature\BootstrapListenerInterface
{
    protected $options;

    public function init($manager = null)
    {
        $manager->events()->attach('loadModules.post', array($this, 'modulesLoaded'));
    }
    
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    
    public function getServiceConfiguration()
    {
        return array(
            'factories' => array(
                'slmCmfAddRoutesListener' => function ($sm) {
                    $di = $sm->get('Di');
                    $em = $di->get('doctrine_em');
                    
                    $repository = $em->getRepository('SlmCmfKernel\Entity\Page');
                    $parser     = $sm->get('slmCmfRouteParser');
                    $events     = $sm->get('EventManager');
                    $events->setIdentifiers(array(
                        __CLASS__,
                        get_class($repository),
                        'SlmCmfKernel\Listener\AddRoutesFromDb'
                    ));
                    $repository->setEventManager($events);
                    
                    $listener   = new Listener\AddRoutesFromDb($repository, $parser, $events);
                    
                    $config  = $sm->get('config');
                    if (true === $config['slmcmf_kernel']['cache']) {
                        $key   = $config['slmcmf_kernel']['cache_key'];
                        $cache = $sm->get($key);
                        $listener->setCache($cache);
                    }
                    
                    return $listener;
                },
                'slmCmfLoadPageListener' => function ($sm) {
                    $di = $sm->get('Di');
                    $em = $di->get('doctrine_em');
                    
                    $repository = $em->getRepository('SlmCmfKernel\Entity\Page');
                    $listener   = new Listener\LoadPageFromRouteMatch($repository);
                    return $listener;
                },
                'slmCmfRouteParser' => function ($sm) {
                    $config = $sm->get('config');
                    $routes = $config['cmf_routes'];
                    
                    $parser = new Parser($routes);
                    return $parser;
                }
            ),
        );
    }
    
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function onBootstrap(Event $e)
    {
        $this->attachListeners($e);
    }

    public function modulesLoaded(Event $e)
    {
        $options = $e->getConfigListener()->getMergedConfig();
        $this->options = $options['slmcmf_kernel'];
    }
    
    public function attachListeners(Event $e)
    {
        $app = $e->getParam('application');
        $sm  = $app->getServiceManager();
        $em  = $app->events();

        if ($this->options['load_routes']) {
            $routerListener = $sm->get('slmCmfAddRoutesListener');
            $em->attach('route', $routerListener, 1000);

            if ($this->options['load_page']) {
               $pageListener   = $sm->get('slmCmfLoadPageListener');
               $em->attach('dispatch', $pageListener, 1000);
            }
        }
    }
}
