# Spécifications techniques de l'implémentation du format de DRM attendues sur le portail d'IVBD

La spécification complète du format d'import attendu est détaillée ici : [Spécification générique du fichier d'import DRM pour logiciels tiers](https://github.com/24eme/mutualisation-douane/blob/master/logiciels-tiers/). Cette documentation "générique" est commune pour les portails déclaratifs du CIVA, du CIVP, d'Interloire, d'InterRhone, d'IVBD, d'IVBD et d'IVSE.

Cette page apporte un éclairage IVBD à la documentation générique. Elle permet d'accéder à la liste des produits aujourd'hui gérés par la plateforme d'IVBD (cette liste peut évoluer en fonction des besoins des ressortissants, n'hésitez donc pas à les remonter), la manière de les déclarer, ainsi que les mouvements désirés pour la DRM IVBD.

## Catalogue des produits spécifiques au portail de l'IVBD

Le catalogue produit nécessaire aux imports de DRM pour IVBD est décrit dans le fichier suivant : [Catalogue produit](catalogue_produits.csv)

Ce fichier comporte les différentes colonnes suivantes :

1. La certification : IGP, AOP, Vins sans IG ou Autres
2. Le genre : Tanquille ou Mousseux
3. L'appellation : Bergerac, Montravel,  Rosette, ...
4. La mention : Sélection de Grains Nobles, ...
5. Le lieu : (Aucun lieu défini pour le moment)
6. La couleur : Blanc, Blanc sec, Rouge...
7. Le cépage : (Aucun cépage défini pour le moment)

La dernière colonne indique le libellé complet du produit, le processus d'import ne tiendra pas compte de ce champs si les 7 champs d'identification sont remplis. Il sera utilisé que si une ambiguité ressort de l'exploitation de ces champs.

Pour plus de détails sur l'exploitation de ces champs, voir la [section "identification du vin" de la Spécification générique DRM pour logiciels tiers](https://github.com/24eme/mutualisation-douane/blob/master/logiciels-tiers/#description-des-lignes-cave).

## Catalogue des mouvements de DRM spécifiques au portail d'IVBD

Le catalogue des mouvements de DRM admis par le portail d'IVBD  [Catalogue mouvements](catalogue_mouvements.csv) est composé de trois colonnes :

1. Le type de DRM : suspendu ou acquitte
2. La catégorie du mouvement : stocks_debut, stocks_fin, entrees ou sorties
3. Le type du mouvement : achatcrd, vrac, repli...

## Exemple complet de fichier d'import de DRM

Un exemple spécifique de DRM à importer pour le portail d'IVBD est disponible ici : [Exemple de fichier d'import pour IVBD](exemple_export_drm.csv) .

Ce fichier reprend l'ensemble des spécificités décrites ci-dessus.

