<?php

namespace SlmCmfKernel\Entity;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\ORM\Mapping as ORM,
    Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="page")
 * @ORM\Entity(repositoryClass="SlmCmfKernel\Repository\Page")
 */
class Page
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var integer
     */
    protected $id;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     * @var integer
     */
    protected $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     * @var integer
     */
    protected $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     * @var integer
     */
    protected $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     * @var integer
     */
    protected $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="SlmCmfKernel\Entity\Page", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     * @var Page
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="SlmCmfKernel\Entity\Page", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     * @var ArrayCollection
     */
    protected $children;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $route;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $module;

    /**
     * @ORM\Column(type="integer")
     * @var string
     */
    protected $moduleId;

    public function __construct ()
    {
        $this->children = new ArrayCollection;
    }

    public function getId ()
    {
        return (int) $this->id;
    }

    public function getLft ()
    {
        return $this->lft;
    }

    public function setLft ($lft)
    {
        $this->lft = $lft;
    }

    public function getLvl ()
    {
        return $this->lvl;
    }

    public function setLvl ($lvl)
    {
        $this->lvl = $lvl;
    }

    public function getRgt ()
    {
        return $this->rgt;
    }

    public function setRgt ($rgt)
    {
        $this->rgt = $rgt;
    }

    public function getRoot ()
    {
        return $this->root;
    }

    public function setRoot ($root)
    {
        $this->root = $root;
    }

    public function getParent ()
    {
        return $this->parent;
    }

    public function setParent (Page $parent)
    {
        $this->parent = $parent;
    }

    public function getChildren ()
    {
        return $this->children;
    }

    public function hasChildren ()
    {
        return (bool) count($this->children);
    }

    public function setChildren ($children)
    {
        $this->children = $children;
    }

    public function getRoute ($includeParent = false)
    {
        if (false === $includeParent) {
            return $this->route;
        }
        
        $page  = $this;
        $route = array();
        do {
            $route[] = $page->getRoute(false);
        } while (($page = $page->getParent()) instanceof self);
        
        $route = array_reverse($route);
        return implode('/', $route);
    }

    public function setRoute ($route)
    {
        $this->route = $route;
    }

    public function getModule ()
    {
        return $this->module;
    }

    public function setModule ($module)
    {
        $this->module = $module;
    }

    public function getModuleId ()
    {
        return $this->moduleId;
    }

    public function setModuleId ($moduleId)
    {
        $this->moduleId = $moduleId;
    }
}