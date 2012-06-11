<?php

/*
 * This is free and unencumbered software released into the public domain.
 * 
 * Anyone is free to copy, modify, publish, use, compile, sell, or
 * distribute this software, either in source code form or as a compiled
 * binary, for any purpose, commercial or non-commercial, and by any
 * means.
 * 
 * In jurisdictions that recognize copyright laws, the author or authors
 * of this software dedicate any and all copyright interest in the
 * software to the public domain. We make this dedication for the benefit
 * of the public at large and to the detriment of our heirs and
 * successors. We intend this dedication to be an overt act of
 * relinquishment in perpetuity of all present and future rights to this
 * software under copyright law.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR
 * OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
 * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 * 
 * For more information, please refer to <http://unlicense.org/>
 * 
 * @package
 * @copyright  Copyright (c) 2009-2012 Soflomo (http://soflomo.com)
 * @license    http://unlicense.org Unlicense
 */

namespace SlmCmfKernel\Parser;

use SlmCmfKernel\Model\PageCollectionInterface;
use SlmCmfKernel\Model\PageCollection;
use SlmCmfKernel\Model\PageInterface as Page;

use SlmCmfKernel\Exception;

/**
 * Description of Route
 */
class Route
{
    protected $routes;
    
    /**
     * Add routes for module segments
     * 
     * @param array $routes 
     */
    public function setModuleRoutes(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * Parse a PageCollection into a list of routes
     * 
     * @param  PageCollection $pages
     * @return array
     */
    public function parse(PageCollectionInterface $pages)
    {
        $routes = array();
        foreach ($pages as $page) {
            $route = $this->parsePage($page);
            $routes[$page->getId()] = $route;
            
            if ($page->hasChildren()) {
                $children    = $page->getChildren()->toArray();
                $collection  = new PageCollection($children);
                $childRoutes = $this->parse($collection);
                
                $routes += $childRoutes;
            }
        }

        return $routes;
    }
    
    /**
     * Parse a single page into a route configuration
     * 
     * @param  Page $page
     * @return array
     * @throws Exception\RuntimeException 
     */
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
            throw new Exception\RouteConfigurationException(sprintf(
                'Module %s should provide defaults in route to dispatch a controller',
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
     * @param  string $name
     * @return array
     */
    protected function getModuleRoute($name)
    {
        if(!isset($this->routes[$name])) {
            throw new Exception\RouteConfigurationException(sprintf(
                'No module routes found for module %s',
                $name
            ));
        }

        return $this->routes[$name];
    }
}