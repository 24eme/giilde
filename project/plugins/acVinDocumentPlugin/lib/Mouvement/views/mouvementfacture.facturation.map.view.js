function(doc) {
      if (doc.type != "DRM" && doc.type != "SV12" && doc.type != "MouvementsFacture") {

          return;
      }

      if (!doc.valide.date_saisie) {

          return;
      }

      region = doc.region;

      for(identifiant in doc.mouvements) {
          for(key in doc.mouvements[identifiant]) {
              var mouv = doc.mouvements[identifiant][key];
              if(mouv.facture == 1 || mouv.facturable != 1) {
                  continue;
              }
              if (mouv.region) {
            region = mouv.region;
              }
        var coefficient_facturation = -1;
        if(mouv.coefficient_facturation) {
          coefficient_facturation = mouv.coefficient_facturation;
        }
        var categorie = (mouv.categorie)? mouv.categorie : doc.libelle;
        var mouv_hash = (mouv.produit_hash)? mouv.produit_hash : mouv.identifiant_analytique;
        var type_hash = (mouv.type_hash)? mouv.type_hash : doc.libelle;
        var mouv_produit_libelle = (mouv.produit_libelle)? mouv.produit_libelle : mouv.identifiant_analytique_libelle;
        var mouv_quantite = (mouv.quantite) ? mouv.quantite : mouv.volume * coefficient_facturation;
        var mouv_prix = (mouv.prix_unitaire)? mouv.prix_unitaire : mouv.cvo;
        var type_libelle = (mouv.type_libelle)? mouv.type_libelle : mouv.libelle;

        var docId = doc.type+'-'+doc.identifiant+'-'+doc.periode;
         var vrac_destinataire = mouv.vrac_destinataire;
         if (doc.type == "MouvementsFacture") {
            docId=doc._id;
            vrac_destinataire = type_libelle;
         }

                emit([mouv.facture, mouv.facturable, region, identifiant, doc.type, categorie , mouv_hash, doc.periode, mouv.date, mouv.vrac_numero, vrac_destinataire, type_hash , mouv.detail_identifiant], [mouv_produit_libelle , type_libelle, mouv_quantite, mouv_prix, mouv.vrac_destinataire, mouv.detail_libelle, docId, doc._id+':'+key]);


          }
     }
  }
