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

namespace SlmCmfKernel\Parser;

use Zend\EventManager\EventManagerInterface as EventManager;
use Zend\EventManager\Event;
use Zend\Navigation\Navigation as Collection;
use Zend\Navigation\Page\Mvc as Page;

use SlmCmfKernel\Model\PageCollectionInterface;
use SlmCmfKernel\Model\PageCollection;
use SlmCmfKernel\Model\PageInterface;

use SlmCmfKernel\Exception;

/**
 * Description of Navigation
 */
class Navigation
{
    /**
     * @var EventManager
     */
    protected $events;

    public function setEventManager(EventManager $events)
    {
        $this->events = $events;
    }

    /**
     * Parse a PageCollection into a navigation structure
     *
     * @param  PageCollection $pages
     * @return array
     */
    public function parse(PageCollectionInterface $pages, $returnArray = false)
    {
        $navigation = ($returnArray) ? array() : new Collection;

        foreach ($pages as $page) {
            $navPage = $this->parsePage($page);

            if ($page->hasChildren()) {
                $collection = $page->getChildren();
                $navChilds  = $this->parse($collection);

                $navPage->addPages($navChilds);
            }

            if ($returnArray) {
                $navigation[] = $navPage;
            } else {
                $navigation->addPage($navPage);
            }
        }

        return $navigation;
    }

    /**
     * Parse a single page into a navigation configuration
     *
     * @param  Page $page
     * @return array
     * @throws Exception\RuntimeException
     */
    public function parsePage(PageInterface $page)
    {
        $meta = $page->getMetaData();

        $navPage = Page::factory(array(
            'type'  => 'mvc',
            'route' => (string) $page->getId(),
            'label' => $meta->getNavigationTitle()
        ));

        $event = new Event;
        $event->setName(__FUNCTION__ . '.' . $page->getModule());
        $event->setTarget($this);
        $event->setParams(array(
            'page'       => $page,
            'navigation' => $navPage
        ));
        $this->events->trigger($event);

        return $navPage;
    }
}