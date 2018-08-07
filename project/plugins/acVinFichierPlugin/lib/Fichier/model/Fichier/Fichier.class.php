<?php
/**
 * Model for Fichier
 *
 */

class Fichier extends BaseFichier implements InterfacePieceDocument {

    protected $piece_document = null;

    public function __construct() {
        parent::__construct();
        $this->initDocuments();
    }

    public function __clone() {
        parent::__clone();
        $this->initDocuments();
    }

    protected function initDocuments() {
        $this->piece_document = new PieceDocument($this);
    }

    public function constructId() {
        $this->set('_id', 'FICHIER-' . $this->identifiant . '-' . $this->fichier_id);
    }

    public function getNbFichier()
    {
    	return count($this->_attachments);
    }

    public function hasFichiers() {
    	return ($this->getNbFichier() > 0);
    }

    public function isMultiFichiers() {
    	return ($this->getNbFichier() > 1);
    }

    public function initDoc($identifiant) {
    	$this->identifiant = $identifiant;
    	$this->fichier_id = uniqid();
    	$this->date_depot = date('Y-m-d');
    	$this->visibilite = 1;
    }

    public function getEtablissementObject() {

    	return EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);
    }

    public function isPapier() {

    	return $this->exist('papier') && $this->get('papier');
    }

    public function getMime($file = null)
    {
    	if (!$file) {
    		foreach ($this->_attachments as $filename => $fileinfos) {
    			$file = $filename;
    		}
    	}
    	if ($file && $this->_attachments->exist($file)) {
    		$fileinfos = $this->_attachments->get($file)->toArray();
    		return $fileinfos['content_type'];
    	}
    	return null;
    }

    public function getFichiers()
    {
    	$fichiers = array();
    	foreach ($this->_attachments as $filename => $fileinfos) {
    		$fichiers[] = $filename;
    	}
    	return $fichiers;
    }

		public function getFichier($ext) {
			$fileinfos = $this->getFileinfos($ext);
			return ($fileinfos['filename'])? $this->getAttachmentUri($fileinfos['filename']) : null;
		}

    public function getFileinfos($ext)
    {
    	foreach ($this->_attachments as $filename => $fileinfos) {
    		if (preg_match('/([a-zA-Z0-9]*)\.([a-zA-Z0-9]*)$/', $filename, $m)) {
    			if (strtolower($m[2]) == strtolower($ext)) {
    				$fileinfos->add('filename', $filename);
    				return $fileinfos;
    			}
    		}
    	}
    	return null;
    }

	protected function doSave() {
		$this->piece_document->generatePieces();
	}

	public function storeFichier($file) {
		if (!is_file($file)) {
			throw new sfException($file." n'est pas un fichier valide");
		}
		$pathinfos = pathinfo($file);
		$extension = (isset($pathinfos['extension']) && $pathinfos['extension'])? strtolower($pathinfos['extension']): null;
		$fileName = ($extension)? uniqid().'.'.$extension : uniqid();
		$couchinfos = $this->getFileinfos($pathinfos['extension']);
		$store4real = true;
		if (isset($couchinfos['digest'])) {
			$digest = explode('-', $couchinfos['digest']);
			if ($digest[1] == base64_encode(hex2bin(md5_file($file)))) {
				$store4real = false;
			}else{
				$this->deleteFichier($couchinfos->getKey());
				$this->save();
			}
		}
		if ($store4real) {
			$mime = mime_content_type($file);
			$this->storeAttachment($file, $mime, $fileName);
		}
    $this->date_import = date('Y-m-d');
    return $store4real;
	}

	public static function convertXlsFile($file) {
		if (!is_file($file)) {
			throw new sfException($file." n'est pas un fichier valide");
		}
		$infos = pathinfo($file);
		$extension = (isset($infos['extension']) && $infos['extension'])? strtolower($infos['extension']): null;
		if (strtolower($extension) != 'xls') {
			throw new sfException($file." n'est pas un fichier xls");
		}
		$path = sfConfig::get('sf_cache_dir').'/xls2csv/';
		if (!is_dir($path)) {
			mkdir($path, 0770);
		}
		if (!is_dir($path)) {
			throw new sfException($path." n'a pas pu être créé");
		}

		$filename = uniqid().'.csv';

		exec('xls2csv '.$file.' > '.$path.$filename);

		if (!filesize($path.$filename)) {
			throw new sfException("xls2csv n'a pas pu convertir le fichier ".$file);
		}

		return $path.$filename;
	}

	public function deleteFichier($filename = null) {
		if (!$filename) {
			$this->remove('_attachments');
			$this->add('_attachments');
		} elseif ($this->_attachments->exist($filename)) {
			$this->_attachments->remove($filename);
		}
	}

	public function getDateDepotFormat($format = 'd/m/Y') {
		if ($this->date_depot) {
			$date = new DateTime($this->date_depot);
			return $date->format($format);
		}
		return null;
	}

    /**** PIECES ****/

    public function getAllPieces() {
    	$complement = ($this->isPapier())? '(Papier)' : '(Télédéclaration)';
    	return array(array(
    		'identifiant' => $this->getIdentifiant(),
    		'date_depot' => $this->getDateDepot(),
    		'libelle' => $this->getLibelle().' '.$complement,
    		'visibilite' => $this->getVisibilite(),
    		'mime' => null,
    		'source' => null,
    		'fichiers' => $this->getFichiers()
    	));
    }

    public function generatePieces() {
    	return $this->piece_document->generatePieces();
    }

    public function generateUrlPiece($source = null) {
    	return ($this->getNbFichier() > 0)? sfContext::getInstance()->getRouting()->generate('get_fichier', $this) : sfContext::getInstance()->getRouting()->generate('csvgenerate_fichier', $this);
    }

    public static function getUrlVisualisationPiece($id, $admin = false) {
		if(!$admin) {
			return null;
		}

		$fichier = FichierClient::getInstance()->find($id);
    	return sfContext::getInstance()->getRouting()->generate('upload_fichier', array('fichier_id' => $fichier->_id, 'sf_subject' => $fichier->getEtablissementObject()));
    }

    public static function getUrlGenerationCsvPiece($id, $admin = false) {
		if(!$admin) {
			return null;
		}

		$fichier = FichierClient::getInstance()->find($id);
    	return sfContext::getInstance()->getRouting()->generate('csvgenerate_fichier', $fichier);
    }

    public static function isVisualisationMasterUrl($admin = false) {
    	return false;
    }

    public static function isPieceEditable($admin = false) {
    	return false;
    }

    /**** FIN DES PIECES ****/

}
