function(doc) {
    if (doc.type != "SV12") {
        return;
    }

    ecarts = 0;
    if (doc.totaux.volume_ecarts)
	ecarts = doc.totaux.volume_ecarts;

    emit([doc.identifiant, doc.campagne, doc.periode, doc.version], [doc.declarant.nom, doc.declarant.cvi, doc.declarant.commune, doc.valide.statut, doc.valide.date_saisie, doc.totaux.volume_raisins, doc.totaux.volume_mouts, ecarts]);
}
