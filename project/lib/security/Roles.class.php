<?php

class Roles
{
    const ADMIN = 'admin';
    const OPERATEUR = 'operateur';

    const TRANSACTION = 'transactions';
    const CONTRAT = 'contrat';
    const DRM = 'drm';
    const FACTURE = 'facture';
    const DREV = 'drev';
    const SV12 = 'sv12';
    const DS = 'ds';
    const STOCK = 'stock';
    const ALERTE = 'alerte';
    const RELANCE = 'relance';
    const CONTACT = 'contacts';
    const STATS = 'stats';

    const COMPTA = 'compta';
    const PRESSE = 'presse';
    const DIRECTION = 'direction';
    const BUREAU = 'bureau';
    const AUTRE = 'autre';

    const ROLEDRM = 'DRM';

    const TELEDECLARATION = 'teledeclaration';
    const TELEDECLARATION_VRAC = 'teledeclaration_vrac';
    const TELEDECLARATION_DRM = 'teledeclaration_drm';
    const TELEDECLARATION_VRAC_CREATION = 'teledeclaration_vrac_creation';
    const TELEDECLARATION_DOUANE = 'teledeclaration_douane';

    const OBSERVATOIRE = 'observatoire';

    public static $teledeclarationLibelles = array(
      self::TELEDECLARATION => "Teledeclaration",
      self::TELEDECLARATION_VRAC => "Teledeclaration signature contrats",
      self::TELEDECLARATION_VRAC_CREATION => "Teledeclaration création contrats",
      self::TELEDECLARATION_DRM => "Teledeclaration DRM",
      self::TELEDECLARATION_DOUANE => "Transmission Ciel",
      );

      public static $teledeclarationLibellesShort = array(
        self::TELEDECLARATION_VRAC => "Signature contrats",
        self::TELEDECLARATION_VRAC_CREATION => "Création contrats",
        self::TELEDECLARATION_DRM => "DRM",
        self::TELEDECLARATION_DOUANE => "Transmission Ciel",
        );

    protected static $hierarchy = array(
        self::ADMIN => array(self::OPERATEUR, self::TRANSACTION),
        self::OPERATEUR => array(),
        self::TRANSACTION => array(self::CONTRAT,
                                   self::DRM,
                                   self::FACTURE,
                                   self::DREV,
                                   self::SV12,
                                   self::DS,
                                   self::STOCK,
                                   self::ALERTE,
                                   self::RELANCE,
                                   self::CONTACT),
        self::TELEDECLARATION_DRM => array(self::TELEDECLARATION),
        self::TELEDECLARATION_VRAC => array(self::TELEDECLARATION),
        self::COMPTA => array(self::CONTACT),
        self::PRESSE => array(self::CONTACT),
        self::DIRECTION => array(self::CONTACT),
        self::BUREAU => array(self::CONTACT),
        self::AUTRE => array(self::CONTACT),
        self::ROLEDRM => array(self::DRM),
    );

    public static function getRoles($role) {
        $roles = array($role);

        if(isset(self::$hierarchy[$role])) {
            foreach(self::$hierarchy[$role] as $r) {
                $roles = array_merge($roles, self::getRoles($r));
            }
        }

        return $roles;
    }

    public function getRolesCompte() {
        return array(self::CONTRAT => "Contrat");
    }
}
