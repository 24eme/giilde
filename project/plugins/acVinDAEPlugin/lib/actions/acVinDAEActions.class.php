<?php
class acVinDAEActions extends sfActions
{
	public function executeMonEspace(sfWebRequest $request) {
		$this->etablissement = $this->getRoute()->getEtablissement();
		$this->periode = new DateTime($request->getParameter('periode', date('Y-m-d')));
		$this->campagne = ConfigurationClient::getInstance()->buildCampagne($this->periode->format('Y-m-d'));
		
		$this->formCampagne = new DAEEtablissementCampagneForm($this->etablissement->identifiant, $this->periode->format('Y-m'));
		
		if ($request->isMethod(sfWebRequest::POST)) {
			$param = $request->getParameter($this->formCampagne->getName());
			if ($param) {
				$this->formCampagne->bind($param);
				return $this->redirect('dae_etablissement', array('identifiant' => $this->etablissement->getIdentifiant(), 'campagne' => $this->formCampagne->getValue('campagne')));
			}
		}
		$this->daes = DAEClient::getInstance()->findByIdentifiantPeriode($this->etablissement->identifiant, $this->periode->format('Ym'), acCouchdbClient::HYDRATE_JSON)->getDatas();
	}
	
	public function executeExportEdi(sfWebRequest $request) {
		$this->etablissement = $this->getRoute()->getEtablissement();
		$this->campagne = $request->getParameter('campagne');
	
		$export = new DAEExportCsv();
	
		$csv = $export->exportByEtablissementAndCampagne($this->etablissement->identifiant, $this->campagne);
	
		$this->response->setContentType('text/csv');
		$this->response->setHttpHeader('md5', md5($csv));
		$this->response->setHttpHeader('Content-Disposition', "attachment; filename=DAE-".$this->etablissement->identifiant."-".$this->campagne.".csv");
		return $this->renderText($csv);
	}
	
	public function executeUploadEdi(sfWebRequest $request) {
  		set_time_limit(300);
		$this->etablissement = $this->getRoute()->getEtablissement();
		$this->identifiant = $request->getParameter('identifiant');
		$this->periode = new DateTime($request->getParameter('periode', date('Y-m-d')));
		$this->erreurs = array();
		$this->form = new DAESCSVUploadForm();
		if ($request->isMethod(sfWebRequest::POST)) {
			$this->form->bind($request->getParameter($this->form->getName()), $request->getFiles($this->form->getName()));
			if ($this->form->isValid()) {
				
				$path = sfConfig::get('sf_data_dir') . '/upload/'.'import_daes_'.$this->etablissement . '_' . $this->periode->format('Y-m-d').'_'.$this->form->getValue('file')->getMd5().$this->form->getValue('file')->getOriginalExtension();
				
				$file = sfConfig::get('sf_data_dir') . '/upload/' . $this->form->getValue('file')->getMd5();
				
				rename($file,  $path);
				
				$this->daeCsvEdi = new DAEImportCsvEdi($path, $this->identifiant, $this->periode->format('Y-m-d'));
				$this->daeCsvEdi->checkCSV();
				
				if($this->daeCsvEdi->getCsvDoc()->hasErreurs()) {
					$this->erreurs = $this->daeCsvEdi->getCsvDoc()->erreurs;
				} else {
					
					$this->daeCsvEdi->importCsv();
					
					if($this->daeCsvEdi->getCsvDoc()->hasErreurs()) {
						$this->erreurs = $this->daeCsvEdi->getCsvDoc()->erreurs;
					} else {
						$this->getUser()->setFlash('notice', 'Vos ventes ont bien été importées');
						$periodes = $this->daeCsvEdi->periodes;
						krsort($periodes);
						$periode = (count($periodes) > 0)? current($periodes) : null;
						return $this->redirect('dae_etablissement', array('identifiant' => $this->identifiant, 'periode' => $periode));
					}
				}
			} else {
				$files = $request->getFiles($this->form->getName());
				if ($files['file']['name'] && $files['file']['tmp_name']) {
					$path = '/tmp/'.$files['file']['name'];
					$resultat = move_uploaded_file($files['file']['tmp_name'], $path);
				}
			}
			
			if (!$this->getUser()->hasCredential(myUser::CREDENTIAL_ADMIN)) {
				return $this->fileErrorUploadEdi($path, $this->etablissement, $this->periode);
			}
		}
	}
	
	public function fileErrorUploadEdi($file, $etablissement, $periode) {
		return;
	}
	
	public function executeCreationEdi(sfWebRequest $request) {
		set_time_limit(0);
		ini_set('memory_limit', '512M');
		$this->md5 = $request->getParameter('md5', null);
		$this->identifiant = $request->getParameter('identifiant');
		$this->periode = new DateTime($request->getParameter('periode', date('Y-m-d')));
		$path =  null;
		if($this->md5){
			$fileName = 'import_daes_'.$this->identifiant . '_' . $this->periode->format('Y-m-d').'_'.$this->md5.'.csv';
			$path = sfConfig::get('sf_data_dir') . '/upload/' . $fileName;
		}
		$this->daeCsvEdi = new DAEImportCsvEdi($path, $this->identifiant, $this->periode->format('Y-m-d'));
		$this->daeCsvEdi->importCSV();
		$this->redirect('dae_etablissement', array('identifiant' => $this->identifiant, "periode" => $this->periode->format('Y-m-d')));
	}
}