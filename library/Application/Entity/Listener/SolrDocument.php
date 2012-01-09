<?php

namespace Application\Entity\Listener;

use Application\Entity,
    Doctrine\Common\EventSubscriber,
    Doctrine\ORM\Events,
    Doctrine\ORM\Event\OnFlushEventArgs,
    Doctrine\ORM\Event\PostFlushEventArgs,
    Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * This event listener checks when changed documents need to be merged with Solr
 *
 * @subpackage EntityListener
 */
class SolrDocument implements EventSubscriber
{

    /**
     * @var Doctrine\ORM\EntityManager
     */
    private $_em;

    /**
     *
     * @var SolrClient
     */
    private $_client;

    /**
     *
     * @var Zend_Config
     */
    private $_config;
    
    /**
     * 
     * Keep in track of a array of insert statements to commit to solr. You can't
     * add documents to solr in the preFlush event because they don't have unique
     * id's yet.
     * 
     * @var array 
     */
    private $_inserts = array();

    public function __construct(\Zend_Config $config) {
        $this->_config = $config;
    }
    
    /**
     * Returns the subscribed events
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(Events::onFlush, Events::postPersist, Events::postFlush);
    }

    /**
     * 
     * Merge entity changed with Solr from entities that are scheduled to be updated
     * or deleted.
     *
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {        
        $this->_client = new \SolrClient($this->_config->solr->connection->toArray());
        $this->_em = $eventArgs->getEntityManager();

        $uow = $this->_em->getUnitOfWork();

        $this->flushUpdates($uow->getScheduledEntityUpdates());
        $this->flushDeletes($uow->getScheduledEntityDeletions());
    }

    /**
     * Post flush is used for tracked insertions. Updates and deletes aren't neccasary
     * here cause they wouldn't be sheduled anymore and you would miss them.
     * 
     * @param PostFlushEventArgs $eventArgs 
     */
    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        if (count($this->_inserts) > 0) {
            $this->_client->addDocuments($this->_inserts);
            
            if(count($this->_inserts) >= $this->_config->solr->bulkindex->minDocs) {
                $this->_client->commit(
                        $this->_config->solr->bulkindex->segments, 
                        $this->_config->solr->bulkindex->waitFlush, 
                        $this->_config->solr->bulkindex->waitSearcher
                );                    
            } else {
                $this->_client->commit();
            }
            
            

            $this->_inserts = array(); //reset
        }
    }

    /**
     * Cause solr doesn't have a update mechanism first delete all update-marked
     * documents then re-insert them with the same ID.
     * 
     * @param array $entities 
     */
    private function flushUpdates($entities)
    {
        $deletes = array();
        $inserts = array();

        foreach ($entities as $entity) {
            if ($entity instanceof \Application_SolrDocument) {
                $deletes[] = $entity->id;
                $inserts[] = $entity->getInputDocument();
            }
        }

        if (count($deletes) > 0) {
            $this->_client->deleteByIds($deletes);
            $this->_client->commit();
            $this->_client->addDocuments($inserts);
            $this->_client->commit();
        }
    }

    /**
     * Delete all to-be-deleted documents from Solr
     * 
     * @param array $entities 
     */
    private function flushDeletes($entities)
    {
        $deletes = array();

        foreach ($entities as $entity) {
            if ($entity instanceof \Application_SolrDocument) {
                $deletes[] = $entity->id;
            }
        }

        if (count($deletes) > 0) {
            $this->_client->deleteByIds($deletes);
            $this->_client->commit();
        }
    }

    /**
     * When one entity persist track it for merging in Solr.
     * 
     * @param LifecycleEventArgs $eventArgs 
     */
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof \Application_SolrDocument) {
            $this->_inserts[] = $entity->getInputDocument();
        }
    }

}