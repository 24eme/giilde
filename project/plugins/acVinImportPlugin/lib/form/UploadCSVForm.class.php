<?php

class UploadCSVForm extends BaseForm {

    /**
     * Configuration du formulaire
     *
     */
    public function configure() {
        $this->setWidget('file', new sfWidgetFormInputFile([], ['required' => true]));
        $this->setValidator('file', new ValidatorImportCsv([
            'file_path' => sfConfig::get('sf_data_dir').'/upload'
        ]));

        $this->setWidget('checkbox', new sfWidgetFormInputCheckbox([
            'label' => 'Le fichier possède une ligne d\'en-tête ?',
            'default' => true
        ]));
        $this->setValidator('checkbox', new sfValidatorBoolean());

        $this->widgetSchema->setLabel('file', "Choisir un fichier :");
        $this->widgetSchema->setNameFormat('csv[%s]');
    }
}
