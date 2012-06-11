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

namespace SlmCmfKernel\Listener\Parse;

use Zend\Cache\Storage\Adapter\AdapterInterface as Cache;
use SlmCmfKernel\Parser\Navigation as Parser;
use Zend\View\Helper\Navigation as Helper;
use Zend\Mvc\MvcEvent as Event;

/**
 * Description of ParseNavigation
 */
class ParseNavigation
{
    const CACHE_KEY = 'SlmCmfKernel_Listener_ParseNavigation';
    
    protected $cache;
    protected $parser;
    protected $helper;
    
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;
    }
    
    public function setParser(Parser $parser)
    {
        $this->parser = $parser;
    }
    
    public function setViewHelper(Helper $helper)
    {
        $this->helper = $helper;
    }
    
    public function __invoke(Event $e)
    {
        if (null === $this->cache || null === ($routes = $this->cache->getItem(self::CACHE_KEY))) {
            $collection = $e->getTarget()->getPageCollection();
            $navigation = $this->parser->parse($collection);
            
            if (null !== $this->cache) {
                $this->cache->setItem(self::CACHE_KEY, $navigation);
            }
        }
        
        $this->helper->setContainer($navigation);
    }
}