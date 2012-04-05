<?php

namespace SlmCmfBase\Entity;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\ORM\Mapping as ORM,
    Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="pages")
 * @ORM\Entity(repositoryClass="Gedmo\Tree\Entity\Repository\NestedTreeRepository")
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
     * @ORM\ManyToOne(targetEntity="SlmCmfBase\Entity\Page", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     * @var Page
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="SlmCmfBase\Entity\Page", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     * @var ArrayCollection
     */
    protected $children;

    /**
     * @ORM\OneToOne(targetEntity="SlmCmfBase\Entity\MetaData")
     * @ORM\JoinColumn(name="id", referencedColumnName="id")
     * @var MetaData
     */
    protected $metadata;

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

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    protected $visible;

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

    public function getMetadata ()
    {
        return $this->metadata;
    }

    public function setMetadata (MetaData $metadata)
    {
        $this->metadata = $metadata;
    }

    public function getRoute ()
    {
        return $this->route;
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

    public function getVisible ()
    {
        return $this->visible;
    }

    public function setVisible ($visible)
    {
        $this->visible = $visible;
    }

    public function getDomain ()
    {
        return $this->domain;
    }

    public function setDomain ($domain)
    {
        $this->domain = $domain;
    }
}