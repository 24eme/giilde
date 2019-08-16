<?php

class ArchivageDocument implements iLock
{
    protected $document;
    protected $format;

    public function __construct(acCouchdbDocument $document, $format = "%05d")
    {
        $this->document = $document;
        $this->format = $format;
    }

    public function reset() {
        $this->document->numero_archive = null;
    }
    
    public function preSave() {
        if ($this->document->isArchivageCanBeSet()) {
            $this->archiver();
        }
    }

    public function postSave() {
        
    }

    public function getDocument() {

        return $this->document;
    }

    public function archiver() {
        if ($this->document->numero_archive) {
            return;
        }
        //echo sprintf("DEBUT;%s;%s;\n", date('Y-m-d H:i:s'), $this->document->get('_id'));
        Lock::runLock($this, $this->getType());
        //echo sprintf("FIN;%s;%s;%s\n", date('Y-m-d H:i:s'), $this->document->get('_id'), $this->document->numero_archive);
    }

    public function getType() {
    	if(method_exists($this->document, 'getTypeArchive')) {

            return $this->document->getTypeArchive();
        }

    	return $this->document->toJson()->type;
    }

    public function getCampagne() {
        if(method_exists($this->document, 'getCampagneArchive') || $this->document->exist('campagne_archive')) {

            return $this->document->getCampagneArchive();
        }

        return $this->document->campagne;
    }

    public function getLastNumeroArchive($type, $campagne) {
        if(method_exists($this->document, 'getLastNumeroArchive')) {

            return $this->document->getCampagneArchive($type, $campagne);
        }

        return ArchivageAllView::getInstance()->getLastNumeroArchiveByTypeAndCampagne($type, $campagne);
    }

    public function executeLock($type = null) {
        $last_numero = $this->getLastNumeroArchive($type, $this->getCampagne());
        //echo sprintf("RECUPERATION DU DERNIER;%s;%s;%s\n", date('Y-m-d H:i:s'), $this->document->get('_id'), $this->getCampagne().":".$last_numero.":".$this->document->_rev);
        $this->document->numero_archive = sprintf($this->format, $last_numero+1);
        
        return array('value' => $this->getCampagne().' '.$this->document->numero_archive, 'key' => $type, 'docid' => $this->document->_id);
    }
}
