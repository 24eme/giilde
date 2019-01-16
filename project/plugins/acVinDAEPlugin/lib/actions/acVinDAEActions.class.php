<?php
class acVinDAEActions extends sfActions
{
	public function executeMonEspace(sfWebRequest $request) {
		$this->etablissement = $this->getRoute()->getEtablissement();
		$this->periode = new DateTime($request->getParameter('periode', date('Y-m-d')));
		$this->campagne = ConfigurationClient::getInstance()->buildCampagne($this->periode->format('Y-m-d'));
		$this->formCampagne($request, 'dae_etablissement');
		$this->daes = DAEClient::getInstance()->findByIdentifiantPeriode($this->etablissement->identifiant, $this->periode->format('Ym'))->getDatas();
	}
	
	private function formCampagne(sfWebRequest $request, $route) {
		$this->etablissement = $this->getRoute()->getEtablissement();
		$this->periode = new DateTime($request->getParameter('periode', date('Y-m-d')));
	
		$this->formCampagne = new DAEEtablissementCampagneForm($this->etablissement->identifiant, $this->periode->format('Y-m'));
	
		if ($request->isMethod(sfWebRequest::POST)) {
			$param = $request->getParameter($this->formCampagne->getName());
			if ($param) {
				$this->formCampagne->bind($param);
				return $this->redirect($route, array('identifiant' => $this->etablissement->getIdentifiant(), 'campagne' => $this->formCampagne->getValue('campagne')));
			}
		}
	}
	
	public function executeNouveau(sfWebRequest $request) {
		$this->etablissement = $this->getRoute()->getEtablissement();
		$this->periode = new DateTime($request->getParameter('periode', date('Y-m-d')));
	
		$this->withlast = $request->getParameter('withlast', null);
		$this->last = null;
	
		if ($this->withlast) {
			$this->last = DAEClient::getInstance()->findLastByIdentifiantDate($this->etablissement->getIdentifiant(), $this->periode->format('Ymd'));
		}
	
		$this->dae = ($id = $request->getParameter('id'))? DAEClient::getInstance()->find($id) : DAEClient::getInstance()->createSimpleDAE($this->etablissement->getIdentifiant(), $this->periode->format('Y-m-d'));
		if ($this->last && !$request->getParameter('id')) {
			$this->dae->initByDae($this->last);
		}
	
		$this->form = new DAENouveauForm($this->dae);
	
		if ($request->isMethod(sfWebRequest::POST)) {
			$this->form->bind($request->getParameter($this->form->getName()));
			if ($this->form->isValid()) {
				$this->dae = $this->form->save();
				return ($this->withlast)?  $this->redirect('dae_nouveau', array('sf_subject' => $this->etablissement, 'periode' => $this->dae->date, 'withlast' => 1)) : $this->redirect('dae_etablissement', array('identifiant' => $this->dae->identifiant, 'periode' => $this->dae->date));
			}
		}
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
						return $this->redirect('dae_etablissement', array('identifiant' => $this->identifiant));
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