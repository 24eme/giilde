<?php

class GenerationFactureMail extends GenerationAbstract {

    public function generateMailForADocumentId($id) {
        $facture = FactureClient::getInstance()->find($id);

        if(!$facture->getSociete()->getEmailTeledeclaration()) {
            echo $facture->getSociete()->_id."\n";
            return;
        }

        $message = Swift_Message::newInstance()
         ->setFrom(sfConfig::get('app_mail_from_email'))
         ->setTo($facture->getSociete()->getEmailTeledeclaration())
         ->setSubject("Facture n°".$facture->getNumeroInterpro()." - BIVC")
         ->setBody("Bonjour,

Une nouvelle facture du BIVC est disponible, vous pouvez la télécharger directement en cliquant sur le lien : <".ProjectConfiguration::getAppRouting()->generate('facture_pdf_auth', array('id' => $facture->_id, 'auth' => FactureClient::generateAuthKey($id)), true).">

Bien cordialement,

Le BIVC");

        return $message;
    }

    public static function getActionLibelle() {

        return "Envoyer les factures par mail";
    }

    public function getMailer() {

        return sfContext::getInstance()->getMailer();
    }

    public function getLogPath() {

        return sfConfig::get('sf_web_dir')."/generation/".$this->getLogFilname();
    }

    public function getPublishFile() {

        return urlencode("/generation/".$this->getLogFilname());
    }

    public function getLogFilname() {

        return $this->generation->date_emission."-facture-envoi-mails.csv";
    }

    public function getLogs() {

        return $this->logs;
    }

    public function addLog($factureId, $statut, $date = null) {
        $header = false;
        if(!file_exists($this->getLogPath())) {
            $header = true;
        }

        $fp = fopen($this->getLogPath(), 'a');

        if($header) {
            fputcsv($fp, array("Date", "Numéro de facture", "Identifiant Opérateur", "Raison sociale", "Email", "Statut", "Facture ID"));
        }

        fputcsv($fp, $this->getLog($factureId, $statut, $date));

        fclose($fp);
    }

    public function getLog($factureId, $statut, $date = null) {
        if(!$date) {
            $date = date("Y-m-d H:i:s");
        }

        $facture = FactureClient::getInstance()->find($factureId);

        return array($date, $facture->getNumeroPieceComptable(), $facture->identifiant, $facture->declarant->raison_sociale, $facture->getSociete()->getEmailTeledeclaration(), $statut, $facture->_id);
    }

    public function generate() {
        $this->generation->setStatut(GenerationClient::GENERATION_STATUT_ENCOURS);
        $this->generation->save();

        $factureAEnvoyer = array();
        $factureDejaEnvoye = $this->generation->documents->toArray();
        $sleepMaxBatch = 5;
        $sleepSecond = 2;
        $i = 0;
        foreach($this->generation->getMasterGeneration()->documents as $factureId) {
            if(in_array($factureId, $factureDejaEnvoye)) {
                continue;
            }
            $mail = $this->generateMailForADocumentId($factureId);

            if(!$mail) {
                $this->addLog($factureId, "PAS_DE_MAIL");
                continue;
            }

            $sended = $this->getMailer()->send($mail);

            if(!$sended) {
                $this->addLog($factureId, "ERREUR");
                continue;
            }

            $this->addLog($factureId, "ENVOYÉ");

            $this->generation->documents->add(null, $factureId);
            $this->generation->save();
            $i++;
            if($i > $sleepMaxBatch) {
                sleep($sleepSecond);
                $i = 0;
            }
        }

        if(!$this->generation->exist('fichiers/'.$this->getPublishFile())) {
            $this->generation->add('fichiers')->add($this->getPublishFile(), "Logs d'envoi de mails");
            $this->generation->save();
        }

        $this->generation->setStatut(GenerationClient::GENERATION_STATUT_GENERE);
        $this->generation->save();
    }
}
