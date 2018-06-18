<?php

/**
* Description of actions
*
*/
class drm_xmlActions extends drmGeneriqueActions {

  public function executeWait(sfWebRequest $request) {
    $this->setLayout(false);
    $this->drm = $this->getRoute()->getDRM();
  }

  public function executeTransfert(sfWebRequest $request) {
    $this->drm = $this->getRoute()->getDRM();
    $this->etablissement = $this->getRoute()->getEtablissement();
    if ($request->isMethod(sfWebRequest::POST)) {
      if (!CielService::hasAppConfig() ||
          ($this->drm->add('transmission_douane')->exist('success') && $this->drm->add('transmission_douane')->get('success'))) {
        return $this->redirect('drm_ciel', $this->drm);
      }

      $this->drm->transferToCiel();

      if (!$this->drm->transmission_douane->success) {
          $to = sfConfig::get('app_ac_exception_notifier_email');
          $to = ($to && isset($to->to)) ? $to->to : 'vins@actualys.com';
          $msg = $this->getMailer()->compose(array(sfConfig::get('app_mail_from_email') => sfConfig::get('app_mail_from_name')),
          $to,
          "Erreur transmision XML pour ".$this->drm->_id,
          "Une transmission vient d'échouer pour la DRM ".$this->drm->_id." : \n".$this->drm->transmission_douane->xml);
          $this->getMailer()->send($msg);
      }

      return $this->redirect('drm_ciel', $this->drm);
    }
    $this->cielResponse = $this->drm->transmission_douane->xml;
  }

  public function executePrint(sfWebRequest $request) {
      $this->drm = $this->getRoute()->getDrm();
      $this->setLayout(false);
      $this->getResponse()->setHttpHeader('Content-Type', 'text/xml');
  }

  public function executeRetour(sfWebRequest $request) {
    $this->drm = $this->getRoute()->getDRM();
    $this->setLayout(false);
    $this->getResponse()->setHttpHeader('Content-Type', 'text/xml');
  }

  public function executeRetourRefresh(sfWebRequest $request) {
    $this->drm = $this->getRoute()->getDRM();
    $this->setLayout(false);
    $pathScript = realpath('../bin/updateOneDouaneDrmComparaison.sh');
    if(!$pathScript){
        throw new sfException("Le script de mis à jour n'existe pas");
    }
    $dateRequete = ConfigurationClient::getInstance()->buildDate(ConfigurationClient::getInstance()->getPeriodePrecedente($this->drm->periode));
    $periode = $this->drm->periode;
    $etb = $this->drm->getEtablissement();
    $numeroAccise = $etb->getNoAccises();
    $cvi = $etb->getCvi();
    $cmd = 'bash '.$pathScript." ".$dateRequete." ".$periode." ".$numeroAccise." ".$cvi;
    $retour = shell_exec($cmd);
    return $this->redirect('drm_visualisation', $this->drm);
  }

  public function executeMain()
  {
  }

}
