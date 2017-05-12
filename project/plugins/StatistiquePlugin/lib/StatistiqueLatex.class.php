<?php
class StatistiqueLatex extends GenericLatex
{
	protected $csv;
	protected $type;
	protected $options;
	
	protected $filename;

  	function __construct($csv, $type, $options = array())
  	{
    	sfProjectConfiguration::getActive()->loadHelpers("Partial", "Url", "MyHelper");
    	$this->csv = $csv;
    	$this->type = $type;
    	$this->options = $options;
    	$this->filename = 'statistiques_'.$this->type.'_'.date('YmdHis');
  	}

  	public function getNbPages()
  	{
    	return 1;
  	}

  	public function getLatexFileNameWithoutExtention()
  	{
    	return $this->getTEXWorkingDir().$this->filename;
  	}

  	public function getLatexFileContents()
  	{
    	return html_entity_decode(htmlspecialchars_decode(get_partial('statistique/pdf_'.$this->type, array('csv' => $this->csv, 'options' => $this->options)), HTML_ENTITIES));
  	}

  	public function getPublicFileName($extention = '.pdf')
  	{
    	return 'statistiques_'.$this->filename.$extention;
  	}

}
