function(doc) {
    if (doc.type != "DRM" && doc.type != 'SV12') {

        return;
    }

    if (!doc.valide.date_saisie) {

        return;
    }

    for(identifiant in doc.mouvements) {
        if(identifiant == doc.identifiant) {
            for (key in doc.mouvements[identifiant]) {
                var mouv = doc.mouvements[identifiant][key];
                pays = "";
        	    if (mouv.type_hash.match(/^export.*_details$/)) {
        		          pays = mouv.detail_libelle;
        	    }
                var coefficient_facturation = -1;
                if(mouv.coefficient_facturation) {
                    coefficient_facturation = mouv.coefficient_facturation;
                }
                emit([doc.type, identifiant, doc.campagne, doc.periode, doc._id, mouv.produit_hash, mouv.type_drm, mouv.type_hash, mouv.vrac_numero, mouv.detail_identifiant], [doc.declarant.nom, mouv.produit_libelle, mouv.type_drm_libelle, mouv.type_libelle, mouv.volume, mouv.vrac_destinataire, mouv.detail_libelle, mouv.date_version, mouv.version, mouv.cvo, mouv.facturable, doc._id+'/mouvements/'+identifiant+'/'+key, pays, mouv.facture, coefficient_facturation]);
            }
        }
    }
}
