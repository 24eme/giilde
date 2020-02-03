;
(function ($, window, undefined) {

    var debug = false;

    /**
     Class Colonnes
     **/
    $.Colonnes = function ()
    {
        this.element = $('#colonnes_dr');
        this.element_saisies = $('#col_saisies');
        this.element_saisies_container = $('#col_saisies_cont');
        this.element_colonne_intitules = $('#colonne_intitules');
        this.colonnes = new Array();
        this.groupes_rows = new GroupesRows(this);
        this.event_valider = function () {}
        this.event_focus = function () {}
        this.event_unfocus = function () {}
        this.event_enabled = function () {}
        this.event_disabled = function () {}
        this.event_valider_champ_modification = function () {}
        this.event_colonne_init = function () {}
        this.event_edition_on = function () {}
        this.event_edition_off = function () {}

        this.init = function () {
            this.colonnes = new Array();

            var colonne_intitule = new ColonneIntitule(this, this.element_colonne_intitules);
            colonne_intitule.init();
            this.colonnes.push(colonne_intitule);

            var object = this;
            var colonnes = this.colonnes;
            this.element_saisies.find('.col_recolte').each(function (i)
            {
                var colonne = new ColonneProduit(object, $(this));
                colonne.init();
                colonnes.push(colonne);
            });

            this.groupes_rows.init();

            this.update();
        }

        this.add = function (html) {
            this.element_saisies_container.append(html);
            var colonne = new ColonneProduit(this, this.element_saisies.find('.col_recolte').last());
            colonne.init();
            this.colonnes.push(colonne);
            this.update();
            colonne.unActive();
            if (colonne.isActive()) {
                colonne.active();
            }
            colonne.focus();
            colonne.focusChampDefault();

            return colonne;
        }

        this.findByHash = function (hash) {
            for (key in this.colonnes) {
                if (this.colonnes[key].element.attr('data-hash') == hash) {

                    return this.colonnes[key];
                }
            }

            return false;
        }

        this.getActive = function () {
            for (key in this.colonnes) {
                if (this.colonnes[key].isActive()) {

                    return this.colonnes[key];
                }
            }

            return false;
        }

        this.hasActive = function () {

            return this.getActive() !== false;
        }

        this.getFocus = function () {
            for (key in this.colonnes) {
                if (this.colonnes[key].isFocus()) {

                    return this.colonnes[key];
                }
            }

            return false;
        }

        this.hasFocus = function () {

            return this.getFocus() !== false;
        }

        this.unFocus = function () {
            for (key in this.colonnes) {
                if (this.colonnes[key].isFocus()) {
                    this.colonnes[key].unFocus();
                }
            }
        }

        this.unActive = function () {
            for (key in this.colonnes) {
                if (this.colonnes[key].isActive()) {
                    this.colonnes[key].unActive();
                }
            }
        }

        this.updateScroll = function ()
        {
            var colToScroll = null;

            if (this.hasActive()) {
                colToScroll = this.getActive();
            } else if (this.hasFocus()) {
                colToScroll = this.getFocus();
            }

            if(colToScroll && colToScroll.getPrevious()) {
                colToScroll = colToScroll.getPrevious();
            }

            if(colToScroll) {
                this.element_saisies.scrollTo(colToScroll.element, 200);
            } else
            {
                this.element_saisies.scrollTo({top: 0, left: 0}, 200);
            }
        };

        this.enabled = function () {
            for (key in this.colonnes) {
                this.colonnes[key].enabled();
            }
        }

        this.disabled = function () {
            for (key in this.colonnes) {
                this.colonnes[key].disabled();
            }
        }

        this.update = function () {
            this._updateLargeur();
            this._updateHauteur();
            this.groupes_rows.update();
        }

        this._updateLargeur = function () {
            var largeur = 0;
            var cols = this.element_colonne_intitules.add(this.element_saisies_container);

            for (key in this.colonnes) {
                if (this.colonnes[key].getClass() == "ColonneProduit") {
                    largeur += this.colonnes[key].element.outerWidth(true);
                }
            }
            this.element_saisies_container.width(largeur);
        }

        this._updateHauteur = function () {
            var cols = this.element_colonne_intitules.add(this.element_saisies_container);

            cols.find('.head').hauteurEgale();
        }
    }

    /**
     Class ColonneIntitule
     **/
    function ColonneIntitule(colonnes, element)
    {
        this.colonnes = colonnes;
        this.element = element;
        this.groupes = new Groupes(this);

        this.init = function () {
            if (debug) {
                console.log('init colonne intitule');
                console.log(this.element);
            }

            this.groupes.init();
        }

        this.isActive = function () {

            return false;
        }

        this.isFocus = function () {

            return false;
        }

        this.enabled = function () {

            return false;
        }

        this.disabled = function () {

            return false;
        }

        this.getClass = function () {

            return 'ColonneIntitule';
        }
    }

    /**
     Class ColonneProduit
     **/
    function ColonneProduit(colonnes, element)
    {

        this.colonnes = colonnes;
        this.element = element;
        this.groupes = new Groupes(this);
        this.boutons_etapes = $('#btn_etape_dr a');

        this.init = function () {
            if (debug) {
                console.log('init colonne produit');
                console.log(this.element);
            }

            this._initBoutons();
            this._initClavier();
            this.groupes.init();
            this.colonnes.event_colonne_init(this);
            this.calculer();
        }

        this.isActive = function () {

            return this.element.hasClass('col_active');
        }

        this.active = function () {
            if (this.isActive()) {
                return;
            }

            if (debug) {
                console.log('colonne active');
                console.log(this.element);
            }

            this.colonnes.unActive();
            this.focus();
            this.element.addClass('col_active');
            this.element.addClass('active');
            this.element.find('.col_btn').removeClass('invisible');
            this.colonnes.disabled();

            // On désactive les boutons et champs autour de la colonne
            this.desactiveElements();
        }

        this.unActive = function () {
            if (debug) {
                console.log('colonne unactive');
                console.log(this.element);
            }

            this.element.removeClass('col_active');
            this.element.removeClass('active');
            this.element.find('.col_btn').addClass('invisible');
            this.colonnes.enabled();

            // On réactive les boutons et champs autour de la colonne
            this.reactiveElements();
        }

        this.desactiveBoutons = function (e)
        {
            e.preventDefault();
        }

        this.desactiveElements = function () {

            this.colonnes.event_edition_on();
        }

        this.reactiveElements = function () {

            this.colonnes.event_edition_off();
        }

        this.enabled = function () {
            if (debug) {
                console.log('colonne enabled');
                console.log(this.element);
            }

            this.element.removeClass('col_inactive');
            this.element.removeClass('inactive');
            this.groupes.enabled();
            this.element.find('a').removeAttr('disabled');

            this.colonnes.event_enabled(this);
        }

        this.disabled = function () {
            if (this.isActive()) {
                return;
            }

            this.element.addClass('col_inactive');
            this.element.addClass('inactive');
            this.groupes.disabled();
            this.element.find('a').attr('disabled', 'disabled');

            this.colonnes.event_disabled(this);
        }

        this.notifyNotFavoris = function() {
          var nofavorisEntrees = this.element.find('input.not_a_favoris_entrees');
          var nofavorisSorties =  this.element.find('input.not_a_favoris_sorties');
          var sommeNotFavorisEntrees = 0.0;
          var sommeNotFavorisSorties = 0.0;
          nofavorisEntrees.each(function(){
              if($(this).val()){
                sommeNotFavorisEntrees = parseFloat($(this).val()) + sommeNotFavorisEntrees;
              }
          });
          nofavorisSorties.each(function(){
            if($(this).val()){
              sommeNotFavorisSorties = parseFloat($(this).val()) + sommeNotFavorisSorties;
            }
          });
          var raccourcis_ouvrir_entrees = this.element.find('a.raccourcis_ouvrir_entrees');
          var raccourcis_ouvrir_sorties = this.element.find('a.raccourcis_ouvrir_sorties');
          if(sommeNotFavorisEntrees != 0 ){
            raccourcis_ouvrir_entrees.addClass('active');
          }else{
            raccourcis_ouvrir_entrees.removeClass('active');
          }
          if(sommeNotFavorisSorties != 0){
            raccourcis_ouvrir_sorties.addClass('active');
          }else{
            raccourcis_ouvrir_sorties.removeClass('active');
          }
        }

        this.focus = function () {
            if (this.isFocus()) {
                return;
            }

            if (debug) {
                console.log('colonne focus');
                console.log(this.element);
            }

            this.colonnes.unFocus();
            this.element.addClass('col_focus');
            this.element.removeClass('panel-success');
            this.element.addClass('panel-primary');
            this.colonnes.updateScroll();

            this.colonnes.event_focus(this);
        }

        this.focusChampDefault = function () {
            var tabIndex = this.element.attr('data-input-focus');
            var field = this.element.find('input[tabindex=' + tabIndex + ']');
            field.focus();
        }

        this.focusChamp = function (fieldId) {
          var field = this.element.find('input#' + fieldId);
          console.log(fieldId)
          field.focus();
        }

        this.unFocus = function () {
            this.element.removeClass('col_focus');
            if (this.element.hasClass('col_edited')) {
                this.element.addClass('panel-success');
            }
            this.element.removeClass('panel-primary');

            this.colonnes.event_unfocus();
        }

        this.isFocus = function () {

            return this.element.hasClass('col_focus');
        }

        this.isShow = function () {
            return this.element.is(':visible');
        }

        this.hide = function () {
            if (!this.isActive()) {
                this.unActive();
                if (this.getNext()) {
                    this.getNext().focus();
                    this.getNext().focusChampDefault();
                }
                this.element.hide();
                return true;
            }
            return false;
        }

        this.show = function () {
            this.unActive();
            return this.element.show();
        }

        this.reinit = function () {
            if (!this.isActive()) {
                return;
            }

            if (debug) {
                console.log('colonne reinit');
                console.log(this.element);
            }

            this.groupes.reinit();
            this.unActive();
        }

        this.valider = function (nextfocus,fieldFocus) {
            if (!this.isActive()) {
                var object = this;
                object.colonnes.event_valider(object, nextfocus,fieldFocus);
                return;
            }

            if (debug) {
                console.log('colonne valider');
                console.log(this.element);
            }

            this.calculer();
            this.saving();

            var object = this;

            var form = this.element.find('form');

            $.post(form.attr('action'), form.serializeArray(), function (data)
            {
                object.unSaving();

                if (!data.success) {
                    alert("Le formulaire n'a pas été sauvegardé car il comporte des erreurs");

                    return;
                }

                object.groupes.valider();
                object.unActive();

                object.colonnes.event_valider(object, nextfocus,fieldFocus);

            }, 'json');
        }

        this.saving = function () {
            this.element.addClass('col_envoi');
        }

        this.unSaving = function () {
            this.element.removeClass('col_envoi');
        }

        this.calculer = function () {
            if (debug) {
                console.log('colonne calculer');
                console.log(this.element);
            }
            this.groupes.calculer();
        }

        this.totalRecolte = function () {

            return this.groupes.totalRecolte();
        }

        this.totalDontRevendique = function () {
            return this.groupes.totalDontRevendique();
        }

        this.getNext = function () {
            var find = false;
            for (key in this.colonnes.colonnes) {
                if (find) {
                    return this.colonnes.colonnes[key];
                }
                if ((this.colonnes.colonnes[key] instanceof ColonneProduit && this.colonnes.colonnes[key].getHash() == this.getHash())
                        && this.colonnes.colonnes[key].isShow()) {
                    find = true;
                }
            }

            return null;
        }

        this.getPrevious = function () {
            var last = false;

            for (key in this.colonnes.colonnes) {

                if ((this.colonnes.colonnes[key] instanceof ColonneProduit && this.colonnes.colonnes[key].getHash() == this.getHash())
                        && this.colonnes.colonnes[key].isShow()) {
                    return last;
                }
                if (this.colonnes.colonnes[key] instanceof ColonneProduit) {
                    last = this.colonnes.colonnes[key];
                }
            }

            return null;
        }

        this.getHash = function () {

            return this.element.attr('data-hash');
        }

        this.getClass = function () {

            return 'ColonneProduit';
        }

        this._initBoutons = function () {
            var object = this;

            this.element.find('.col_btn button.btn_reinitialiser').click(function () {
                object.reinit();
                return false;
            });

            this.element.find('.col_btn button.btn_valider').click(function () {
                object.valider();
                return false;
            });
        }

        this._initClavier = function () {
            var object = this;

            this.element.keydown(function (e)
            {
                if (e.keyCode == 27)
                {
                    $(".modal").each(function () {
                        if ($(this).hasClass('in')) {
                            $("#" + $(this).attr('id')).modal('hide');
                        }
                    });
                    e.preventDefault();
                }
            });

        }
    }

    /**
     Class GroupesRows
     **/
    function GroupesRows(colonnes)
    {
        this.colonnes = colonnes;
        this.groupes_rows = new Array();

        this.init = function () {
            var object = this;
            colonnes.element_colonne_intitules.find('.groupe').each(function () {
                var groupe_id = $(this).attr('data-groupe-id');
                var groupes_row = new GroupesRow(object, groupe_id);
                groupes_row.init();
                object.groupes_rows[groupe_id] = groupes_row;
            });
        }

        this.close = function () {
            for (key in this.groupes_rows) {
                this.groupes_rows[key].close();
            }
        }

        this.update = function () {
            for (key in this.groupes_rows) {
                this.groupes_rows[key].update();
            }
            this._updateHauteur();
        }

        this._updateHauteur = function () {
            var colonnes = this.colonnes;
            if (debug) {
                console.log('hauteur egale li');
            }
            colonnes.element_colonne_intitules.find('.groupe').each(function () {
                var groupe_intitule_ul_li = $(this).find('ul li');
                var groupe_id = $(this).attr('data-groupe-id');
                var groupe_produits = colonnes.element_saisies.find('.groupe[data-groupe-id=' + groupe_id + ']');

                groupe_intitule_ul_li.each(function (i) {
                    var intitule_li = $(this);
                    var produits_li = groupe_produits.find('li:eq(' + i + ')');
                    intitule_li.add(produits_li).hauteurEgale();
                });

            });
        }
    }

    /**
     Class GroupesRow
     **/
    function GroupesRow(groupes_rows, groupe_id)
    {
        this.groupes_rows = groupes_rows;
        this.groupe_id = groupe_id;
        this.groupes_row = new Array();
        this.groupe_intitule = null;

        this.init = function () {
            this._getGroupeRows();
        }

        this._updatePosition = function () {
            if (this.isOpen()) {
                for (key in this.groupes_row) {
                    this.groupes_row[key].open();
                }

                return;
            }

            if (this.isClosed()) {
                for (key in this.groupes_row) {
                    this.groupes_row[key].close();
                }

                return;
            }
        }

        this.update = function () {
            this._getGroupeRows();
            this._updateHauteur();
            this._updatePosition();
        }

        this._getGroupeRows = function () {
            this.groupes_row = new Array();

            for (key_colonne in this.groupes_rows.colonnes.colonnes) {
                for (key_groupe in this.groupes_rows.colonnes.colonnes[key_colonne].groupes.groupes) {
                    groupe = this.groupes_rows.colonnes.colonnes[key_colonne].groupes.groupes[key_groupe];
                    if (groupe.groupe_id == this.groupe_id) {
                        if (groupe.colonne.getClass() == 'ColonneIntitule') {
                            this.groupe_intitule = groupe;
                        }
                        this.groupes_row.push(groupe);
                    }
                }
            }
        }

        this._updateHauteur = function () {
            var element = $('');
            if (debug) {
                console.log('hauteur egale p');
            }
            for (key in this.groupes_row) {
                var groupe_element = this.groupes_row[key].element;
                element.add(groupe_element.children('p'));
            }
            element.hauteurEgale();
        }

        this.isClosed = function () {

            return this.groupe_intitule.isClosed();
        }

        this.isBloque = function () {

            return this.groupe_intitule.isBloque();
        }

        this.isOpen = function () {

            return this.groupe_intitule.isOpen();
        }

        this.open = function () {
            if (this.isBloque()) {

                return;
            }

            if (debug) {
                console.log('groupes row open');
                console.log(this);
            }

            this.groupes_rows.close();
            for (key in this.groupes_row) {
                this.groupes_row[key].open();
            }
        }

        this.close = function () {
            if (this.isBloque()) {

                return;
            }

            if (debug) {
                console.log('groupes row open');
                console.log(this);
            }
            for (key in this.groupes_row) {
                this.groupes_row[key].close();
            }
        }
    }

    /**
     Class Groupes
     **/
    function Groupes(colonne)
    {
        this.colonne = colonne;
        this.groupes = new Array();

        this.init = function () {
            var elements = this.colonne.element.find('.groupe');

            var colonne = this.colonne;
            var object = this;
            var groupes = this.groupes;

            elements.each(function (i)
            {
                if (colonne.getClass() == "ColonneIntitule") {
                    var groupe = new GroupeIntitule(colonne, object, $(this));
                }

                if (colonne.getClass() == "ColonneProduit") {
                    var groupe = new GroupeProduit(colonne, object, $(this));
                }

                groupe.init();
                groupes[groupe.groupe_id] = groupe;

            });
        }

        this.valider = function () {
            for (key in this.groupes) {
                this.groupes[key].valider();
            }
        }

        this.reinit = function () {
            for (key in this.groupes) {
                this.groupes[key].reinit();
            }
        }

        this.enabled = function () {
            for (key in this.groupes) {
                this.groupes[key].enabled();
            }
        }

        this.disabled = function () {
            for (key in this.groupes) {
                this.groupes[key].disabled();
            }
        }

        this.calculer = function () {
            for (key in this.groupes) {
                this.groupes[key].calculer();
            }
        }

        this.totalRecolte = function () {
            var somme = 0;
            for (key in this.groupes) {
                somme += this.groupes[key].totalRecolte();
            }

            return somme;
        }

        this.totalDontRevendique = function () {
            var sommeR = 0;
            for (key in this.groupes) {
                sommeR += this.groupes[key].totalDontRevendique();
            }

            return sommeR;
        }
    }

    /**
     Class GroupeIntitule
     **/
    function GroupeIntitule(colonne, groupes, element)
    {
        this.colonne = colonne;
        this.groupes = groupes;
        this.element = element;
        this.element_titre = this.element.children('p');
        ;
        this.groupe_id = this.element.attr('data-groupe-id');

        this.init = function () {
            if (debug) {
                console.log('init groupe intitule');
                console.log(this.element);
            }

            this._init();
        }

        this._init = function () {
            var object = this;

            this.element_titre.click(function () {
                var groupes_row = object.getGroupesRow();
                if (groupes_row.isOpen()) {
                    groupes_row.close();
                } else {
                    groupes_row.open();
                }
            });
        }

        this.getGroupesRow = function () {

            return this.colonne.colonnes.groupes_rows.groupes_rows[this.groupe_id];
        }

        this.isOpen = function () {

            return this.element.hasClass('groupe_ouvert');
        }

        this.isClosed = function () {

            return !this.isOpen();
        }

        this.isBloque = function () {

            return this.element.hasClass('groupe_bloque');
        }

        this.open = function () {
            this.element.addClass('groupe_ouvert');
            this.element.children('ul').slideDown();
        }

        this.close = function () {
            this.element.removeClass('groupe_ouvert');
            this.element.children('ul').slideUp();
        }
    }

    /**
     Class GroupeProduit
     **/
    function GroupeProduit(colonne, groupes, element)
    {
        this.colonne = colonne;
        this.groupes = groupes;
        this.element = element;
        this.groupe_id = this.element.attr('data-groupe-id');
        this.champs = new Champs(this.colonne, this);

        this.init = function () {
            if (debug) {
                console.log('init groupe produit');
                console.log(this.element);
            }
            this.champs.init();
        }

        this.getPrecedent = function () {
            if (this.isFirst()) {

                return false;
            }

            return this.groupes.groupes[this.groupe_id - 1];
        }

        this.getSuivant = function () {
            if (this.isLast()) {

                return false;
            }

            return this.groupes.groupes[this.groupe_id + 1];
        }

        this.isFirst = function () {

            return this.groupe_id == 1;
        }

        this.isLast = function () {

            return this.groupes.groupes.length == this.groupe_id;
        }

        this.reinit = function () {
            this.champs.reinit();
        }

        this.valider = function () {
            this.champs.valider();
        }

        this.calculer = function () {
            this.champs.calculer();
        }

        this.totalRecolte = function () {

            return this.champs.totalRecolte();
        }

        this.totalDontRevendique = function () {
            return this.champs.totalDontRevendique();
        }

        this.enabled = function () {
            this.champs.enabled();
        }

        this.disabled = function () {
            this.champs.disabled();
        }

        this.getGroupesRow = function () {

            return this.colonne.colonnes.groupes_rows.groupes_rows[this.groupe_id];
        }

        this.open = function () {
            this.element.children('ul').slideDown();
        }

        this.close = function () {
            this.element.children('ul').slideUp();
        }
    }

    /**
     Class Champs
     **/
    function Champs(colonne, groupe)
    {
        this.colonne = colonne;
        this.groupe = groupe;
        this.champs = new Array();

        this.init = function () {
            var elements = this.groupe.element.find('input:text, select');

            var colonne = this.colonne;
            var object = this;
            var champs = this.champs;

            elements.each(function (i)
            {
                var champ = new Champ(colonne, object, $(this));
                champ.init();
                champs.push(champ);

            });
        }

        this.valider = function () {
            for (key in this.champs) {
                this.champs[key].valider();
            }
        }

        this.reinit = function () {
            for (key in this.champs) {
                this.champs[key].reinit();
            }
        }

        this.enabled = function () {
            for (key in this.champs) {
                this.champs[key].enabled();
            }
        }

        this.disabled = function () {
            for (key in this.champs) {
                this.champs[key].disabled();
            }
        }

        this.calculer = function () {
            for (key in this.champs) {
                this.champs[key].calculer();
            }
        }

        this.somme = function () {
            var somme = 0;
            for (key in this.champs) {
                somme += this.champs[key].somme();
            }

            return somme;
        }

        this.sommeEntreesRevendique = function () {
            var somme = 0;
            for (key in this.champs) {
                if (this.champs[key].isRevendiqueEntree()) {
                    somme += this.champs[key].somme();
                }
            }

            return somme;
        }

        this.sommeSortiesRevendique = function () {
            var somme = 0;
            for (key in this.champs) {
                if (this.champs[key].isRevendiqueSortie()) {
                    somme += this.champs[key].somme();
                }
            }

            return somme;
        }

        this.sommeEntreesRecolte = function () {
            var somme = 0;
            for (key in this.champs) {
                if (this.champs[key].isRecolteEntree()) {
                    somme += this.champs[key].somme();
                }
            }

            return somme;
        }

        this.sommeSortiesRecolte = function () {
            var somme = 0;
            for (key in this.champs) {
                if (this.champs[key].isRecolteSortie()) {
                    somme += this.champs[key].somme();
                }
            }

            return somme;
        }

        this.totalRecolte = function () {
            var somme = 0;
            for (key in this.champs) {
                somme += this.champs[key].totalRecolte();
            }

            return somme;
        }

        this.totalDontRevendique = function () {
            var sommeR = 0;
            for (key in this.champs) {
                sommeR += this.champs[key].totalDontRevendique();
            }
            return sommeR;
        }

        this.getFirst = function () {

            return this.champs[0];
        }

        this.getLast = function () {

            return this.champs[this.champs.length - 1];
        }
    }

    /**
     Class Champ
     **/
    function Champ(colonne, champs, element)
    {

        this.colonne = colonne;
        this.champs = champs;
        this.element = element;

        this.init = function () {

            if (debug) {
                console.log('init champ');
                console.log(this.element);
            }

            this._init();
            this._initText();
            this._initSelect();
            this._initNum();
        }

        this.reinit = function () {
            this._reinitText();
            this._reinitSelect();
        }

        this.valider = function () {
            if (parseFloat(this.element.val()) != parseFloat(this.element.attr('data-val-defaut'))) {
                this.colonne.colonnes.event_valider_champ_modification(this);
            }

            this.element.attr('data-val-defaut', this.element.val());
        }

        this.enabled = function () {
            this.element.removeAttr('disabled');
        }

        this.disabled = function () {
            this.element.attr('disabled', 'disabled');
        }

        this.isNum = function () {

            return this.element.is('input.input-float');
        }



        this.isSommeDetail = function () {

            return this.element.hasClass('somme_detail');
        }

        this.isRecolteEntree = function () {

            return this.element.hasClass('recolte_entree');
        }

        this.isRecolteSortie = function () {

            return this.element.hasClass('recolte_sortie');
        }

        this.isRevendiqueEntree = function () {

            return this.element.hasClass('revendique_entree');
        }

        this.isRevendiqueSortie = function () {

            return this.element.hasClass('revendique_sortie');
        }

        this.isTotalDebut = function () {

            return this.element.hasClass('somme_stock_debut');
        }

        this.isTotalDebutDontRevendique = function () {

            return this.element.hasClass('somme_stock_debut_dont_revendique');
        }

        this.isTotalFin = function () {

            return this.element.hasClass('somme_stock_fin');
        }

        this.isTotalFinDontRevendique = function () {

            return this.element.hasClass('somme_stock_fin_dont_revendique');
        }

        this.isSommeEntreesRecolte = function () {

            return this.element.hasClass('somme_entrees_recolte');
        }

        this.isSommeSortiesRecolte = function () {

            return this.element.hasClass('somme_sorties_recolte');
        }


        this.isSommeEntreesRevendiques = function () {

            return this.element.hasClass('somme_entrees_revendique');
        }

        this.isSommeSortiesRevendiques = function () {

            return this.element.hasClass('somme_sorties_revendique');
        }

        this.getVal = function () {
            if (!this.isNum()) {

                return 0;
            }

            valeur = 0;

            if (this.element.val() != '') {
                valeur = parseFloat(this.element.val());
            }

            return valeur;
        }

        this.setVal = function (value) {
            this.element.val(value);
            this.element.change();
        }

        this.calculer = function () {

            if (this.isSommeEntreesRevendiques()) {
                this.setVal(this.champs.sommeEntreesRevendique());

                return;
            }

            if (this.isSommeSortiesRevendiques()) {
                this.setVal(this.champs.sommeSortiesRevendique());

                return;
            }

            if (this.isSommeEntreesRecolte()) {
                this.setVal(this.champs.sommeEntreesRecolte());

                return;
            }

            if (this.isSommeSortiesRecolte()) {
                this.setVal(this.champs.sommeSortiesRecolte());

                return;
            }


            if (this.isTotalFin()) {
                var val_before = this.getVal();
                this.setVal(this.colonne.totalRecolte());

                if (this.isSommeDetail() && val_before != this.getVal()) {
                    this.champs.calculer();
                }
                this.colonne.notifyNotFavoris();
                return;
            }

            if (this.isTotalFinDontRevendique()) {
                var val_before = this.getVal();
                this.setVal(this.colonne.totalDontRevendique());

                if (val_before != this.getVal()) {
                    this.champs.calculer();
                }

                return;
            }

            return;
        }

        this.somme = function () {
            if (this.isSommeDetail()) {

                return this.getVal();
            }

            return 0;
        }

        this.totalRecolte = function () {
            if (this.isTotalDebut()) {

                return this.getVal();
            }

            if (this.isSommeEntreesRecolte()) {

                return this.getVal();
            }

            if (this.isSommeSortiesRecolte()) {

                return -this.getVal();
            }

            return 0;
        }

        this.totalDontRevendique = function () {

            if (this.isTotalDebutDontRevendique()) {
                return this.getVal();
            }

            if (this.isSommeEntreesRevendiques()) {

                return this.getVal();
            }

            if (this.isSommeSortiesRevendiques()) {
                return -this.getVal();
            }

            return 0;
        }

        this.isFirst = function () {

            return this == this.champs.getFirst()
        }

        this._init = function () {
            var colonne = this.colonne;
            var object = this;
            var groupe = this.champs.groupe;

            this.element.focus(function ()
            {
                var groupes_row = groupe.getGroupesRow();
                colonne.focus();
                if (groupes_row.isClosed()) {
                    groupes_row.open();
                }
            });

            this.element.keydown(function (e)
            {
                if (e.keyCode == 9 && e.shiftKey)
                {

                    if (!object.isFirst() || groupe.isFirst()) {

                        return true;
                    }

                    $(this).blur();
                    groupe.getPrecedent().champs.getLast().element.focus();
                    e.preventDefault();
                }
            });
        }

        this._initSelect = function () {

            if (!this.element.is('select'))
            {
                return;
            }

            this.element.blur(function ()
            {
                if (this.colonnes.hasActive()) {

                    return;
                }

                var val = this.champs.val();

                if (val != val_default) {
                    colonne.active();
                }
            });

            this.element.change(function ()
            {
                if (this.colonnes.hasActive()) {

                    return;
                }

                colonne.active();
            });
        }

        this._initText = function () {

            if (!this.element.is('input:text'))
            {
                return;
            }

            if (this.element.attr('readonly'))
            {
                return;
            }

            this.element.click(function (e)
            {
                $(this).select();
                e.preventDefault();
            });
        }

        this._initNum = function () {

            if (!this.isNum()) {

                return;
            }

            var colonne = this.colonne;

            this.element.on('keydown', function (e) {
                if(e.key == "Tab") {
                    return;
                }
                if(e.key == "Escape") {
                    return;
                }
                colonne.active();
            });

            this.element.on('blur', function () {
                colonne.calculer();
            });
        }

        this._reinitText = function () {
            if (!this.element.is('input:text'))
            {
                return;
            }

            this.setVal(this.element.attr('data-val-defaut'));
        }

        this._reinitSelect = function () {
            if (!this.element.is('select'))
            {
                return;
            }

            this.element.children().removeAttr('selected');
            this.element.children('[value=' + this.champ.attr('data-val-defaut') + ']').attr('selected', 'selected');
        }
    }

}(jQuery, window));
