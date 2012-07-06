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
 * @author      Jurian Sluiman <jurian@soflomo.com>
 * @copyright   2012 Soflomo http://soflomo.com.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://ensemble.github.com
 */
namespace Ensemble\Kernel;

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
        $config = $config['ensemble_kernel'];

        if ($config['pages_parse']) {
            $listener = $sm->get('Ensemble\Kernel\Listener\ParsePages');
            $em->attach('route', $listener, 1000);

            if ($config['page_load']) {
                $listener = $sm->get('Ensemble\Kernel\Listener\LoadPage');
                $em->attach('dispatch', $listener, 1000);
            }

            $em = $em->getSharedManager();

            // Parse page tree for routes and navigation structure
            $routeListener      = $sm->get('Ensemble\Kernel\Listener\Parse\ParseRoutes');
            $navigationListener = $sm->get('Ensemble\Kernel\Listener\Parse\ParseNavigation');
            $em->attach('Ensemble\Kernel', 'parse', $routeListener);
            $em->attach('Ensemble\Kernel', 'parse', $navigationListener);

            // Inject page title into head
            $headTitleListener  = $sm->get('Ensemble\Kernel\Listener\Load\HeadTitle');
            $em->attach('Ensemble\Kernel', 'loadPage', $headTitleListener);

            // Set loaded page to active
            $setActiveListener  = $sm->get('Ensemble\Kernel\Listener\Load\SetActive');
            $em->attach('Ensemble\Kernel', 'loadPage', $setActiveListener);
        }
    }
}
