# Migration de CONFIGURATION à l'inter-campagne

L'intercampagne (juillet/août) est l'occasion de faire des changements structurants dans la CONFIGURATION.

Voici les étapes auxquelles il faut penser :

## Duplication de la configuration

Création d'une nouveau document CONFIGURATION dont la date est par exemple 2020-08-01

    ``php symfony configuration:fork CONFIGURATION-20200801 --application=APP``

Fork 2020-08-01 créé à partir de la configuration CONFIGURATION-XXXXXXXX
La nouvelle configuration CONFIGURATION-20200801 est configurée pour être utilisée à partir de 2020-08-01

## Suppression du noeud Correspondances

Il est préférable de supprimer le noeud correspondances de la configuration. Il est possible de le faire de cette manière :

    php symfony document:setvalue CONFIGURATION-20210801 "correspondances" --delete=true --application=APP

Le document CONFIGURATION-20200801@x_revision a été sauvé @y_revision, les valeurs suivantes ont été changés : correspondances:supprimé

## Renommage des noeuds de produits (et gestion de sa correspondances)

Pour un produit dont on doit faire évoluer la hash :

On considère le changement comme suit :

    HASH_FROM="/declaration/certification..."
    HASH_TO="/declaration/certification..."

    HASH_FROM_WITH_TIRET=$(echo $HASH_FROM | sed 's|/|-|g')

    php symfony document:replace-hash CONFIGURATION-20200801 --from="$HASH_FROM" --to="$HASH_TO" --application=APP

    php symfony document:add-in-collection CONFIGURATION-20200801 "correspondances" --key="$HASH_FROM_WITH_TIRET" --value="$HASH_TO" --application=APP

## Ajout de nouveaux produits

## Changement des droits (CVO et Douane)

### Lister les droits existants

Une tache permet de lister les droits existants :

    php symfony configuration:list-droits --application=APP 2018-08-01 cvo

Cette tache produit une ligne par type de droit avec les colonnes suivantes :

 - taux
 - hash du taux
 - nombre de produits concernés

Une option ``with_produit`` permet d'avoir une ligne par produit :

 - taux
 - hash du taux
 - produit concerné

Il est ainsi possible de voir le détail des produits concernés par certains noeuds afin rechercher les optitimisations possibles :

    php symfony configuration:list-droits --application=APP --with_produit=true 2018-08-01 cvo | grep '^3.46;'

Ces options fonctionne aussi pour les droits douane :

    php symfony configuration:list-droits --application=APP 2018-08-01 douane

### Ajouter des noeuds droits

### Nettoyer des noeuds non nessaires

## Changement des mouvements

## changement de favoris mouvements

## Changement du CURRENT
