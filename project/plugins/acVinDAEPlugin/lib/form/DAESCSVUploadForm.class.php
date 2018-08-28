<?php
class DAESCSVUploadForm extends BaseForm
{
    public function configure() {
        $this->setWidgets(array(
            'file' => new sfWidgetFormInputFile()
        ));
        $this->widgetSchema->setLabels(array(
            'file' => "Fichier"
        ));
        $this->setValidators(array(
            'file' => new ValidatorImportCsv(array('file_path' => sfConfig::get('sf_data_dir') . '/upload'))
        ));
        $this->widgetSchema->setNameFormat('daes_upload[%s]');
    }
}