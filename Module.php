<?php

namespace SlmCmfKernel;

use Zend\ModuleManager\Feature;
use Zend\EventManager\Event;

class Module implements
    Feature\AutoloaderProviderInterface,
    Feature\ServiceProviderInterface,
    Feature\ConfigProviderInterface,
    Feature\BootstrapListenerInterface
{    
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
        return include __DIR__ . '/config/services.config.php';
    }
    
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function onBootstrap(Event $e)
    {
        $app = $e->getParam('application');
        $sm  = $app->getServiceManager();
        $em  = $app->events();
        
        $config = $sm->get('configuration');
        $config = $config['slmcmf_kernel'];
        
        if ($config['pages_parse']) {
            $listener = $sm->get('SlmCmfKernel\Listener\ParsePages');
            $em->attach('route', $listener, 1000);
            
            if ($config['page_load']) {
                $listener = $sm->get('SlmCmfKernel\Listener\LoadPage');
                $em->attach('dispatch', $listener, 1000);
            }

            $em = $em->getSharedManager();

            // Parse page tree for routes and navigation structure
            $routeListener      = $sm->get('SlmCmfKernel\Listener\Parse\ParseRoutes');
            $navigationListener = $sm->get('SlmCmfKernel\Listener\Parse\ParseNavigation');
            $em->attach('SlmCmfKernel', 'parse', $routeListener);
            $em->attach('SlmCmfKernel', 'parse', $navigationListener);
        }
    }
}
