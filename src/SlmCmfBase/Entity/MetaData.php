<?php

namespace SlmCmfbase\Entity;

use Gedmo\Mapping\Annotation as Gedmo,
    Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="pages_metadata")
 */
class MetaData
{
    /**
     * @ORM\Id 
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var integer
     */
    protected $id;
    
    /**
     * @ORM\OneToOne(targetEntity="SlmCmfBase\Entity\Page")
     * @var SlmCmfBase\Entity\Page
     */
    protected $page;
    
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $title;
    
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $shortTitle;
    
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $description;
    
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $keywords;
    
    public function getTitle ()
    {
        return $this->title;
    }
    
    public function setTitle ($title)
    {
        $this->title = (string) $title;
        return $this;
    }
    
    public function getShortTitle ()
    {
        return $this->shortTitle;
    }
    
    public function setShortTitle ($shortTitle)
    {
        $this->shortTitle = (string) $shortTitle;
        return $this;
    }
    
    public function hasShortTitle ()
    {
        return !empty($this->shortTitle);
    }
    
    public function getDescription ()
    {
        return $this->description;
    }
    
    public function setDescription ($description)
    {
        $this->description = (string) $description;
        return $this;
    }
    
    public function getKeywords ()
    {
        return $this->keywords;
    }
    
    public function setKeywords ($keywords)
    {
        $this->keywords = (string) $keywords;
        return $this;
    }
}