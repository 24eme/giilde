<?php

class drm_annexesActions extends drmGeneriqueActions {

    public function executeAnnexes(sfWebRequest $request) {
        $this->initSocieteAndEtablissementPrincipal();
        $this->drm = $this->getRoute()->getDRM();
        $this->isTeledeclarationMode = $this->isTeledeclarationDrm();
        if (!$this->isTeledeclarationMode) {
          $this->redirect('drm_etablissement', $this->drm);
        }

        $this->drm->update();

        $this->initDeleteForm();

        $this->annexesForm = new DRMAnnexesForm($this->drm);
        if ($request->isMethod(sfRequest::POST)) {
            $this->annexesForm->bind($request->getParameter($this->annexesForm->getName()));
            if ($this->annexesForm->isValid()) {
                $this->annexesForm->save();
                $this->redirect('drm_validation', $this->annexesForm->getObject());
            }
        }
    }

}
