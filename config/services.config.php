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

use SlmCmfKernel\Listener;
use SlmCmfKernel\Parser;
use SlmCmfKernel\Service;

use SlmCmfKernel\Exception;

return array(
    'factories' => array(
        'SlmCmfKernel\Service\Page' => function ($sm) {
            $config   = $sm->get('config');
            $config   = $config['slmcmf_kernel'];
            if (empty($config['page_service_class'])) {
                throw new Exception\PageServiceNotFoundException(
                    'No service manager key provided for an service adapter'
                );
            }
            
            $service  = $sm->get($config['page_service_class']);
            
            if (!$service instanceof Service\PageInterface) {
                throw new Exception\PageServiceNotFoundException(
                    'Instance of service adapter does not implement SlmCmfKernel\Service\PageInterface'
                );
            }
            return $service;
        },
        'SlmCmfKernel\Listener\ParsePages' => function ($sm) {
            $service  = $sm->get('SlmCmfKernel\Service\Page');
            $events   = $sm->get('EventManager');
            
            $listener = new Listener\ParsePages;
            $listener->setEventManager($events);
            $listener->setPageService($service);
            
            return $listener;
        },
        'SlmCmfKernel\Listener\LoadPage' => function ($sm) {
            $service  = $sm->get('SlmCmfKernel\Service\Page');
            $events   = $sm->get('EventManager');
            
            $listener = new Listener\LoadPage;
            $listener->setEventManager($events);
            $listener->setPageService($service);
            
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
