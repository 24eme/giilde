<?php
class daeActions extends sfActions {

    public function executeMonEspace(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->periode = new DateTime($request->getParameter('periode', date('Y-m-d')));
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
        
        $this->dae = ($id = $request->getParameter('id'))? DAEClient::getInstance()->find($id) : DAEClient::getInstance()->createSimpleDAE($this->etablissement->getIdentifiant(), $this->periode->format('Y-m-d'));
        $this->form = new DAENouveauForm($this->dae);
        
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->dae = $this->form->save();
                return $this->redirect('dae_etablissement', $this->dae);
            }
        }
    }

    public function executeExport(sfWebRequest $request) {
    	$etablissement = $this->getRoute()->getEtablissement();
    	$daes = DAEClient::getInstance()->findByIdentifiant($etablissement->identifiant, acCouchdbClient::HYDRATE_JSON)->getDatas();
    	$csv = null;
    	foreach ($daes as $id => $dae) {
    		$dae->produit_key = $this->explodeProduct($dae->produit_key);
    		$datas = acCouchdbToolsJson::json2FlatenArray($dae, null, '_');
    		unset($datas['__id'],$datas['__rev'], $datas['_type']);
    		if (!$csv) {
    			$csv = $this->cleanCsvLine(implode(';', array_keys($datas)));
    			$csv .= "\n";
    		}
    		$csv .= str_replace('.', ',', implode(';', $datas));
    		$csv .= "\n";
    	}
    	$this->response->setContentType('text/csv');
    	$this->response->setHttpHeader('md5', md5($csv));
    	$this->response->setHttpHeader('Content-Disposition', "attachment; filename=dae.csv");
    	return $this->renderText($csv);
    }
    
    protected function cleanCsvLine($line) {
    	return str_replace('/', '_',  $line);
    }
    
    protected function explodeProduct($hash) {
    	if (!preg_match('/^\/declaration\/certifications\/([a-zA-Z0-9]+)\/genres\/([a-zA-Z0-9]+)\/appellations\/([a-zA-Z0-9]+)\/mentions\/([a-zA-Z0-9]+)\/lieux\/([a-zA-Z0-9]+)\/couleurs\/([a-zA-Z0-9]+)\/cepages\/([a-zA-Z0-9]+)$/', $hash, $m)) {
    		$m = array(null,null,null,null,null,null,null,null);
    	}
    	$product = new stdClass();
    	$product->certification = ($m[1] != 'DEFAUT')? $m[1] : null;
    	$product->genre = ($m[2] != 'DEFAUT')? $m[2] : null;
    	$product->appellation = ($m[3] != 'DEFAUT')? $m[3] : null;
    	$product->mention = ($m[4] != 'DEFAUT')? $m[4] : null;
    	$product->lieu = ($m[5] != 'DEFAUT')? $m[5] : null;
    	$product->couleur = ($m[6] != 'DEFAUT')? $m[6] : null;
    	$product->cepage = ($m[7] != 'DEFAUT')? $m[7] : null;
    	return $product;
    }

}
