<?php
/**
 * Copyright (c) 2012 Soflomo http://soflomo.com.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of the
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package     Ensemble\Kernel
 * @author      Jurian Sluiman <jurian@juriansluiman.nl>
 * @copyright   2012 Soflomo http://soflomo.com.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://ensemble.github.com
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
                $collection  = $page->getChildren();
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