<?php

namespace SlmCmfBase;

use Zend\Module\Manager,
    Zend\EventManager\StaticEventManager,
    Zend\EventManager\Event,
    Zend\Module\Consumer\AutoloaderProvider;

class Module implements AutoloaderProvider
{
    protected $config;

    public function init(Manager $moduleManager)
    {
        $events = StaticEventManager::getInstance();
        $events->attach('bootstrap', 'bootstrap', array($this, 'addRouterListener'));

        $moduleManager->events()->attach('loadModules.post', array($this, 'modulesLoaded'));
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
    
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function modulesLoaded(Event $e)
    {
        $config = $e->getConfigListener()->getMergedConfig();
        $this->config = $config['slmcmfbase'];
    }
    
    public function addRouterListener(Event $e)
    {
        $app     = $e->getParam('application');
        $locator = $app->getLocator();

        if ($this->config['load_routes']) {
            $routerListener = $locator->get('SlmCmfBase\Listener\AddRoutesFromDb');
            $app->events()->attach('route', $routerListener, 1000);

            if ($this->config['load_page']) {
               $pageListener   = $locator->get('SlmCmfBase\Listener\LoadPageFromRouteMatch');
               $app->events()->attach('dispatch', $pageListener, 1000);
            }
        }
    }
}
