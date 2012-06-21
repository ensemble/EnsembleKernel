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