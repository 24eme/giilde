<?php
class VracSansPrixLatex extends GenericLatex
{
	protected $csv;
	protected $type;
	protected $options;

  	function __construct($csv, $options = array())
  	{
    	sfProjectConfiguration::getActive()->loadHelpers("Partial", "Url", "MyHelper");
    	$this->csv = $csv;
    	$this->options = $options;
  	}

  	public function getNbPages()
  	{
    	return 1;
  	}

  	public function getLatexFileNameWithoutExtention()
  	{
    	return $this->getTEXWorkingDir().'vracsansprix_'.$this->csv[0][0].'_'.$options['date_debut'].$options['date_fin'];
  	}

  	public function getLatexFileContents()
  	{
    	return html_entity_decode(htmlspecialchars_decode(get_partial('statistique/pdf_vracsansprix', array('csv' => $this->csv, 'options' => $this->options)), HTML_ENTITIES));
  	}

  	public function getPublicFileName($extention = '.pdf')
  	{
    	return 'vracsansprix_'.$this->csv[0][0].'_'.$options['date_debut'].$options['date_fin'].$extention;
  	}

}
