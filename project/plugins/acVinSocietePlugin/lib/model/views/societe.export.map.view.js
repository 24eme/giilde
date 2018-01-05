function(doc) {
    if (doc.type != "Societe") {

        return;
    }

    cooperative = "NON";
    if (doc.cooperative && doc.cooperative!="0") {
        cooperative = "OUI";
    }

    type_fournisseur = null;
    if (doc.type_fournisseur) {
      type_fournisseur = doc.type_fournisseur.join("|");
    }

    emit([doc.interpro,
          doc.statut,
          doc.type_societe,
          doc._id,
          doc.identifiant],
         [doc.code_comptable_client,
          doc.code_comptable_fournisseur,
          type_fournisseur,
          doc.raison_sociale,
          doc.raison_sociale_abregee,
          cooperative,
          doc.siret,
          doc.code_naf,
          doc.no_tva_intracommunautaire,
          doc.enseignes.join("|"),
          doc.siege.adresse,
    doc.siege.adresse_complementaire,
          doc.siege.code_postal,
          doc.siege.commune,
          doc.siege.pays,
    doc.telephone,
          doc.fax,
          doc.email
          ]);
}
