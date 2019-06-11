<?php

class drm_annexesComponents extends sfComponents {

  public function executeAnnexes() {
      $this->annexesForm = new  DRMAnnexesForm($this->drm);
      if ($this->requestAnnexes->isMethod(sfRequest::POST)) {
            $this->annexesForm->bind($this->requestAnnexes->getParameter($this->annexesForm->getName()));
            if ($this->annexesForm->isValid()) {
                $this->annexesForm->save();
                $this->generateUrl('drm_validation', $this->annexesForm->getObject());
            }
        }
  }

}
