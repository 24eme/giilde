<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DRMEmailManager
 *
 * @author mathurin
 */
class DRMEmailManager {

    protected $drm = null;
    protected $mailer = null;

    public function __construct($mailer) {
        $this->mailer = $mailer;
        sfProjectConfiguration::getActive()->loadHelpers("Date");
        sfProjectConfiguration::getActive()->loadHelpers("Orthographe");
        sfProjectConfiguration::getActive()->loadHelpers("DRM");
    }

    public function setDRM($drm) {
        $this->drm = $drm;
    }

    public function sendMailCoordonneesOperateurChanged($type, $diff) {
        $typeInfos = null;
        $typeLibelle = null;
        $mailsInterloire = sfConfig::get('app_mail_from_email');
        switch ($type) {
            case CompteClient::TYPE_COMPTE_ETABLISSEMENT:
                $typeInfos = $this->drm->getDeclarant();
                $typeLibelle = "l'etablissement";
                $identification = $typeInfos->nom . " (" . $this->drm->identifiant . ")";
                break;

            case CompteClient::TYPE_COMPTE_SOCIETE:
                $typeInfos = $this->drm->getSociete();
                $typeLibelle = 'la société';
                $identification = $typeInfos->raison_sociale . " (" . substr($this->drm->identifiant, 0, 6) . ")";
                break;
        }

        $mess = "Les coordonnées de " . $typeLibelle . " " . $identification . " ont été modifiées.
Voici les différentes modifications enregistrées :

";
        foreach ($diff as $key => $value) {
            $mess .= $key . " : " . $value . "
";
        }
        $mess .= "

——

L’application de télédéclaration de votre interprofession.";


        $subject = "Changement de coordonnées de " . $typeLibelle . " : " . $identification;

        $message = $this->getMailer()->compose(array(sfConfig::get('app_mail_from_email') => sfConfig::get('app_mail_from_name')), $mailsInterloire, $subject, $mess);
        try {
            $this->getMailer()->send($message);
        } catch (Exception $e) {
            $this->getUser()->setFlash('error', 'Erreur de configuration : Mail de confirmation non envoyé, veuillez contacter votre interprofession.');
            return null;
        }
        return true;
    }


    public function sendMailDrmCielDiffs() {
        $typeInfos = null;
        $typeLibelle = null;
        $mailsInterloire = sfConfig::get('app_teledeclaration_emails_interloire');

        $subject = "[ Comparaison Ciel Vinsi ] - Drm différente pour ".$this->drm->declarant->nom." (".$this->drm->identifiant.") période : ".$this->drm->getPeriode();

        $mess = "La DRM de ".$this->drm->declarant->nom." (".$this->drm->identifiant.") période:".$this->drm->getPeriode()." a été transmise sur CIEL et possède des différences avec celle d'Interloire. ";

        $diffArrStr = $this->drm->getXMLComparison()->getFormattedXMLComparaison();
        foreach ($diffArrStr as $key => $values) {
            $mess .= $key . " [" . ((is_null($values[0])) ? "valeur nulle" : $values[0]) . "]
";
        }
        $mess .= "
        Une DRM modificatrice a été ouverte : ".sfConfig::get('app_routing_context_production_host').sfContext::getInstance()->getRouting()->generate("drm_etablissement",array("identifiant" => $this->drm->identifiant))."

——

L’application de télédéclaration des contrats d’InterLoire";

        $message = $this->getMailer()->compose(array(sfConfig::get('app_mail_from_email') => sfConfig::get('app_mail_from_name')), $mailsInterloire, $subject, $mess);
        try {
          //  $this->getMailer()->send($message);
        } catch (Exception $e) {
            $this->getUser()->setFlash('error', 'Erreur de configuration : Mail de confirmation non envoyé, veuillez contacter INTERLOIRE');
            return null;
        }
        return true;
    }

    public function sendMailValidation($send = true) {
        $messages = $this->composeMailValidation();
        $emails = array();
        foreach($messages as $message) {
            try {
                if($send) {
                    $this->getMailer()->send($message);
                }
                $emails = array_merge($emails, array_keys($message->getTo()));
            } catch(Exception $e) {

            }
        }

        return $emails;
    }
    public function composeMailValidation($transmission_douane = null) {
        $messages = array();

        $etablissement = EtablissementClient::getInstance()->find($this->drm->identifiant);
        $contact = EtablissementClient::getInstance()->buildInfosContact($etablissement);
        if (!$contact || !isset($contact->email) || !$contact->email) {
            $email = sfConfig::get('app_mail_from_email');
        } else {
            $email = $contact->email;
        }

        if($transmission_douane === null) {
            $transmission_douane = $etablissement->getSociete()->getMasterCompte()->hasDroit(Roles::TELEDECLARATION_DOUANE);
        }

        $interpro = strtoupper(sfConfig::get('app_teledeclaration_interpro'));

        $mess = "Bonjour,

Votre DRM " . getFrPeriodeElision($this->drm->periode). " a été validée électroniquement sur le portail de télédeclaration ". sfConfig::get('app_teledeclaration_url')." .";

if($transmission_douane) {
    $mess .= "

N'oubliez pas de valider votre DRM sur l'application CIEL de Prodouane.";
}

$mess .= "

La version PDF de cette DRM est également disponible en pièce jointe dans ce mail.
";

if(!$transmission_douane && DRMConfiguration::getInstance()->getConfig("texte_mail_pas_transmission_douane")) {
    $mess .= "
".DRMConfiguration::getInstance()->getConfig("texte_mail_pas_transmission_douane")."
    ";
} elseif(!$transmission_douane) {
    $mess .= "
Dans l'attente de votre acceptation du contrat de service douane, la DRM doit être signée manuellement avant transmission par mail ou courrier postal à votre service local douanier.
    ";
}

$mess .= "
Pour toutes questions, veuillez contacter votre interprofession (".$interpro.") : " . $email . " .

--

L’application de télédéclaration des DRM ". sfConfig::get('app_teledeclaration_url') ."";

        $pdf = new DRMLatex($this->drm);
        $pdfContent = $pdf->getPDFFileContents();
        $pdfName = $pdf->getPublicFileName();

        $subject = "Validation de la DRM " . getFrPeriodeElision($this->drm->periode) . " créée le " . $this->getDateSaisieDrmFormatted();

        $message = $this->getMailer()->compose(array(sfConfig::get('app_mail_from_email') => sfConfig::get('app_mail_from_name')), $etablissement->getEmailTeledeclaration(), $subject, $mess);

        $message->attach(new Swift_Attachment($pdfContent, $pdfName, 'application/pdf'));

        $messages[] = $message;

        if ($this->drm->email_transmission) {
            $message_transmission = $this->getMailer()->compose(array(sfConfig::get('app_mail_from_email') => sfConfig::get('app_mail_from_name')), $this->drm->email_transmission, $subject, $mess);
            $message_transmission->attach(new Swift_Attachment($pdfContent, $pdfName, 'application/pdf'));
            $messages[] = $message_transmission;
        }

        return $messages;
    }

    private function getMailer() {
        return $this->mailer;
    }

    private function getUrlVisualisationDrm() {
        return sfContext::getInstance()->getRouting()->generate('drm_visualisation', $this->drm, true);
    }

    protected function getDateSaisieDrmFormatted() {
        return date("d/m/Y", strtotime($this->drm->valide->date_saisie));
    }

}
