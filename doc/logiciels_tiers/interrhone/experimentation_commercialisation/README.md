# Format d'échange DTI+ pour l'expérimentation commercialisation

Dans le cadre de l'expérimentation commercialisation proposé par InterRhone., une interface DTI+ a été développé afin de permettre les imports des données au format tableur (CSV).

## Standard d'échange de données

Les données échangées en mode lecture ou écriture se font sous le [format tableur/CSV](https://fr.wikipedia.org/wiki/Comma-separated_values). La plateforme supporte indifféremment les séparateurs virgules (« , ») ou point-virgules (« ; »). En revanche, il est nécessaire qu'un seul type de séparateur soit utilisé  au sein d'un même document.

La plateforme de télédéclération est insensible à la casse et aux caractères accentués. Les chaînes de caractères « Côte » ou « cote » seront donc traitées de manière identique.
Il faut noter toute fois, qu'en cas d'utilisation de caractères accentués, ces caractères devront être encodés en [UTF-8](https://fr.wikipedia.org/wiki/UTF-8).

Débuter une ligne par le caractère « #  » permet de définir des commentaires. Elles ne sont donc pas prises en compte par la plateforme.

Les nombres décimaux peuvent avoir pour délimiteur de décimal une virgule « , » ou un point « . ». Dans le cas ou la virgule « , » est choisi, bien faire attention qu'il n'y ait pas de confusion avec le séparateur du CSV : le recours à la virgule « , » comme délimiteur de décimal impose donc le recours au point-virgule « ; » comme séparateur de champs.

## Sécurité des transferts

Toutes les connexions réalisées sur l'interface d'import des données de commercialisation se feront via le protocole [HTTPS](https://tools.ietf.org/html/rfc2818).

## Format des données

Les fichiers attendus doivent contenu une ligne par information de commercialisation. Il est organisé en trois parties :

 - informations relatives au déclarant
 - informations relatives au produit déclaré
 - informations relatives à la commercialisation réalisée

Le format d'import et d'export des données est identique, des champs réservés sont donc prévus à ces fins.

Voici le détails de chacune des colonnes attendues :

### section déclarant

 - A (colonne n°1) : date de la commercialisation (format AAAA-MM-JJ)
 - B (colonne n°2) : identifiant declarvins du déclarant (le vendeur)
 - C (colonne n°3) : numéro d'accises du déclarant (le vendeur)
 - D (colonne n°4) : nom du déclarant (le vendeur)
 - E (colonne n°5) : champs réservé (stat famille)
 - F (colonne n°6) : champs réservé (stat sous famille)
 - G (colonne n°7) : champs réservé (stat département)

### section produit

la description du produit sujet à commercialisation se fait sous le même format que pour l'[interface DRM](https://github.com/24eme/mutualisation-douane/tree/master/logiciels-tiers#description-des-lignes-cave)

 - H (colonne n°8) : code ou nom de la certification du vin (champ obligatoire si la colonne P (n° 16) n'est pas renseigné)  
 - I (colonne n°9) : nom ou code du genre du vin (champ obligatoire si la colonne P (n° 16) n'est pas renseigné)  
 - J (colonne n°10) : nom ou code du appellation du vin (champ facultatif)
 - K (colonne n°11) : nom ou code du mention du vin (champ facultatif)  
 - L (colonne n°12) : nom ou code du lieu du vin (champ facultatif)
 - M (colonne n°13) : nom ou code du couleur du vin (champ obligatoire si la colonne P (n° 16) n'est pas renseigné)
 - N (colonne n°14) : nom ou code du cépage du vin (champ facultatif)
 - O (colonne n°15) : Le complément du vin (champ facultatif)
 - P (colonne n°16) : Le libellé personnalisé du vin (champ facultatif sauf si les colonnes H à N ne sont pas renseignées) pouvant contenir entre parenthèses le code INAO ou le libellé fiscal du produit
 - Q (colonne n°17) : label du produit : "conventionnel", "biologique", ... (champ facultatif)
 - R (colonne n°18) : mention de domaine ou château revendiqué ("domaine", "château" ou vide)
 - S (colonne n°19) : millésime (au format AAAA) (champ facultatif)

### section commercialisation

 - T (colonne n°20) : n° accise de l'acheteur (champ facultatif)
 - U (colonne n°21) : nom acheteur (champ facultatif)
 - V (colonne n°22) : type acheteur ("Importateur", "Négociant région" ou "Négociant/Union Vallée du Rhône", négociant hors région, "GD" ou "Grande Distribution", "Discount", "Grossiste", "Caviste", "VD" ou "Vente directe", "Autre", ...)
 - W (colonne n°23) : nom du pays de destination ou son code ISO 3166
 - X (colonne n°24) : type de conditionnement (VRAC ou HL, Bouteille, BIB)
 - Y (colonne n°25) : libellé conditionnement
 - Z (colonne n°26) : contenance conditionnement en litres
 - AA (colonne n°27) : quantité de conditionnement (en nombre de bib, de bouteille ou, pour le vrac, en hl)
 - AB (colonne n°28) : prix unitaire (prix en € par bouteille, bib ou hl)
 - AC (colonne n°29) : champ réservé (stat qtt hl)
 - AD (colonne n°30) : champ réservé (stat prix hl)

Il est possible de déclarer en une seule ligne plusieurs commercialisations.
