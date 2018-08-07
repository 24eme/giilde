<?php

class PieceDocument
{
	protected $document = null;
    protected $hash;

    public function __construct(acCouchdbDocument $document) 
    {
        $this->document = $document;
        $this->hash = $document->getPieces()->getHash();
    }

    public function clearPieces(){
    	$this->document->remove('pieces');
    	$this->document->add('pieces');
    }
    
    public function generatePieces() 
    {
    	$this->clearPieces();
        $this->document->set($this->hash, $this->document->getAllPieces());
    }
}