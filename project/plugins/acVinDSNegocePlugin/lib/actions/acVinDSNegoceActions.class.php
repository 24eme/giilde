<?php
class acVinDSNegoceActions extends sfActions
{
	public function executeMonEspace(sfWebRequest $request) {
		$this->etablissement = $this->getRoute()->getEtablissement();
		$this->date = DSNegoceClient::getDateDeclaration($request->getParameter('date', date('Y-m-d')));

		$this->dsnegoce = DSNegoceClient::getInstance()->find(DSNegoceClient::makeId($this->etablissement->identifiant, $this->date));
		$this->docRepriseProduits = DSNegoceClient::getDocumentRepriseProduits($this->etablissement->identifiant, $this->date);

		$this->formPeriodes = new DSNegoceEtablissementPeriodesForm($this->etablissement->identifiant, $this->date);

		if ($request->isMethod(sfWebRequest::POST)) {
			$param = $request->getParameter($this->formPeriodes->getName());
			if ($param) {
				$this->formPeriodes->bind($param);
				return $this->redirect('dsnegoce_etablissement', array('identifiant' => $this->etablissement->getIdentifiant(), 'date' => $this->formPeriodes->getValue('date')));
			}
		}
	}

	public function executeCreation(sfWebRequest $request) {
			$etablissement = $this->getRoute()->getEtablissement();
			$date = $request->getParameter('date');
			$dsnegoce = DSNegoceClient::getInstance()->createOrFind($etablissement->identifiant, $date, $this->isTeledeclarationDSNegoce());
			if($dsnegoce->isNew()) {
				$dsnegoce->save();
			}
			return $this->redirect('dsnegoce_infos', $dsnegoce);
	}

	public function executeInfos(sfWebRequest $request) {
			$this->dsnegoce = $this->getRoute()->getDsNegoce();
			$this->etablissement = $this->dsnegoce->getEtablissementObject();
			$this->isTeledeclarationMode = $this->isTeledeclarationDSNegoce();
	}

	public function executeStocks(sfWebRequest $request) {
			$this->dsnegoce = $this->getRoute()->getDsNegoce();
			$this->etablissement = $this->dsnegoce->getEtablissementObject();
			$this->isTeledeclarationMode = $this->isTeledeclarationDSNegoce();

			$this->form = new DSNegoceProduitsForm($this->dsnegoce);

			if (!$request->isMethod(sfWebRequest::POST)) {
					return sfView::SUCCESS;
			}

			$this->form->bind($request->getParameter($this->form->getName()));

			if (!$this->form->isValid()) {
				return sfView::SUCCESS;
			}
			$this->form->save();

			$this->redirect('dsnegoce_validation', $this->dsnegoce);

	}

	public function executeValidation(sfWebRequest $request) {
			$this->dsnegoce = $this->getRoute()->getDsNegoce();
			$this->etablissement = $this->dsnegoce->getEtablissementObject();
			$this->isTeledeclarationMode = $this->isTeledeclarationDSNegoce();
	}

	public function executeValidate(sfWebRequest $request) {
			$this->dsnegoce = $this->getRoute()->getDsNegoce();
			$this->dsnegoce->validate();
			$this->dsnegoce->save();
			$this->redirect('dsnegoce_visualisation', $this->dsnegoce);
	}

	public function executeDevalidate(sfWebRequest $request) {
			$this->dsnegoce = $this->getRoute()->getDsNegoce();
			$this->dsnegoce->devalidate();
			$this->dsnegoce->save();
			$this->redirect('dsnegoce_stocks', $this->dsnegoce);
	}

	public function executeVisualisation(sfWebRequest $request) {
			$this->dsnegoce = $this->getRoute()->getDsNegoce();
			$this->etablissement = $this->dsnegoce->getEtablissementObject();
			$this->isTeledeclarationMode = $this->isTeledeclarationDSNegoce();
	}

	protected function isTeledeclarationDSNegoce() {
		return $this->getUser()->hasTeledeclaration();
	}
}
