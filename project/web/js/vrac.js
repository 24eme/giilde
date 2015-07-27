$(document).ready(function()
{
    if ($('#contrat_soussignes').length > 0) {
        initSoussignes();
    }

    if ($('#contrat_marche').length > 0) {
        initMarche();
    }

    if ($('#contrat_conditions').length > 0) {
        initConditions();
    }

});

var initSoussignes = function()
{
	var form = $("#contrat_soussignes");
	var numContrat = form.attr('data-numcontrat');
	var isTeledeclare = parseInt(form.attr('data-isteledeclare'));
	var etablissementPrincipal = form.attr('data-etablissementprincipal');
	var isCourtierResponsable = parseInt(form.attr('data-iscourtierresponsable'));

    $('.select-ajax').on('change', function() {
        if(!$(this).val()) {
            $($(this).attr('data-bloc')).html("");
            return;
        }
        $($(this).attr('data-bloc')).load($(this).attr('data-url'), {id: $(this).val()});
    });


	/*if (numContrat) {
		ajaxifySoussigne('getInfos', {autocomplete: '#vendeur_choice', 'numero_contrat': numContrat}, '#vendeur_informations', 'vendeur');
		ajaxifySoussigne('getInfos', {autocomplete: '#acheteur_choice', 'numero_contrat': numContrat}, '#acheteur_informations', 'acheteur');
		ajaxifySoussigne('getInfos', {autocomplete: '#mandataire_choice', 'numero_contrat': numContrat}, '#mandataire_informations', 'mandataire');
        majMandatairePanel();
	} else {
		ajaxifySoussigne('getInfos', '#vendeur_choice', '#vendeur_informations', 'vendeur');
		ajaxifySoussigne('getInfos', '#acheteur_choice', '#acheteur_informations', 'acheteur');
		ajaxifySoussigne('getInfos', '#mandataire_choice', '#mandataire_informations', 'mandataire');
	    majMandatairePanel();
	}
	
	if (isTeledeclare) {
		$(".btn_ajout_autocomplete a").on('click', function() {
            $("#vrac_soussigne").attr('action', $(this).attr('href'));
            $("#vrac_soussigne").submit();
            return false;
        });
        $("div#acheteur_choice input.ui-autocomplete-input").val(etablissementPrincipal);
        if (isCourtierResponsable) {
        	initTeledeclarationCourtierSoussigne();
        }
	}*/
};

var initMarche = function(isTeledeclarationMode)
{
	if ($('#vrac_bouteilles_contenance_libelle').length > 0) {
		$('#vrac_jus_quantite').keyup(function(e){
			var bouteille = $('#vrac_bouteilles_contenance_libelle').val();
			var hl = contenances[bouteille];
			 $('#correspondance_bouteille').html(formatNumber($('#vrac_jus_quantite').val() / hl)+' bouteilles');
		});
		$('#vrac_bouteilles_contenance_libelle').change(function(e){
			var bouteille = $('#vrac_bouteilles_contenance_libelle').val();
			var hl = contenances[bouteille];
			var val = $('#vrac_jus_quantite').val();
			if (!val) {
				val = 0;
			}
			 $('#correspondance_bouteille').html(formatNumber(val / hl)+' bouteilles');
		});
	}
};


var initConditions = function()
{

}


var ajaxifySoussigne = function(url, params, eltToReplace, famille) 
{
    if(typeof(params)=="string") { 
        $(params + ' select').on("change", function() {         
            $.get(url, {id : $(this).val(), famille : famille}, function(data) {
                $(eltToReplace).html(data);
            });
        });
    } else {
        for (var i in params)  {
            if(i == "autocomplete") {
                var autocompleteEltName = params[i];
                delete params.autocomplete;
                $(autocompleteEltName + ' select').on("change", function() {   
                    $.extend(params, {id : $(this).val(), famille : famille});
                    $.get(url, params, function(data) {
                        $(eltToReplace).html(data);
                    });
                });   
               break;
            }
        }
    }               
}

var init_ajax_nouveau = function()
{

    ajaxifyAutocompleteGet('getInfos', '#vendeur_choice', '#vendeur_informations');
    ajaxifyAutocompleteGet('getInfos', '#acheteur_choice', '#acheteur_informations');
    ajaxifyAutocompleteGet('getInfos', '#mandataire_choice', '#mandataire_informations');
};



