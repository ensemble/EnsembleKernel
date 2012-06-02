<?php

namespace SlmCmfKernel\Router;

use SlmCmfKernel\Entity\Page;
use Traversable;

use SlmCmfKernel\Exception;

class Parser
{
    protected $routes;
    
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * Parse pages to route array
     *
     * @return array
     */
    public function parse(array $pages)
    {
        $routes = array();
        foreach ($pages as $page) {
            $route = $this->parsePage($page);
            $routes[$page->getId()] = $route;
            
            if ($page->hasChildren()) {
                $children    = $page->getChildren()->toArray();
                $childRoutes = $this->parse($children);
                
                $routes += $childRoutes;
            }
        }

        return $routes;
    }
    
    public function parsePage(Page $page)
    {
        $module = $this->getModuleRoute($page->getModule());
        
        $type = 'literal';
        if (isset($module['type'])) {
            $type = $module['type'];
        }
        
        $route = $page->getRoute(true);
        $route = '/' . trim($route, '/');
        if (isset($module['options']['route'])) {
            $route .= $module['options']['route'];
        }

        if (!isset($module['options']['defaults'])) {
            throw new Exception\RuntimeException(sprintf(
                'Module %s should provide defaults in route to dispatch controller',
                $page->getModule()
            ));
        }
        $defaults = array('page-id' => $page->getId())
                  + $module['options']['defaults'];
        
        $route = array(
            'type' => $type,
            'options' => array(
                'route'    => $route,
                'defaults' => $defaults
            ),
            'may_terminate' => true
        );
        
        if (isset($module['options']['constraints'])) {
            $route['options']['constraints'] = $module['options']['constraints'];
        }
        
        if (isset($module['child_routes'])) {
            $route['child_routes'] = $module['child_routes'];
        }
        
        return $route;
    }

    /**
     * Get config of route segments for module
     * 
     * @param string $name
     * @return Config
     */
    protected function getModuleRoute($name)
    {
        if(!isset($this->routes[$name])) {
            throw new Exception\RuntimeException(sprintf(
                'No module routes found for module %s',
                $name
            ));
        }

        return $this->routes[$name];
    }
}