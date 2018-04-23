<?php

/**
 * Description of class drmGeneriqueActions
 * @author mathurin
 */
class drmGeneriqueActions extends sfActions {

    protected function initSocieteAndEtablissementPrincipal() {
        $this->compte = $this->getUser()->getCompte();
        if ($this->isTeledeclarationDrm()) {
            $this->etablissementPrincipal = $this->getRoute()->getEtablissement();
            $this->societe = $this->etablissementPrincipal->getSociete();
        }

        $this->etablissementPrincipal = $this->getRoute()->getEtablissement();
    }

    protected function redirect403IfIsNotTeledeclaration() {
        if (!$this->isTeledeclarationDrm()) {
            $this->redirect403();
        }
    }

     protected function redirect403IfIsTeledeclaration() {
        if ($this->isTeledeclarationDrm()) {
            $this->redirect403();
        }
    }

    protected function redirect403IfIsNotTeledeclarationAndNotMe() {
        $this->redirect403IfIsNotTeledeclaration();
        if ($this->getUser()->getCompte()->identifiant != $this->identifiant) {
            $this->redirect403();
        }
    }

    protected function redirect403Unless($bool) {
        if (!$bool) {
          $this->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));
        }
    }


    private function redirect403() {
        $this->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));
    }

    protected function isTeledeclarationDrm() {
    	return $this->getUser()->hasTeledeclarationDrm();
    }

    protected function initDeleteForm() {
        $this->deleteForm = new DRMDeleteForm($this->drm);
    }

    protected function isUsurpationMode() {
        return $this->getUser()->isUsurpationCompte();
    }

    protected function processProduitDetails($request, $formClass) {
        $this->detail = $this->getRoute()->getDRMDetail();
        $this->drm = $this->detail->getDocument();
        $this->isTeledeclarationMode = $this->isTeledeclarationDrm();
        $this->catKey = $request->getParameter('cat_key');
        $this->key = $request->getParameter('key');
        $this->form = new $formClass($this->detail->get($this->catKey)->get($this->key."_details"), array(),array('isTeledeclarationMode' => $this->isTeledeclarationMode));

        if ($request->isMethod(sfRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if($this->form->isValid()) {
                  $this->form->update();
                  $this->drm->update();
                  $this->drm->save();
                if($request->isXmlHttpRequest())
                {
                    $this->getUser()->setFlash("notice", 'Le détail a été mis à jour avec success.');
                    return $this->renderText(json_encode(array("success" => true, "type" => $this->catKey."_".$this->key, "volume" => $this->detail->get($this->catKey)->get($this->key), "document" => array("id" => $this->drm->get('_id'),"revision" => $this->drm->get('_rev')))));
                }

                return $this->redirect('drm_edition_detail', $this->detail);
            }
            if($request->isXmlHttpRequest())
            {
                return $this->renderText(json_encode(array('success' => false ,'content' => $this->getPartial('formContent', array('form' =>   $this->form, 'detail' => $this->detail,'isTeledeclarationMode' => $this->isTeledeclarationMode, 'catKey' => $this->catKey, 'key' => $this->key)))));
            }
        }
    }

}
