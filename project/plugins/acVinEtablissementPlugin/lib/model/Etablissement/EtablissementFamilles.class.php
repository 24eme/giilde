<?php
class EtablissementFamilles
{

    const FAMILLE_PRODUCTEUR = "PRODUCTEUR";
    const FAMILLE_PRODUCTEUR_VINIFICATEUR = "PRODUCTEUR_VINIFICATEUR";
    const FAMILLE_NEGOCIANT = "NEGOCIANT";
    const FAMILLE_COOPERATIVE = "COOPERATIVE";
    const FAMILLE_COURTIER = "COURTIER";
    const FAMILLE_REPRESENTANT = "REPRESENTANT";

    // /!\ cooperative est une pseudo famille, elle est basée sur l'exploitation du champ cooperative
    const PSEUDOFAMILLE_COOPERATIVE = "COOPERATIVE";

    const SOUS_FAMILLE_CAVE_PARTICULIERE = "CAVE_PARTICULIERE";
    const SOUS_FAMILLE_CAVE_COOPERATIVE = "CAVE_COOPERATIVE";
    const SOUS_FAMILLE_REGIONAL = "REGIONAL";
    const SOUS_FAMILLE_EXTERIEUR = "EXTERIEUR";
    const SOUS_FAMILLE_ETRANGER = "ETRANGER";
    const SOUS_FAMILLE_UNION = "UNION";
    const SOUS_FAMILLE_VINIFICATEUR = "VINIFICATEUR";

    protected static $familles = array(
    	self::FAMILLE_PRODUCTEUR => "Producteur",
    	self::FAMILLE_NEGOCIANT => "Négociant",
    	self::FAMILLE_COOPERATIVE => "Coopérative",
    	self::FAMILLE_COURTIER => "Courtier",
		self::FAMILLE_REPRESENTANT => "Representant"
    );

    protected static $type_societe_famille = array(
        SocieteClient::TYPE_OPERATEUR => array(self::FAMILLE_PRODUCTEUR, self::FAMILLE_NEGOCIANT, self::FAMILLE_COOPERATIVE, self::FAMILLE_REPRESENTANT),
        SocieteClient::TYPE_COURTIER => array(self::FAMILLE_COURTIER),
        SocieteClient::TYPE_AUTRE => array(),
    );

    protected static $sous_familles = array(
    	self::FAMILLE_PRODUCTEUR => array(self::SOUS_FAMILLE_CAVE_PARTICULIERE => "Cave particulière",
                                          self::SOUS_FAMILLE_CAVE_COOPERATIVE => "Cave coopérative"),
    	self::FAMILLE_NEGOCIANT => array(self::SOUS_FAMILLE_REGIONAL => "Régional",
                                         self::SOUS_FAMILLE_EXTERIEUR => "Extérieur",
                                         self::SOUS_FAMILLE_ETRANGER => "Etranger",
                                         self::SOUS_FAMILLE_UNION => "Union",
                                         self::SOUS_FAMILLE_VINIFICATEUR => "Vinificateur"),
    	self::FAMILLE_COURTIER => array(),
    	self::FAMILLE_REPRESENTANT => array()
    );

