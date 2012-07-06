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

namespace Ensemble\Kernel\Listener\Parse;

use Zend\Cache\Storage\Adapter\AdapterInterface as Cache;
use Ensemble\Kernel\Parser\Navigation as Parser;
use Zend\Navigation\Page\Mvc as Page;
use Zend\View\Helper\Navigation as Helper;
use Zend\Mvc\MvcEvent as Event;

/**
 * Description of ParseNavigation
 */
class ParseNavigation
{
    const CACHE_KEY = 'EnsembleKernel_Listener_ParseNavigation';

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
        $router = $e->getRouter();
        Page::setDefaultRouter($router);

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