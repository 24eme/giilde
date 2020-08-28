<?php

/**
 * Description of SubventionDossierForm
 *
 * @author mathurin
 */
class SubventionDossierForm extends acCouchdbForm {

    public function configure() {

      $this->setWidgets(array(
          'file' => new sfWidgetFormInputFile()
      ));
      $this->widgetSchema->setLabels(array(
          'file' => "Dossier"
      ));
      $this->setValidators(array(
          'file' => new ValidatorImportXls(array('required' => true, 'file_path' => sfConfig::get('sf_data_dir') . '/subventions'))
      ));

      if($this->getDocument()->hasXls()) {
          $this->getValidator('file')->setOption('required', false);
      }

      $this->widgetSchema->setNameFormat('subvention_dossier[%s]');

    }

    public function save() {
      $file = $this->getValue('file');
      if (!$file && $this->getDocument()->isNew()) {
        throw new sfException("Une erreur lors de l'upload est survenue");
      }
      if ($file && !$file->isSaved()) {
        $file->save();
      }

      $isNew = false;
      if ($this->getDocument()->isNew()) {
        $this->getDocument()->save();
        $isNew = true;
      }
      if ($file) {
        try {
          $this->getDocument()->storeDossier($file->getSavedName());
          $date = new \DateTime( 'now');
          $this->getDocument()->dossier_date = $date->format('Y-m-d H:i:s');
        } catch (sfException $e) {
          if ($isNew) {
            $this->getDocument()->remove();
          }
          throw new sfException($e);
        }
        unlink($file->getSavedName());
      }

      $this->getDocument()->save();
      return $this->subvention;
    }



}
