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

namespace Ensemble\Kernel\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\MvcEvent;

/**
 * Default listener aggregate
 *
 * @package    Ensemble\Kernel
 */
class DefaultListenerAggregate implements ListenerAggregateInterface
{
    /**
     * @var array
     */
    protected $listeners = array();

    /**
     * @var array
     */
    protected $config = array();

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceManager;

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function setServiceManager(ServiceLocatorInterface $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Attach one or more listeners
     *
     * @param  EventManagerInterface $em
     * @return DefaultListenerAggregate
     */
    public function attach(EventManagerInterface $em)
    {
        $config = $this->config;
        $sm     = $this->getServiceManager();

        if ($config['pages_parse']) {
            $this->attachPagesParseListeners($em, $sm);

            if ($config['page_load']) {
                $this->attachPageLoadListeners($em, $sm);
            }
        }
    }

    /**
     * Detach all previously attached listeners
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $key => $listener) {
            $detached = false;
            if ($listener === $this) {
                continue;
            }
            if ($listener instanceof ListenerAggregateInterface) {
                $detached = $listener->detach($events);
            } elseif ($listener instanceof CallbackHandler) {
                $detached = $events->detach($listener);
            }

            if ($detached) {
                unset($this->listeners[$key]);
            }
        }
    }

    protected function attachPagesParseListeners(EventManagerInterface $em, $sm)
    {
        $parsePagesListener = $sm->get('Ensemble\Kernel\Listener\ParsePages');
        $this->listeners[]  = $em->attach(MvcEvent::EVENT_ROUTE, $parsePagesListener, 1000);

        $em = $em->getSharedManager();

        // Parse page tree for routes and navigation structure
        $routeListener      = $sm->get('Ensemble\Kernel\Listener\Parse\ParseRoutes');
        $navigationListener = $sm->get('Ensemble\Kernel\Listener\Parse\ParseNavigation');
        $this->listeners[]  = $em->attach('Ensemble\Kernel', 'parse', $routeListener);
        $this->listeners[]  = $em->attach('Ensemble\Kernel', 'parse', $navigationListener);
    }

    public function attachPageLoadListeners(EventManagerInterface $em, $sm)
    {
        $loadPageListener  = $sm->get('Ensemble\Kernel\Listener\LoadPage');
        $this->listeners[] = $em->attach(MvcEvent::EVENT_DISPATCH, $loadPageListener, 1000);

        $em = $em->getSharedManager();

        // Inject page title into head
        $headTitleListener  = $sm->get('Ensemble\Kernel\Listener\Load\HeadTitle');
        $this->listeners[]  = $em->attach('Ensemble\Kernel', 'loadPage', $headTitleListener);

        // Set loaded page to active
        $setActiveListener  = $sm->get('Ensemble\Kernel\Listener\Load\SetActive');
        $this->listeners[]  = $em->attach('Ensemble\Kernel', 'loadPage', $setActiveListener);
    }
}
