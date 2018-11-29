function(doc) { 
    if (doc.type == "DAE") {
		var produitTab = (doc.produit_key).split('/');
		emit([doc.date], [
			doc.date,
			doc.identifiant,
			doc.declarant.no_accises,
			doc.declarant.nom,
			doc.declarant.famille,
			doc.declarant.sous_famille,
			(doc.declarant.code_postal).substring(0,2),
			(produitTab[3] != 'DEFAUT' ? produitTab[3] : null),
			(produitTab[5] != 'DEFAUT' ? produitTab[5] : null),
			(produitTab[7] != 'DEFAUT' ? produitTab[7] : null),
			(produitTab[9] != 'DEFAUT' ? produitTab[9] : null),
			(produitTab[11] != 'DEFAUT' ? produitTab[11] : null),
			(produitTab[13] != 'DEFAUT' ? produitTab[13] : null),
			(produitTab[15] != 'DEFAUT' ? produitTab[15] : null),
			null,
			doc.produit_libelle,
			doc.label_libelle,
			doc.mention_libelle,
			doc.millesime,
			(doc.primeur ? '1' : '0'),
			doc.no_accises_acheteur,
			doc.nom_acheteur,
			doc.type_acheteur_libelle,
			doc.destination_libelle,
			doc.conditionnement_libelle,
			doc.contenance_libelle,
			(doc.contenance_hl*100),
			doc.quantite,
			doc.prix_unitaire,
			doc.volume_hl,
			doc.prix_hl ]);
	}
} 