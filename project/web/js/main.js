 /**
 * Initialisation
 ******************************************/


(function ($)
{

  if(!('contains' in String.prototype)) {
      String.prototype.contains = function(str, startIndex) {
               return -1 !== String.prototype.indexOf.call(this, str, startIndex);
      };
  }

    var options = {
        selectors: {
            ajaxModal: '#ajax-modal'
        }
    };

    $(document).ready(function ()
    {
        $(document).initAdvancedElements();
        $.initQueryHash();
        $.initTableSelection();

        $(options.selectors.ajaxModal).on("show.bs.modal", function (e) {
            var link = $(e.relatedTarget);
            $(this).load(link.attr("href"), function () {
                $(this).initAdvancedElements();
            });
        });
        $(options.selectors.ajaxModal).on("hidden.bs.modal", function (e) {
            $(this).html("");
        });

    });


  	/**
  	 * Sélection de lignes de tableau
  	 * $.initTableSelection();
  	 ******************************************/
  	$.initTableSelection = function()
  	{
  		var tables = $('.table_selection');

  		tables.each(function()
  		{
  			var table = $(this);
  			var selecteurGlobal = table.find('thead .selecteur input');
  			var selecteursLignes = table.find('tbody .selecteur input');

  			// Selection / Déselection globale
  			selecteurGlobal.click(function()
  			{
  				if(selecteurGlobal.is(':checked'))
  				{
  					selecteursLignes.attr('checked', 'checked');
  				}
  				else
  				{
  					selecteursLignes.removeAttr('checked');
  				}
  			});

  			// Déselection unique
  			selecteursLignes.click(function()
  			{
  				var selecteur = $(this);

  				if(!selecteur.is(':checked'))
  				{
  					selecteurGlobal.removeAttr('checked');
  				}
  			});
  		});
  	};

    $.fn.initAdvancedElements = function () {

        var element = $(this);

        $(this).find('.input-float').inputNumberFormat({'decimal': 5, 'decimalAuto': 2});
        $(this).find('.input-integer').inputNumberFormat({'decimal': 0, 'decimalAuto': 0});

        $(this).find('[data-toggle="tooltip"]').tooltip({'container': 'body'});
        $(this).find('[data-toggle="popover"], .toggle-popover').popover({'container': 'body', trigger: "manual" , html: true, animation:false})
        .on("mouseenter", function () {
            var _this = this;
            $(this).popover("show");
            $(".popover").on("mouseleave", function () {
                $(_this).popover('hide');
            });
        })
        .on("mouseleave", function () {
            var _this = this;
            setTimeout(function () {
                if (!$(".popover:hover").length) {
                    $(_this).popover("hide");
                }
            }, 50);
        });

        $(this).find('[data-toggle="popover"], .toggle-popover').each(function() {
            if($(this).attr('data-content').match(/^#/)) {
                $(this).attr('data-content', $($(this).attr('data-content')).html());
            };
        });

        $(this).find('[data-toggle="popover"], .toggle-popover').on("shown.bs.popover", function(e) {
            $('.popover').initAdvancedElements();
        });

        $(this).find("select.select2").select2({
            allowClear: true
        });

        $(this).find(".select2autocomplete").each(function () {
            var urlAjax = $(this).data('ajax');
            var defaultValue = $(this).val();
            var defaultValueSplitted = defaultValue.split(',');
            var select2 = $(this);
            $(this).select2({
                onselected: function () {
                },
                initSelection: function (element, callback) {
                    if (defaultValue != '') {
                        callback({id: defaultValueSplitted[0], text: defaultValueSplitted[1]});
                        select2.val(defaultValueSplitted[0]);
                    }
                },
                placeholder: 'Entrer un nom',
                minimumInputLength: 3,
                formatInputTooShort: function (input, min) {
                    var n = min - input.length;
                    return  min + " caractère" + (n == 1 ? "" : "s") + " min";
                },
                formatNoMatches: function () {
                    return "Aucun résultat";
                },
                formatSearching: function () {
                    return "Recherche…";
                },
                allowClear: true,
                ajax: {
                    quietMillis: 150,
                    url: urlAjax,
                    dataType: 'json',
                    type: "GET",
                    data: function (term, page) {
                        return {
                            q: term
                        };
                    },
                    results: function (data) {
                        var results = [];
                        $.each(data, function (index, item) {
                            results.push({
                                id: index,
                                text: item
                            });
                        });
                        return {
                            results: results
                        }

                    }}
            });
        });

        $(this).find(".select2SubmitOnChange").on("change", function (e) {
            if (e.val) {
                $(this).parents('form').submit();
            }
        });

        $(this).find("a.to_autofocus").focus();

        $(this).find("input[type='radio'][autofocus='autofocus']").each(function(){
            var name = $(this).attr("name");
            $(document).find("input[name='"+name+"']").each(function(){
                if(!$(this).is(":checked") && $(this).is(':focus')){
                    $(this).blur();
                }
                if($(this).is(":checked") && !$(this).is(':focus')){
                     $(this).focus();
                }
            });

        });

        $(this).find('.select2permissifNoAjax').each(function() {
	    var element = $(this);
	    var id = $(this).attr('id');
	    $(this).select2({
                data: JSON.parse($(this).attr('data-choices')),
                allowClear: true,
                multiple: false,
                createSearchChoice: function (term, data) {
                    if ($(data).filter(function () {
                        return this.text.localeCompare(term) === 0;
                    }).length === 0) {
                        return {id: term, text: term + ' (nouveau)'};
                    }
                }
            }).on("select2-close", function (e) {
		lastValue = $('#'+id).val();
                $(this).select2("val", lastValue);
                $(this).val(lastValue);
                $('#s2id_'+id).find('.select2-chosen').text(lastValue);
            })
        });

        $(this).find('.hamzastyle').each(function () {
            var select2 = $(this);
            select2.select2({
                multiple: true,
                data: function () {
                    var data = [];
                    element.find('.hamzastyle-item').each(function () {
                        data = data.concat(JSON.parse($(this).attr('data-words')));
                    });

                    var data = unique(data.sort());

                    dataFinal = [];
                    for (key in data) {
                        if (data[key] + "") {
                            dataFinal.push({id: (data[key] + ""), text: (data[key] + "")});
                        }
                    }

                    return {results: dataFinal};
                }
            })
        });

        $(this).find('.hamzastyle').on("change", function (e) {
            var select2Data = $(this).select2("data");
            var selectedWords = [];
            for (key in select2Data) {
                selectedWords.push(select2Data[key].text);
            }

            if (!selectedWords.length) {
                document.location.hash = "";
            } else {
                document.location.hash = encodeURI("#filtre=" + JSON.stringify(selectedWords));
            }
        });

        $(this).find('.input-group.date').datetimepicker({
            locale: 'fr_FR',
            format: 'L',
            allowInputToggle: true,
            focusOnShow: true,
            useCurrent: false
        });

        $(this).find("form.form-ajax-modal").on('submit', function () {
            var form = $(this);
            var callback = $(this).attr('callback');
            $.post($(this).attr('action'),
                    $(this).serialize(),
                    function (data)
                    {
                        if (!data.success) {
                            var content = form.find(form.attr('data-content')).html(data.content);
                            content.initAdvancedElements();
                            return;
                        }

                        if (form.data('related-element')) {
                            $(form.data('related-element')).trigger("modal_callback", data);
                        }

                        $(options.selectors.ajaxModal).modal('hide');
                    }, "json");

            return false;
        });

        $(this).find('.modal-autoshow').modal({
            'show': true
        });

        $(this).find('.link-submit').on('click', function () {
            var form = $($(this).attr('data-form'));
            form.attr('action', $(this).attr('href'));
            form.submit();

            return false;
        });
        $(this).find('.pointer').on('click', function () {
            if ($(this).attr('data-pointer')) {
                $($(this).attr('data-pointer')).click();
            }

            return false;
        });

        $(this).find('.dynamic-element-add').on('click', function () {
            var content = $($($(this).attr('data-template')).html().replace(/var---nbItem---/g, UUID.generate()));
            $($(this).attr('data-container')).append(content);
            content.initAdvancedElements();
        });

        $(this).find('.dynamic-element-delete').on('click', function () {
            $($(this).attr('data-line')).find('input').val("");
            $($(this).attr('data-line')).find('input').trigger('keyup');
            $($(this).attr('data-line')).remove();
            if ($($(this).attr('data-lines')).length < 1 && $(this).attr('data-add')) {
                $($(this).attr('data-add')).trigger('click');
            }
        });

        $(this).find('.btn-dynamic-element-submit').on('click', function(e) {
            var vals = $(this).parents('form').serializeArray();

            $(this).parents('form').find('.dynamic-element-delete').each(function(){
                var ligne = $($(this).attr('data-line'));
                var hasValue = false;
                ligne.find('input, select, textarea').each(function() {
                    if($(this).attr('name') && $(this).val()) {
                        hasValue = true;
                    }
                });

                if(!hasValue) {
                    $($(this).attr('data-line')).remove();
                }
            });

            return true;
        });
        
        /**
      	 * Sélection de lignes de tableau
      	 ******************************************/
      	$(this).find('.table_selection thead .selecteur input').each(function(){
            $(this).on('click', function(){
    			var selecteursLignes = $(this).parents('table').find('tbody .selecteur input');
      			if($(this).is(':checked')) {
      				selecteursLignes.attr('checked', 'checked');
      			} else {
      				selecteursLignes.removeAttr('checked');
      			}
            });
      	});

        $(this).find('.table_selection tbody .selecteur input').on('click', function(e){

      				var selecteur = $(this).parents('table').find('thead .selecteur input');

      				if(!$(this).is(':checked'))
      				{
      					selecteur.removeAttr('checked');
      				}
      			});

        /**
         * Contrôle la bonne saisie de nombres dans
         * un champ
         * $(s).saisieNum(float, callbackKeypress);
         ******************************************/
        $.fn.saisieNum = function (float, callbackKeypress, callbackBlur)
        {
            var champ = $(this);

            // A chaque touche pressée
            champ.keypress(function (e)
            {
                var val = $(this).val();
                var touche = e.which;
                var ponctuationPresente = (val.indexOf('.') != -1 || val.indexOf(',') != -1);
                var chiffre = (touche >= 48 && touche <= 57); // Si chiffre

                // touche "entrer"
                if (touche == 13)
                    return e;

                // touche "entrer"
                if (touche == 0)
                    return e;

                // Champ nombre décimal
                if (float)
                {
                    // !backspace && !null && !point && !virgule && !chiffre
                    if (touche != 8 && touche != 0 && touche != 46 && touche != 44 && !chiffre)
                        return false;
                    // point déjà présent
                    if (touche == 46 && ponctuationPresente)
                        e.preventDefault();
                    // virgule déjà présente
                    if (touche == 44 && ponctuationPresente)
                        e.preventDefault();
                    // 2 décimales
                    if (val.match(/[\.\,][0-9][0-9]/) && chiffre && e.currentTarget && e.currentTarget.selectionStart > val.length - 3)
                        e.preventDefault();
                }
                // Champ nombre entier
                else
                {
                    if (touche != 8 && touche != 0 && !chiffre)
                        e.preventDefault();
                }

                if (callbackKeypress)
                    callbackKeypress();
                return e;
            });
          }

          $(this).find('.btn-loading').on('click', function (e) {
                $(this).addClass('disabled');
                $(this).text($(this).attr('data-loading-text'));
          });

    }


    $.initQueryHash = function () {
        $(window).on('hashchange', function () {
            if ($(document).find('.hamzastyle').length) {
                var params = jQuery.parseParams(location.hash.replace("#", ""));
                var filtres = [];
                if (params.filtre && params.filtre.match(/\[/)) {
                    filtres = JSON.parse(params.filtre);
                } else if (params.filtre) {
                    filtres.push(params.filtre);
                }

                var select2Data = [];
                for (key in filtres) {
                    select2Data.push({id: filtres[key], text: filtres[key]});
                }

                $(document).find('.hamzastyle').select2("data", select2Data);

                $(document).find('.hamzastyle-item').each(function () {
                    var words = $(this).attr('data-words');
                    var find = true;
                    for (key in filtres) {
                        var word = filtres[key];
                        if (words.indexOf(word) === -1) {
                            find = false;
                        }
                    }
                    if (find) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
            if ($(document).find('.nav.nav-tabs').length) {
                var params = jQuery.parseParams(location.hash.replace("#", ""));
                if (params.tab) {
                    $('.nav.nav-tabs a[aria-controls="' + params.tab + '"]').tab('show');
                }
            }
        });

        if (location.hash) {
            $(window).trigger('hashchange');
        }
    }

})(jQuery);
