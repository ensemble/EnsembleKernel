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
 * @copyright  Copyright (c) 2009-2011 Soflomo (http://www.soflomo.com)
 * @license    http://unlicense.org Unlicense
 */

namespace SlmCmfKernel\Listener;

use Zend\Mvc\MvcEvent;

use SlmCmfKernel\Repository\Page as Repository;
use SlmCmfKernel\Router\Parser;
use Zend\EventManager\EventManagerInterface;

use Zend\Cache\Storage\Adapter as Cache;

/**
 * Description of Listener
 *
 * @package    SlmCmfKernel
 * @subpackage Listener
 * @author     Jurian Sluiman <jurian@soflomo.com>
 */
class AddRoutesFromDb
{
    const CACHE_KEY = 'SlmCmfBase\Listener\AddRoutesFromDb';
    
    /**
     *
     * @var Repository
     */
    protected $repository;
    
    /**
     *
     * @var Parser
     */
    protected $parser;
    
    /**
     *
     * @var EventManagerInterface
     */
    protected $events;
    
    /**
     *
     * @var Cache
     */
    protected $cache;
    
    public function __construct (Repository $repository, Parser $parser, EventManagerInterface $events)
    {
        $this->repository = $repository;
        $this->parser     = $parser;
        $this->events     = $events;
    }
    
    public function setCache (Cache $cache)
    {
        $this->cache = $cache;
    }
    
    public function __invoke (MvcEvent $e)
    {
        if (null === $this->cache || false !== ($routes = $this->cache->getItem(self::CACHE_KEY))) {
            $pages  = $this->repository->getRootNodes();
            $routes = $this->parser->parse($pages);
            
            if (null !== $this->cache) {
                $this->cache->setItem(self::CACHE_KEY, $routes);
            }
        }
        
        $router = $e->getRouter();
        $router->addRoutes($routes);
    }
}
