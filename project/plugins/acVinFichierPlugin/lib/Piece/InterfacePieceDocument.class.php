<?php

interface InterfacePieceDocument
{
	public function getAllPieces();
	public function generatePieces();
	public function generateUrlPiece($source = null);
	public static function getUrlVisualisationPiece($id, $admin = false);
	public static function isVisualisationMasterUrl($admin = false);
	public static function isPieceEditable($admin = false);
	public static function getUrlGenerationCsvPiece($id, $admin = false);
}