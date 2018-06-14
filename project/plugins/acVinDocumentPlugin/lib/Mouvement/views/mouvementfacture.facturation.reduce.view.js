function(keys,values,rereduce) {

    var merge_field = function(field,pos,merged_field){
        var field_copy = field;
        if(field_copy == null) return merged_field;
        if(typeof(field_copy)==='string') {
            merged_field.push(field_copy);
        }else if(typeof(field_copy)==='number') {
            merged_field.push(field_copy);
        }
        else {
            for(key in field_copy) {
                if(in_array(field_copy[key], merged_field)) {

                    continue;
                }
                merged_field.push(field_copy[key]);
            }
        }

        merged_field.sort();

        for (var i = 1; i < merged_field.length; i++){
            if (merged_field[i-1] === merged_field[i]) {
                    merged_field.splice(i, 1);
            }
        }
        var result = [];
        for (var i = 0; i < merged_field.length; i++){
            if (merged_field[i] != null) {
                    result.push(merged_field[i]);
            }
        }
        return result;
    }

    var in_array = function(value, tab) {
        for(key in tab) {
            if(tab[key] == value) {

                return true;
            }
        }

        return false;
    }

    var reduce_result = function(tab)
    {
        if(tab.length) return tab[0];

        return null;
    }

    var reduce_origines = function(tab)
    {
        if(tab.length == 1) return tab[0];

        return false;
    }

    var origines = new Array();
    var produits_libelles = new Array();
    var types_libelles = new Array();
    var total_volume = 0;
    var cvos = new Array();
    var vrac_destinataire = new Array();
    var details_libelles = new Array();
    var identifiants = new Array();

    for(item in values)
    {
        produits_libelles = merge_field(values[item][0],item,produits_libelles);
        types_libelles = merge_field(values[item][1],item,types_libelles);
        total_volume += values[item][2];
        cvos = merge_field(values[item][3],item,cvos);
        details_libelles = merge_field(values[item][5],item,details_libelles);
        vrac_destinataire = merge_field(values[item][6],item,vrac_destinataire);
        identifiants = merge_field(values[item][7],item,identifiants);
        origines = merge_field(values[item][9],item,origines);
    }

    produits_libelles = reduce_result(produits_libelles);
    types_libelles = reduce_result(types_libelles);
    cvos = reduce_result(cvos);
    details_libelles = reduce_result(details_libelles);
    vrac_destinataire = reduce_result(vrac_destinataire);
    identifiants = reduce_result(identifiants);
    origines = reduce_origines(origines);
    result = [produits_libelles, types_libelles, total_volume, cvos, vrac_destinataire, details_libelles, identifiants, origines];

    return result;
 }
