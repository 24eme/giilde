<?php
class SocieteEnteteLatex extends GenericLatex
{
	protected $type;
	protected $options;

  	function __construct($societe, $options = array())
  	{
    	sfProjectConfiguration::getActive()->loadHelpers("Partial", "Url", "MyHelper");
    	$this->societe = $societe;
  	}

  	public function getLatexFileNameWithoutExtention()
  	{
    	return $this->getTEXWorkingDir().'societeentete_'.$this->societe->identifiant;
  	}

  	public function getLatexFileContents()
  	{
    	return html_entity_decode(htmlspecialchars_decode(get_partial('societe/pdf_entete', array('societe' => $this->societe)), HTML_ENTITIES));
  	}

  	public function getPublicFileName($extention = '.pdf')
  	{
    	return 'societeentete_'.$this->societe->identifiant.$extention;
  	}

}
