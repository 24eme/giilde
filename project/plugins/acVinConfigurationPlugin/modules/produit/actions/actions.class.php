<?php

/**
 * produit actions.
 *
 * @package    declarvin
 * @subpackage produit
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class produitActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
      if(!$request->getParameter('date')) {

        return $this->redirect('produits', array('date' => date('Y-m-d')));
      }
      $this->date = $request->getParameter('date');
      $this->config = ConfigurationClient::getConfiguration($this->date);
      $this->produits = $this->config->declaration->getProduits($this->date);
  }

  public function executeModification(sfWebRequest $request)
  {
    //throw new sfException("Edition de l'arbre produit désactivé pour le moment");
  	$this->forward404Unless($request_noeud = $request->getParameter('noeud', null));
  	$this->forward404Unless($hash = str_replace('-', '/', $request->getParameter('hash', null)));

  	$this->interpro = 'INTERPRO-declaration';
  	$this->produit = ConfigurationClient::getCurrent()->getOrAdd(str_replace('-', '/', $hash));
    $this->noeud = $this->produit->get($request_noeud);

  	$this->form = new ProduitDefinitionForm($this->noeud);

  	if ($request->isMethod(sfWebRequest::POST)) {
      $this->form->bind($request->getParameter($this->form->getName()));
  		if ($this->form->isValid()) {
        $this->form->save();
		$this->getUser()->setFlash("notice", 'Le produit a été modifié avec success.');

        return $this->redirect('produits');
      }
    }
  }

  public function executeNouveau(sfWebRequest $request)
  {
    //throw new sfException("Edition de l'arbre produit désactivé pour le moment");
  	$this->interpro = InterproClient::getInstance()->find('INTERPRO-declaration');
  	$configuration = ConfigurationClient::getCurrent();
  	$this->form = new ProduitNouveauForm($configuration, $this->interpro->_id);
  	if (!$request->isMethod(sfWebRequest::POST)) {

      return sfView::SUCCESS;
    }

    $this->form->bind($request->getParameter($this->form->getName()));
		if ($this->form->isValid()) {
			$noeud_to_edit = $this->form->save();
      $produit = $this->form->getProduit();

			$this->getUser()->setFlash("notice", 'Le produit a été ajouté avec success.');

      return $this->redirectModification($produit->getHash(), $noeud_to_edit);
		}
  }

  protected function redirectModification($hash, $noeud_to_edit = array()) {
    if(!count($noeud_to_edit)) {

      return $this->redirect('produits');
    }

    $noeud = $noeud_to_edit[0];
    unset($noeud_to_edit[0]);

    $hash = str_replace('/', '-', $hash);

    return $this->redirect('produit_modification', array('noeud' => $noeud, 'hash' => $hash, 'noeud_to_edit' => implode("|", $noeud_to_edit)));
  }
}
