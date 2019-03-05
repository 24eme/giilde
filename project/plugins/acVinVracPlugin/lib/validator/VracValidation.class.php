<?php

class VracValidation extends DocumentValidation
{
    private $etablissement_client;

    private static $format = 'Y-m-d';

    /**
     * Constructeur
     *
     * @param Vrac $document Le vrac à vérifier
     * @param array $options Un tableau d'option
     */
    public function __construct(Vrac $document, $options = []) {
        $this->etablissement_client = EtablissementClient::getInstance();
        parent::__construct($document, $options);
    }

    /**
     * Configure les types de contrôles
     */
    public function configure() {
        parent::addControle('erreur', 'date', 'La date est mal formée');
        parent::addControle('erreur', 'inexistant', 'L\'entrée n\'existe pas');
        parent::addControle('erreur', 'float', 'L\'entrée renseignée n\'est pas un float');
        parent::addControle('erreur', 'bouteille', 'Un problème avec les bouteilles');

        parent::addControle('vigilance', 'mandataire', '');
        parent::addControle('vigilance', 'date', '');
        parent::addControle('vigilance', 'domaine', '');
    }

    /**
     * Contrôle les entrées du vrac
     */
    public function controle() {
        if (! $this->document->type_transaction || ! array_key_exists($this->document->type_transaction, VracClient::$types_transaction)) {
            parent::addPoint('erreur', 'inexistant', 'Le type de transaction n\'existe pas');
        }

        if (! $this->checkDate($this->document->_get('date_signature'))) {
            parent::addPoint('erreur', 'date', 'La date de signature doit être renseignée');
        }

        if (! $this->checkEtablissement($this->document->vendeur_identifiant, EtablissementFamilles::FAMILLE_PRODUCTEUR)) {
            parent::addPoint('erreur', 'inexistant', 'Le vendeur n\'existe pas');
        }

        if (! $this->checkEtablissement($this->document->acheteur_identifiant, EtablissementFamilles::FAMILLE_NEGOCIANT)) {
            parent::addPoint('erreur', 'inexistant', 'L\'acheteur n\'existe pas');
        }

        if ($this->document->representant_identifiant !== $this->document->vendeur_identifiant) {
            if (! $this->checkEtablissement($this->document->representant_identifiant, EtablissementFamilles::FAMILLE_REPRESENTANT)) {
                parent::addPoint('erreur', 'inexistant', 'Le représentant n\'existe pas');
            }
        }

        if ($this->document->mandataire_identifiant) {
            if (! $this->document->mandataire_exist) {
                parent::addPoint('vigilance', 'mandataire', 'Le flag de mandataire doit être à true');
            }

            if (! $this->checkEtablissement($this->document->mandataire_identifiant, EtablissementFamilles::FAMILLE_COURTIER)) {
                parent::addPoint('erreur', 'inexistant', 'Le mandataire n\'existe pas');
            }
        }

        if ($this->document->responsable && !in_array($this->document->responsable, ['vendeur', 'mandataire', 'acheteur'])) {
            parent::addPoint('erreur', 'inexistant', 'Le type de responsable n\'existe pas');
        }

        if (! $this->document->produit) {
            parent::addPoint('erreur', 'inexistant', 'Le produit n\'a pas été trouvé');
        }

        if ($this->document->domaine) {
            if ($this->document->categorie_vin === VracClient::CATEGORIE_VIN_GENERIQUE) {
                parent::addPoint('vigilance', 'domaine', 'Un domaine a été spécifié alors que le vin est générique');
            }
        }

        if (! $this->checkFloat($this->document->degre)) {
            parent::addPoint('erreur', 'float', 'Le degré n\'est pas un chiffre flottant');
        }

        if ($this->document->bouteilles_contenance_volume) {
            if ($this->document->type_transaction !== VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE) {
                parent::addPoint('erreur', 'bouteille', 'Le contrat a une contenance de bouteille mais n\'a pas été signalé en contrat bouteille');
            }

            if (! $this->checkContenance($this->document->bouteilles_contenance_volume, $this->document->bouteilles_contenance_libelle)) {
                parent::addPoint('erreur', 'bouteille', 'La contenance n\'existe pas dans le configurateur');
            }
        }

        if ($this->document->type_transaction === VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE
            && !$this->document->bouteilles_contenance_volume) {
                parent::addPoint('erreur', 'bouteille', 'Le contrat a été signalé comme contrat bouteille, mais n\'a pas de contenance');
        }

        if (! $this->checkFloat($this->document->jus_quantite)) {
            parent::addPoint('erreur', 'float', 'La quantité n\'est pas un chiffre flottant');
        }

        if (! $this->document->prix_initial_unitaire) {
            parent::addPoint('erreur', 'inexistant', 'Le prix unitaire est requis');
        }

        if (! $this->checkDate($this->document->date_limite_retiraison)) {
            parent::addPoint('erreur', 'date', 'La date de limite de retiraison n\'est pas valide');
        }

        if ($this->document->date_debut_retiraison) {
            if (! $this->checkDate($this->document->date_debut_retiraison)) {
                parent::addPoint('erreur', 'date', 'La date de début de retiraison n\'est pas valide');
            }

            if ($this->document->date_limite_retiraison < $this->document->date_debut_retiraison) {
                parent::addPoint('vigilance', 'date', 'La date de début de retiraison est supérieure à celle du début');
            }
        }

        if ($this->document->acompte && ! $this->checkFloat($this->document->acompte)) {
            parent::addPoint('erreur', 'float', 'L\'acompte n\'est pas un chiffre flottant');
        }
    }

    /**
     * Vérifie une date
     *
     * @param string $date Une date
     * @param string $format Le format de date à vérifier
     * @return bool
     */
    private function checkDate($date, $format = 'Y-m-d') {
        if (! $date) {
            return false;
        }

        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Vérifie l'existence d'un etablissement
     *
     * @param string $id L'identifiant de l'établissement
     * @param string $famille Famille de l'établissement : négociant, acheteur, producteur, …
     * @return bool
     */
    public function checkEtablissement($id, $famille) {
        if (strlen($id) !== 8) {
            return false;
        }

        $etablissement = $this->etablissement_client->find($id, acCouchdbClient::HYDRATE_JSON);

        return ($etablissement) ? $etablissement->famille === $famille : false;
    }

    /**
     * Vérifie si le nombre est un float
     *
     * @param string $number Le nombre à vérifier
     * @return bool
     * @see https://php.net/manual/en/function.is-float.php#85848
     */
    private function checkFloat($number) {
        return ($number === (string)(float) $number);
    }

    /**
     * Vérifie la contenance de la bouteille
     *
     * @param string $contenance La contenance de la bouteille
     * @param string $libelle Libelle de la contenance
     * @return bool
     */
    private function checkContenance($volume, $libelle) {
        $contenances = VracConfiguration::getInstance()->getContenances();

        if (! in_array($volume, $contenances)) {
            return false;
        }

        return array_search($volume, $contenances) === $libelle;
    }
}
