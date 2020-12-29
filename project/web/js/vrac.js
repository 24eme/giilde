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

var selectAjout = function(term){
    return $(this).val('AJOUT').change();
}

var initSoussignes = function()
{
	var form = $("#contrat_soussignes");
	var numContrat = form.attr('data-numcontrat');
	var isTeledeclare = parseInt(form.attr('data-isteledeclare'));
	var etablissementPrincipal = form.attr('data-etablissementprincipal');
	var isCourtierResponsable = parseInt(form.attr('data-iscourtierresponsable'));

  $("select.select2-soussigne-teledeclaration").each(function(){
    $(this).select2({
      allowClear: true,
        });
      });

        $("select.select2-soussigne-teledeclaration").each(function(){
          $(this).select2().parent().find('.select2-with-searchbox').find('.select2-results')
      .append('<li class="select2-results-dept-0 select2-result select2-result-selectable" role="presentation"><div class="select2-result-label" id="select2-result-label-11" role="option"><span class="select2-match"></span>Ajout récoltant</div></li>')
      .on('click', '.createLink', function() {
        alert('Add clicked');
      });
    });

    $('.select-ajax').on('change', function() {

        var select = $(this);
        var dataBloc = $($(this).attr('data-bloc'));

        var dataHide = $($(this).attr('data-hide'));
        if($(this).val() == "AJOUT"){
          window.location.href = $(this).attr('data-annuaire-link');
        }
        if(!$(this).val()) {
            dataBloc.addClass('hidden');
            dataHide.removeClass('hidden');
            $($(this).attr('data-bloc .container-ajax')).html("");
            select.parent().find('.select2-focusser').focus();
            return;
        }
        dataBloc.find('.container-ajax').load($(this).attr('data-url'), {id: $(this).val()}, function() {

            dataBloc.removeClass('hidden');
            dataHide.addClass('hidden');
            dataBloc.find('button').focus();
        });
    });

    $('.select-close').on('click', function() {
        var select = $($(this).attr('data-select'));
        select.val(null);
        select.change();
    });
    $('[data-toggle="tooltip"]').tooltip();
    $('.responsable input').each(function() {
    	$(this).addClass('hidden');
    });
    $('.responsable').on('click', function(e) {
    	e.preventDefault();
    	var active = $(this);
    	if (active.hasClass('text-success')) {
    		console.log('declick');
            active.removeClass('text-success');
            active.addClass('text-muted');
            active.find('input').removeAttr('checked');
    	} else {
        	$('.responsable').each(function() {
        		$(this).removeClass('text-success');
        		$(this).addClass('text-info');
        	});
            active.removeClass('text-muted');
            active.addClass('text-success');
            active.find('input').attr('checked', 'checked');
    	}
    });
};

var initMarche = function()
{
	if ($('#vrac_bouteilles_contenance_libelle').length > 0) {
		var bouteille = $('#vrac_bouteilles_contenance_libelle').val();
		var hl = contenances[bouteille];
		var val = $('#vrac_jus_quantite').val();
		if (val) {
			$('#correspondance_bouteille').html(formatNumber(val / hl)+' bouteilles');
		}
		$('#vrac_jus_quantite').keyup(function(e){
			bouteille = $('#vrac_bouteilles_contenance_libelle').val();
			hl = contenances[bouteille];
			 $('#correspondance_bouteille').html(formatNumber($('#vrac_jus_quantite').val() / hl)+' bouteilles');
		});
		$('#vrac_bouteilles_contenance_libelle').change(function(e){
			bouteille = $('#vrac_bouteilles_contenance_libelle').val();
			hl = contenances[bouteille];
			val = $('#vrac_jus_quantite').val();
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

var formatNumber = function (number)
{
    var number = number.toFixed(2) + '';
    var x = number.split('.');
    var x1 = x[0];
    var x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ' ' + '$2');
    }
    return x1 + x2;
}
