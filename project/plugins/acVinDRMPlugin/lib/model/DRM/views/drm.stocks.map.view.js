function(doc) {
      if (doc.type != "DRM") {
          return;
      }

      if (!doc.valide.date_saisie) {
          return;
      }

      var societe = null;

      for (certification_key in doc.declaration.certifications) {
          var certification = doc.declaration.certifications[certification_key];
          for (genre_key in certification.genres) {
              var genre = certification.genres[genre_key];
              for (appellation_key in genre.appellations) {
                  var appellation = genre.appellations[appellation_key];
                  for(mention_key in appellation.mentions) {
                      var mention = appellation.mentions[mention_key];
                      for(lieu_key in mention.lieux) {
                          var lieu = mention.lieux[lieu_key];
                          for(couleur_key in lieu.couleurs) {
                              var couleur = lieu.couleurs[couleur_key];
                              for(cepage_key in couleur.cepages) {
                                  var cepage = couleur.cepages[cepage_key];
                                  var hash = "/declaration/certifications/"+certification_key+"/genres/"+genre_key+"/appellations/"+appellation_key+"/mentions/"+mention_key+"/lieux/"+lieu_key+"/couleurs/"+couleur_key+"/cepages/"+cepage_key;
                                 var total_debut_mois = 0;
                 				 var total_entrees = 0;
                				 var total_recolte = 0;
                				 var total_sorties = 0;
                				 var total_facturable = 0;
                				 var total = 0;
                                 var produit_libelle = null;
				                 var nbDetails = 0;
                				 for(detail_key in cepage.details) {
                                     detail = cepage.details[detail_key];
                				     total_debut_mois += detail.total_debut_mois;
                                     total_entrees += detail.total_entrees;
                				     total_recolte += detail.total_recolte;
                				     total_sorties += detail.total_sorties;
                				     total_facturable += detail.total_facturable;
                				     total += detail.total;
                				     if(!produit_libelle) {
                				     	produit_libelle = detail.produit_libelle;
                				     }
                				     nbDetails += 1;
                                 }
                    				if(nbDetails > 0)  {
                     				 emit([doc.campagne, societe, doc.identifiant, hash, doc.periode, doc.version], [total_debut_mois, total_entrees, total_recolte, total_sorties, total_facturable, total, doc.declarant.nom, produit_libelle]);
                    				}
                            } // Boucle cepage
                          } // Boucle couleur
                      } // Boucle lieu
                  } // Boucle mention
              } // Boucle appellation
          } // Boucle genre
      } // Boucle certification
  }
