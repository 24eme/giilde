# Création de DRM sur le portail du CIVA

*La création des DRM sur le portail du CIVA peut se faire de différentes façons.*

Ce document d'aide détaille les différentes règles mise en place pour la création des DRM.

Ces stratégies de reprise de données et pré-remplissage des différents mouvements sont susceptibles d'évoluer rapidement c'est pourquoi ce document est principalement diffuser à des fins de communication de la part du CIVA.  

# Les différents types de créations d'une DRM

Dans l'interface, il y a 3 manières de créer une DRM :

1. Création d'une drm [**pré-remplie**](https://github.com/24eme/giilda/blob/master/doc/logiciels_tiers/civa/creation_drm.md#1-cr%C3%A9ation-dune-drm-pr%C3%A9-remplie)
2. Création d'une drm [**à néant**](https://github.com/24eme/giilda/blob/master/doc/logiciels_tiers/civa/creation_drm.md#2-cr%C3%A9ation-dune-drm-%C3%A0-n%C3%A9ant)
3. Création depuis un [**logiciel tiers**](https://github.com/24eme/giilda/blob/master/doc/logiciels_tiers/civa/creation_drm.md#3-cr%C3%A9ation-depuis-un-logiciel-tiers)

# Cadre général de saisie et reprise des produits par historique

Indépendemment du choix de création choisi, les produits des DRM sont toujours repris d'un mois à l'autre.

Par conséquence, il y a deux possibilités pour les produits d'une DRM créée qui dépend de l'historique du portail:

#### Il s'agit de la première DRM du portail :

La DRM n'aura **aucun produits** car il n'y a aucun historique DRM au CIVA.

Ainsi son catalogue sera soit :
- Les produits des DR/DS/Contrats si le choix de création est "pré-remplie" (voir [Création d'une drm pré-remplie](https://github.com/24eme/giilda/blob/master/doc/logiciels_tiers/civa/creation_drm.md#1-cr%C3%A9ation-dune-drm-pr%C3%A9-remplie))
- Totalement vierge (sans produits) s'il s'agit d'une création "néant" (voir [Création d'une drm à néant](https://github.com/24eme/giilda/blob/master/doc/logiciels_tiers/civa/creation_drm.md#2-cr%C3%A9ation-dune-drm-%C3%A0-n%C3%A9ant))
- Augmenté des produits issus du fichier importé depuis le logiciel tiers (voir [Création depuis un logiciel tiers](https://github.com/24eme/giilda/blob/master/doc/logiciels_tiers/civa/creation_drm.md#3-cr%C3%A9ation-depuis-un-logiciel-tiers))

#### Il ne s'agit pas de la première DRM, il existe une DRM précédente au CIVA

La DRM aura déjà **les produits du mois précédent** si leurs **stocks** de fin et de début ne sont **pas à 0**.

Les mêmes règles s'appliquent que pour la première DRM pour la reprise des produits.

Les potentiels produits issus de ces choix sont "ajoutés" au catalogue produits de la DRM s'il n'existe pas.


D'un point de vue général, les **stocks de début de mois** ne sont **pas éditables** pour assurer une continuité dans la cohérence des stocks.

Toutefois, ils deviennent **éditables** dans seulement trois cas :
- lorsqu'un produit est ajouté et n'a aucun stock de début
- lorsqu'il s'agit d'une DRM d'août (début de campagne propice aux régulations de stocks)
- lorsqu'il manque la DRM précédente de la DRM en cours. N'ayant pas de cohérence de stocks pour ce cas, il est nécessaire d'ajuster le stock début de mois des produits


## 1. Création d'une drm pré-remplie

*La création d'une DRM pré-remplie permet d'aggrémenter le catalogue "historique" en reprennant les produits (et certaine fois les mouvements) d'autres déclarations précédemment enregistré au CIVA.*

On ditinguera ici deux types de reprise :
 1. La reprise **"catalogue produits"** qui reprend les différents produit du document d'origine
 2. La reprise **"mouvements"** qui rappatrie certains volume de ces documents pour en faire des mouvements.

Il y a **trois déclarations** différentes donnant lieu à des reprises de données :

On reprend le catalogue produit du dernier document le plus récent entre la DR et la DS.

#### A. Reprise depuis la DS

En **août** de chaque année le **stock début de mois** est pré-remplie par le stock entrée dans la DS.

#### B. Reprise depuis la DR

Le mois de validationde la DR, on remplie l'entrée **"récolte"** avec le volume revendiqué sur place saisie dans la DR  

#### C. Reprise depuis les Contrats

On reprend **les produits des contrats** ayant été **enlevés au mois de la DRM**.

On reprend le **volume enlevé** du contrat, cela donne lieu à une nouvelle ligne dans les différentes **sorties contrats** du produit avec l'acheteur et la date d'enlèvement.

> Comme expliqué ci dessus ([drm avec historique](https://github.com/24eme/giilda/blob/master/doc/logiciels_tiers/civa/creation_drm.md#il-ne-sagit-pas-de-la-premi%C3%A8re-drm-il-existe-une-drm-pr%C3%A9c%C3%A9dente-au-civa)) et pour tout ces cas de figures, s'il y a un **historique de DRM**, les **produits ajoutés** à la DRM **enrichierons** le catalogue déjà repris de l'historique.```

## 2. Création d'une drm à néant

*C'est la création la plus simple. Elle sert dans le cadre d'une DRM n'ayant **aucun mouvements** (i.e. aucune entrées et aucune sorties)*

Lors de ce choix on arrive directement à la dernière étape **"Validation"**.

S'il existe une DRM précédente la liste des produits est reprise s'il reste du stock pour ces produits. En revanche il n'y aura aucun mouvement.

## 3. Création depuis un logiciel tiers

*La création d'une DRM avec un fichier issu de logiciel de cave permet d'insérer les produits et mouvements déjà saisie dans son logiciel.*

Un exemple de fichier est disponible [ici](https://github.com/24eme/giilda/blob/master/doc/logiciels_tiers/civa/exemple_export_drm.csv).

On différenciera au niveau des produits (lignes [CAVE](https://github.com/24eme/mutualisation-douane/tree/master/logiciels-tiers#description-des-lignes-cave)), deux types de lignes : **les lignes stocks** et les **lignes mouvements**.

#### 1. les lignes stocks :

Le stock début est repris depuis le fichier qu'a la condition ou il n'existe pas de DRM du mois précédent au CIVA.

S'il existe une DRM le mois précédent, c'est le stock de fin de mois de la DRM précente du CIVA qui deviendra le stock de début de mois comme dans le cadre général.

Le stock de fin n'est jamais repris tel quel car le stock de fin de mois est recalculé selon la formule : stock de début + entrées - sorties

#### 2. les lignes mouvements :

Elles sont toutes reprise depuis le fichier à partir du moment ou le produit a été trouvé et que ce mouvement possède le bon format (voir [les mouvements du CIVA](https://github.com/24eme/giilda/blob/master/doc/logiciels_tiers/civa/catalogue_mouvements.csv)).