    protected static $droits = array(
    	"PRODUCTEUR_CAVE_PARTICULIERE" => array(EtablissementDroit::DROIT_DRM_DTI, EtablissementDroit::DROIT_DRM_PAPIER, EtablissementDroit::DROIT_VRAC),
    	"PRODUCTEUR_CAVE_COOPERATIVE" => array(EtablissementDroit::DROIT_DRM_DTI, EtablissementDroit::DROIT_DRM_PAPIER, EtablissementDroit::DROIT_VRAC),
    	"NEGOCIANT_REGIONAL" => array(EtablissementDroit::DROIT_DRM_PAPIER, EtablissementDroit::DROIT_VRAC),
    	"NEGOCIANT_EXTERIEUR" => array(EtablissementDroit::DROIT_DRM_PAPIER, EtablissementDroit::DROIT_VRAC),
    	"NEGOCIANT_ETRANGER" => array(EtablissementDroit::DROIT_DRM_PAPIER, EtablissementDroit::DROIT_VRAC),
    	"NEGOCIANT_UNION" => array(EtablissementDroit::DROIT_DRM_PAPIER, EtablissementDroit::DROIT_VRAC),
    	"NEGOCIANT_VINIFICATEUR" => array(EtablissementDroit::DROIT_DRM_DTI, EtablissementDroit::DROIT_DRM_PAPIER, EtablissementDroit::DROIT_VRAC),
    	"COURTIER" => array(EtablissementDroit::DROIT_VRAC),
    	"REPRESENTANT" => array(EtablissementDroit::DROIT_VRAC)
    );

    public static function getFamilles()
    {
    	return self::$familles;
    }

    public static function getFamillesForJs()
    {
    	$sousFamilles =  self::getSousFamilles();
    	$result = array();
    	foreach ($sousFamilles as $key => $value) {
    		$result[$key] = $value;
    	}
    	return $result;
    }

    public static function getFamillesByTypeSociete($typeSociete) {
        $famillesKey = (isset(self::$type_societe_famille[$typeSociete])) ? self::$type_societe_famille[$typeSociete] : array();
        $familles = array();

        foreach ($famillesKey as $familleKey) {
            $familles[$familleKey] = self::$familles[$familleKey];
        }

        return $familles;
    }

    public static function getSousFamilles()
    {
    	return self::$sous_familles;
    }

    public static function getSousFamillesByFamille($famille)
    {
    	$famille = self::getKey($famille);
    	if (!in_array($famille, array_keys(self::getFamilles()))) {
    		throw new sfException('La clé famille "'.$famille.'" n\'existe pas');
    	}
    	$sousFamilles = self::getSousFamilles();
    	return $sousFamilles[$famille];
    }

    public static function getDroits()
    {
    	return self::$droits;
    }

    public static function getDroitsByFamilleAndSousFamille($famille, $sousFamille = null)
    {
    	$famille = self::getKey($famille);
    	$sousFamille = self::getKey($sousFamille);
    	if (!in_array($famille, array_keys(self::getFamilles()))) {
    		throw new sfException('La clé famille "'.$famille.'" n\'existe pas');
    	}
    	$index = $famille;
    	if ($sousFamille) {
    		if (!in_array($sousFamille, array_keys(self::getSousFamillesByFamille($famille)))) {
    			throw new sfException('La clé sous famille "'.$sousFamille.'" n\'existe pas pour la famille "'.$famille.'"');
    		}
    		$index .= '_'.$sousFamille;
    	}
    	$droits = self::getDroits();
    	if (!in_array($index, array_keys($droits))) {
    		throw new sfException('Aucun droit défini pour la famille "'.$famille.'" et la sous famille "'.$sousFamille.'"');
    	}
    	return $droits[$index];
    }

    public static function getFamilleLibelle($famille = null)
    {
    	$famille = self::getKey($famille);
    	$familles = self::getFamilles();
    	if (!in_array($famille, array_keys($familles))) {
    		throw new sfException('La clé famille "'.$famille.'" n\'existe pas');
    	}
    	return $familles[$famille];
    }


    public static function getSousFamilleLibelle($famille = null, $sousFamille = null)
    {
    	$famille = self::getKey($famille);
    	$sousFamille = self::getKey($sousFamille);
    	$sousFamilles = self::getSousFamillesByFamille($famille);
    	if (!in_array($sousFamille, array_keys($sousFamilles))) {
    		throw new sfException('La clé sous famille "'.$sousFamille.'" n\'existe pas');
    	}
    	return $sousFamilles[$sousFamille];
    }

    public static function getKey($libelle)
    {
    	return str_replace('-', '_', strtoupper(KeyInflector::slugify($libelle)));
    }
}
