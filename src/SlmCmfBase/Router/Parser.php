<?php

namespace SlmCmfBase\Router;

use SlmCmfBase\Exception,
    Traversable;

class Parser
{
    protected $routes;
    
    public function setRoutes(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * Parse pages to route array
     *
     * @return array
     */
    public function parse($pages)
    {
        if (!is_array($pages) && !$pages instanceof Traversable) {
            throw new Exception\InvalidArgumentException('Pages should be array or traversable');
        }
        
        $routes = array();
        foreach ($pages as $page) {
            
            $route = array(
                'type' => 'literal',
                'options' => array(
                    'route'    => $page->getRoute(),
                    'defaults' => array(
                        'page-id' => $page->getId()
                    ),
                ),
                'may_terminate' => false,
                'child_routes' => $this->getChildRoutes($page->getModule())
            );
            
            if ($page->hasChildren()) {
                $children = $page->getChildren();
                
                $route['child_routes'] += $this->parse($children);
            }
            
            $routes[$page->getId()] = $route;
        }

        return $routes;
    }

    /**
     * Get config of route segments for module
     * 
     * @param string $name
     * @return Config
     */
    protected function getChildRoutes($name)
    {
        if(!isset($this->routes[$name])) {
            throw new Exception\RuntimeException(sprintf(
                'No child module routes found for module %s',
                $name
            ));
        }

        return $this->routes[$name];
    }
}