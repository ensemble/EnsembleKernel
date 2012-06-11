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
 * @package    SlmCmfKernel
 * @copyright  Copyright (c) 2009-2012 Soflomo (http://soflomo.com)
 * @license    http://unlicense.org Unlicense
 */

use SlmCmfKernel\Listener;
use SlmCmfKernel\Parser;

return array(
    'factories' => array(
        'SlmCmfKernel\Listener\ParsePages' => function ($sm) {
            $events   = $sm->get('EventManager');
            
            $listener = new Listener\ParsePages;
            $listener->setEventManager($events);
            
            return $listener;
        },
        'SlmCmfKernel\Listener\LoadPage' => function ($sm) {
            $events   = $sm->get('EventManager');
            
            $listener = new Listener\LoadPage;
            $listener->setEventManager($events);
            
            return $listener;
        },
        'SlmCmfKernel\Listener\Parse\ParseRoutes' => function ($sm) {
            $parser   = $sm->get('SlmCmfKernel\Parser\Route');

            $listener = new Listener\Parse\ParseRoutes;
            $listener->setParser($parser);
            
            $config = $sm->get('config');
            if ($config['slmcmf_kernel']['cache_routes']) {
                $name  = $config['slmcmf_kernel']['cache_routes_key'];
                $cache = $sm->get($name);
                $listener->setCache($cache);
            }
            
            return $listener;
        },
        'SlmCmfKernel\Listener\Parse\ParseNavigation' => function ($sm) {
            $parser   = $sm->get('SlmCmfKernel\Parser\Navigation');
            $renderer = $sm->get('Zend\View\Renderer\PhpRenderer');
            $helper   = $renderer->plugin('navigation');

            $listener = new Listener\Parse\ParseNavigation;
            $listener->setParser($parser);
            $listener->setViewHelper($helper);
            
            $config = $sm->get('config');
            if ($config['slmcmf_kernel']['cache_navigation']) {
                $name  = $config['slmcmf_kernel']['cache_navigation_key'];
                $cache = $sm->get($name);
                $listener->setCache($cache);
            }
            
            return $listener;
        },
        'SlmCmfKernel\Parser\Route' => function ($sm) {
            $config = $sm->get('config');
            $routes = $config['cmf_routes'];

            $parser = new Parser\Route;
            $parser->setModuleRoutes($routes);
            return $parser;
        },
        'SlmCmfKernel\Parser\Navigation' => function ($sm) {
            $events = $sm->get('EventManager');
            
            $parser = new Parser\Navigation;
            $parser->setEventManager($events);
            
            return $parser;
        }
    ),
);
