function(doc) {
  	if ((doc.type != "Vrac") || (doc.valide.statut == null)) {

    	return;
    }

  	if (doc.domaine) {

  		emit([doc.vendeur_identifiant, (doc.campagne).split('-')[0], doc.domaine],  1);
  	}
}
