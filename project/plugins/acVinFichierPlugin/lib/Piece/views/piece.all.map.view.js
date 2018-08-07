function(doc) {
    if (!doc.pieces) {
        return;
    }

    for(key in doc.pieces) {
    	var piece = doc.pieces[key];
        emit([piece.visibilite, piece.identifiant, piece.date_depot, piece.libelle, piece.mime, piece.source], [key, piece.fichiers]);
    }
}