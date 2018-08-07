<?php

abstract class Piece extends acCouchdbDocumentTree
{
	const MIME_PDF = 'application/pdf';
	const MIME_HTML = 'text/html';
	const LIMIT_HISTORY = 10;

	public function getUrl()
	{
		return $this->getDocument()->generateUrlPiece($this->source);
	}

	public static function getUrlVisualisation($id, $isadmin = false)
	{
		if (preg_match('/^([a-zA-Z0-9]+)-.*$/', $id, $m)) {
			$doc = $m[1];
			return $doc::getUrlVisualisationPiece($id, $isadmin);

		}
		return null;
	}

    public static function getUrlGenerationCsvPiece($id, $admin = false) {
    	if (preg_match('/^([a-zA-Z0-9]+)-.*$/', $id, $m)) {
			$doc = $m[1];
			return $doc::getUrlGenerationCsvPiece($id, $admin);

		}
		return null;
    }
	
	public static function isVisualisationMasterUrl($id, $isadmin = false)
	{
		if (preg_match('/^([a-zA-Z0-9]+)-.*$/', $id, $m)) {
			$doc = $m[1];
			return $doc::isVisualisationMasterUrl($isadmin);
		
		}
		return false;
	}
	
	public static function isPieceEditable($id, $isadmin = false)
	{
		if (preg_match('/^([a-zA-Z0-9]+)-.*$/', $id, $m)) {
			$doc = $m[1];
			return $doc::isPieceEditable($isadmin);
		
		}
		return false;
	}
}