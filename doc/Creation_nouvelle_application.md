# Création d'une nouvelle application

L'exemple de création dans ce cas est pour l'application civa

Copier l'application à partir de l'une d'elle :

> cp -r apps/generique apps/civa

Renommer le fichier

> mv apps/civa/config/{generique,civa}Configuration.class.php

changer le nom de la classe dans civaConfiguration.class.php :

> class civaConfiguration extends sfApplicationConfiguration

Créer les fichiers d'index

> cp web/index.php web/civa.php
> cp web/generique_dev.php web/civa_dev.php

Changer l'application dans ces 2 nouveaux fichiers

* web/civa.php :

> $configuration = ProjectConfiguration::getApplicationConfiguration('civa', 'prod', false);

* web/civa_dev.php :

> $configuration = ProjectConfiguration::getApplicationConfiguration('civa', 'dev', true);
