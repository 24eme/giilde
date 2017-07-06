<?php

/* This file is part of the acVinComptePlugin package.
 * Copyright (c) 2011 Actualys
 * Authors :
 * Tangui Morlier <tangui@tangui.eu.org>
 * Charlotte De Vichet <c.devichet@gmail.com>
 * Vincent Laurent <vince.laurent@gmail.com>
 * Jean-Baptiste Le Metayer <lemetayer.jb@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * acVinCompte plugin.
 *
 * @package    acVinComptePlugin
 * @subpackage lib
 * @author     Tangui Morlier <tangui@tangui.eu.org>
 * @author     Charlotte De Vichet <c.devichet@gmail.com>
 * @author     Vincent Laurent <vince.laurent@gmail.com>
 * @author     Jean-Baptiste Le Metayer <lemetayer.jb@gmail.com>
 * @version    0.1
 */
class compte_teledeclarantActions extends sfActions {

    const SESSION_COMPTE_DOC_ID_CREATION = '';
    const SESSION_COMPTE_DOC_ID_OUBLIE = '';


    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */
    public function executeFirst(sfWebRequest $request) {
        $this->form = new CompteLoginFirstForm();
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->getUser()->setAttribute(self::SESSION_COMPTE_DOC_ID_CREATION, $this->form->getValue('compte')->_id);

                return $this->redirect('compte_teledeclarant_cgu');
            }
        }
    }

    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */
    public function executeCgu(sfWebRequest $request) {
        if(!is_file(sfConfig::get('sf_app_dir').'/modules/compte_teledeclarant/templates/cguSuccess.php')) {

            return $this->redirect("compte_teledeclarant_creation");
        }

        if($request->isMethod(sfWebRequest::POST)) {

            return $this->redirect("compte_teledeclarant_creation");
        }
    }

    /**
     *
     * @param sfWebRequest $request
     */
    public function executeCreation(sfWebRequest $request) {
        $this->forward404Unless($this->getUser()->getAttribute(self::SESSION_COMPTE_DOC_ID_CREATION, null));
        $this->compte = CompteClient::getInstance()->find($this->getUser()->getAttribute(self::SESSION_COMPTE_DOC_ID_CREATION, null));
        $this->forward404Unless($this->compte);
        $this->forward404Unless($this->compte->getStatutTeledeclarant() == CompteClient::STATUT_TELEDECLARANT_NOUVEAU);

        $this->form = new CompteTeledeclarantCreationForm($this->compte);

        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->form->save();
                $emailCible = $this->form->getValue('email');

                $this->getUser()->getAttributeHolder()->remove(self::SESSION_COMPTE_DOC_ID_CREATION);
                $this->getUser()->signInOrigin($this->compte);
                try {
                    $message = $this->getMailer()->composeAndSend(array(sfConfig::get('app_mail_from_email') => sfConfig::get('app_mail_from_name')), $emailCible, "Confirmation de création de votre compte", $this->getPartial('compte_teledeclarant/creationEmail', array('compte' => $this->compte)));
                } catch (Exception $e) {
                    $this->getUser()->setFlash('error', "Problème de configuration : l'email n'a pu être envoyé");
                }

                return $this->redirect('common_homepage');
            }
        }
    }

    /**
     *
     * @param sfWebRequest $request
     */
    public function executeModification(sfWebRequest $request) {
        $this->compte = $this->getUser()->getCompte();

        $this->form = new CompteTeledeclarantForm($this->compte);

        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->form->save();

                $this->getUser()->setFlash('maj', 'Vos identifiants ont bien été mis à jour.');
                $this->redirect('compte_teledeclarant_modification');
            }
        }
    }

    public function executeMotDePasseOublie(sfWebRequest $request) {
        $this->form = new CompteMotDePasseOublieForm();
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $compte = $this->form->save();

                $societe = $compte->getSociete();
                $lien = $this->generateUrl("compte_teledeclarant_mot_de_passe_oublie_login", array("login" => $societe->identifiant, "mdp" => str_replace("{OUBLIE}", "", $compte->mot_de_passe)), true);
                $emailCible = null;

                if (!$societe->isTransaction()) {
                    $emailCible = $societe->getEmailTeledeclaration();
                }else{
                     $emailCible = $societe->getEtablissementPrincipal()->getEmailTeledeclaration();
                }

                try {
                    $message = $this->getMailer()->composeAndSend(array(sfConfig::get('app_mail_from_email') => sfConfig::get('app_mail_from_name')), $emailCible, "Demande de mot de passe oublié", $this->getPartial('motDePasseOublieEmail', array('compte' => $compte, 'lien' => $lien)));
                } catch (Exception $e) {
                    $this->getUser()->setFlash('error', "Problème de configuration : l'email n'a pu être envoyé");
                }
                $this->redirect('compte_teledeclarant_mot_de_passe_oublie_confirm');
            }
        }
    }

    public function executeMotDePasseOublieLogin(sfWebRequest $request) {
        $this->forward404Unless($compte = CompteClient::getInstance()->findByLogin($request->getParameter('login', null)));
        $this->forward404Unless($compte->mot_de_passe == '{OUBLIE}' . $request->getParameter('mdp', null));
        $this->getUser()->setAttribute(self::SESSION_COMPTE_DOC_ID_OUBLIE, $compte->_id);

        $this->redirect('compte_teledeclarant_modification_oublie');
    }

    public function executeMotDePasseOublieConfirm(sfWebRequest $request) {

    }

    /**
     *
     * @param sfWebRequest $request
     */
    public function executeModificationOublie(sfWebRequest $request) {
        $this->forward404Unless($this->getUser()->getAttribute(self::SESSION_COMPTE_DOC_ID_OUBLIE, null));
        $this->compte = CompteClient::getInstance()->find($this->getUser()->getAttribute(self::SESSION_COMPTE_DOC_ID_OUBLIE, null));
        $this->forward404Unless($this->compte);
        $this->forward404Unless($this->compte->getStatutTeledeclarant() == CompteClient::STATUT_TELEDECLARANT_OUBLIE);

        $this->form = new CompteTeledeclarantOublieForm($this->compte);

        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->form->save();
                $this->getUser()->getAttributeHolder()->remove(self::SESSION_COMPTE_DOC_ID_OUBLIE);
                $this->getUser()->signInOrigin($this->compte);

                return $this->redirect('common_homepage');
            }
        }
    }

    public function executeReglementationGenerale() {
        return $this->renderPdf(sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . "data/reglementation_generale_des_transactions.pdf", "reglementation_generale_des_transactions.pdf");
    }

    /*
     * Fonctions pour le téléchargement de la reglementation_generale_des_transactions
     *
     */

    protected function renderPdf($path, $filename) {
        $this->getResponse()->setHttpHeader('Content-Type', 'application/pdf');
        $this->getResponse()->setHttpHeader('Content-disposition', 'attachment; filename="' . $filename . '"');
        $this->getResponse()->setHttpHeader('Content-Transfer-Encoding', 'binary');
        $this->getResponse()->setHttpHeader('Content-Length', filesize($path));
        $this->getResponse()->setHttpHeader('Pragma', '');
        $this->getResponse()->setHttpHeader('Cache-Control', 'public');
        $this->getResponse()->setHttpHeader('Expires', '0');
        return $this->renderText(file_get_contents($path));
    }

}
