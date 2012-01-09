<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping AS ORM;

include_once(__DIR__ . '/AbstractEntity.php');

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Article extends AbstractEntity implements \Application_SolrDocument
{
    /**
     * 
     * @var integer
     * 
     * @ORM\Column(type="integer",nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    
    /**
     * 
     * @var string
     * @ORM\Column(type="string",nullable=false)	 
     */
    protected $title;
    
    /**
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $content;
    
    /**
     *
     * @var \DateTime
     * @ORM\Column(type="date", nullable=false)
     */
    protected $insertDate;
    
    /**
     *
     * Last modification date
     * 
     * @var \DateTime
     * @ORM\Column(type="date", nullable=true)
     */
    protected $editDate;
    
    /**
     *
     * The user which is the author
     * 
     * @var string
     * @ORM\Column(type="string",nullable=false)	 
     */
    protected $author;
    
    /**
     *
     * @var string
     * @ORM\Column(type="string",nullable=false)	 
     */
    protected $source;
    
    /**
     *
     * @var string
     * @ORM\Column(type="string",nullable=false)	 
     */
    protected $url;
     
    /**
     * @ORM\PreUpdate
     */
    public function preUpdate() {
        $this->editDate = new \DateTime();        
    }

    /**
     * @ORM\PrePersist
     */    
    public function prePersist() {
        $this->insertDate = new \DateTime();
    }
    
    /**
     *
     * This function is for Solr indexing purposes.
     * 
     * @return \SolrInputDocument 
     */
    public function getInputDocument() {
        $doc = new \SolrInputDocument();

        $this->insertDate->setTimezone(new \DateTimeZone('Etc/Zulu'));
        
        $doc->addField('id', $this->id);
        $doc->addField('title', $this->title);
        $doc->addField('content', $this->content);
        $doc->addField('author', $this->author);
        $doc->addField('url', $this->url);        
        $doc->addField('insertdate', $this->insertDate->format('Y-m-d\TH:i:s\Z'));

        return $doc;
    }    
}
