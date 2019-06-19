jQuery(function($){
    //Customizer settings
    var settings = rbCustomizer.settings;
    //WP Url
    var templateUrl = rbCustomizer.templateUrl;
    //Changes to save
    var stagedChanges = {};
    //Saving status
    var saving = false;
    //Edition images
    var editorImageElements = [];

    //Amount of changes staged to save
    function stagedChangesAmount(){
        return Object.keys(stagedChanges).length;
    }

    //Update save button markup
    function updateMarkup(){
        //console.log($('#rb-customizer-save-container'));
        if(stagedChangesAmount())
            $('#rb-customizer-save-container').fadeIn();
        else
            $('#rb-customizer-save-container').fadeOut();

        if(saving)
            $('#rb-customizer-save-container').addClass('saving');
        else
            $('#rb-customizer-save-container').removeClass('saving');
    }

    function setSavingStatus(status){
        saving = status;
        updateMarkup();
    }

    //Saves a setting in stagedChanges for it to be updated later
    function stageChange(setting){
        stagedChanges[setting.id] = setting.frontEdition.currentValue;
        updateMarkup();
        console.log(stagedChanges);
    }

    //Saves all settings changed
    function saveStagedChanges(){
        setSavingStatus(true);
        updateSettings(stagedChanges)
        .done(function() {
            stagedChanges = {};
        })
        .fail(function() {
            console.log('error');
        })
        .always(function( msg ){
            setSavingStatus(false);
            console.log(msg);
        });
    }

    //REST call to update settings
    function updateSettings(settings){
        console.log("Updating settings", settings);
        var config = {
            method: 'POST',
            url: templateUrl + '/wp-json/rb-customizer/v1/settings/update',
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
            },
            data: {
                settings: settings
            },
        };
        return $.ajax(config);
    }

    //REST call to update a single setting
    function updateSetting(setting){
        console.log("Update", setting.id, setting.frontEdition.currentValue);
        var config = {
            method: 'POST',
            url: templateUrl + `/wp-json/rb-customizer/v1/setting/${setting.id}update`,
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
            },
            data: {
                settingID: setting.id,
                value: setting.frontEdition.currentValue,
            },
        };
        $.ajax(config)
        .done(function( msg ) {
            console.log(msg);
        });
    }

    function getControl(control){
        var config = {
            method: 'GET',
            url: templateUrl + `/wp-json/rb-customizer/v1/control`,
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
            },
            data: control,
        };
        var request = $.ajax(config);
        request.always(function( controlHtml ) {
            console.log(controlHtml);
        });
        return request;
    }

    function addControlToPreview(control){
        getControl(control).success(function(controlHtml){
            var $control = $(controlHtml);
            $control.appendTo("#rb-customizer-controls");
        });
    }

    function moveEditionIconInside(iconInfo){
        iconInfo.$image.css({
            left: `auto`,
            top: `auto`,
        });
        iconInfo.$image.prependTo(iconInfo.$element);
    }

    function moveEditionIconOut(iconInfo){
        iconInfo.$image.css({
            left: `auto`,
            top: `auto`,
        });
        let iconWidth = iconInfo.$image.width();
        let iconHeight = iconInfo.$image.height();
        let iconLeft = iconInfo.$image.offset().left;
        let iconTop = iconInfo.$image.offset().top;
        let elementLeft = iconInfo.$element.offset().left;
        let elementTop = iconInfo.$element.offset().top;
        iconInfo.$image.css({
            left: `${elementLeft - 32}px`,
            top: `${elementTop - 11}px`,
        });
        iconInfo.$image.appendTo("#rb-customizer-edition-icons");
    }

    function positionateEditionIcons(iconsInformation){
        iconsInformation.forEach((iconInfo) => moveEditionIconInside(iconInfo));
    }

    $(document).ready(function(){
        console.log(rbCustomizer);
        addControlToPreview(rbCustomizer.controls[0]);
        for(let i = 0; i < settings.length; i++){
            let setting = settings[i];
            let selectiveRefresh = setting.selective_refresh;
            if(typeof selectiveRefresh.selector === 'string' && !selectiveRefresh.has_user_callback){
                setting.frontEdition = {
                    currentValue: '',
                    editionIcons: [],
                }
                setting.$elements = $(selectiveRefresh.selector);

                // =================================================================
                // MAKE EDITABLE - SAVE EDIT
                // =================================================================
                setting.$elements.attr('contenteditable', '');
                setting.$elements.addClass('rb-customizer-editable-element');
                setting.$elements.on('click', function(event){
                    if(!event.ctrlKey)
                        return;
                    event.preventDefault();
                    event.stopPropagation();
                });
                // =============================================================
                // START EDITION
                // =============================================================
                setting.$elements.on('focus', function(){
                    //Move edition icon outside, so it wont be taken as part of the value
                    setting.frontEdition.editionIcons.forEach(function(iconInfo){
                        moveEditionIconOut(iconInfo);
                    });
                    //Set current value
                    setting.frontEdition.currentValue = $(this).html();
                    //console.log('focus', setting);
                });
                // =============================================================
                // EDITING
                // =============================================================
                setting.$elements.on('input', function(){
                    let currentElementText = $(this).html();
                    //Change html in case there are more elements than the one edited
                    setting.$elements.not(this).html(currentElementText);
                    //console.log('input', setting);
                });
                // =============================================================
                // STAGE CHANGES
                // =============================================================
                setting.$elements.on('blur', function(){
                    let currentElementText = $(this).html();
                    //Move icon back inside the elements
                    setting.frontEdition.editionIcons.forEach(function(iconInfo){
                        moveEditionIconInside(iconInfo);
                    });
                    //Check for difference between old value and new
                    //console.log(currentElementText,setting.frontEdition.currentValue);
                    if( currentElementText != setting.frontEdition.currentValue ){
                        setting.frontEdition.currentValue = currentElementText;
                        stageChange(setting);
                        //updateSetting(setting);
                    }
                    //console.log('blur', setting);
                });
                // =============================================================================
                // EDITIONS ICONS SETUP
                // =============================================================================
                if($('#rb-customizer-edition-icons').length){
                    setting.$elements.each(function(){
                        let iconInfo = {
                            $image: $(`<img data-index="${editorImageElements.length}" class="rb-customizer-edition-image" src="${rbCustomizer.assetsUrl}/img/edit--v1.png"/>`),
                            $element: $(this),
                        };
                        editorImageElements.push(iconInfo);
                        setting.frontEdition.editionIcons.push(iconInfo);
                        iconInfo.$image.appendTo("#rb-customizer-edition-icons");
                        //Link click in icon to element edition
                        iconInfo.$image.click(function(event){
                            event.stopPropagation();
                            event.preventDefault();
                            setTimeout(function() {
                                iconInfo.$element.focus();
                            }, 100);
                        });
                    });
                }
            }
            positionateEditionIcons(editorImageElements);
        }

        $('#rb-customizer-settings-save-button').click(function(){
            if(!saving)
                saveStagedChanges();
        });

    });

    // =========================================================================
    // BEFORE EXIT
    // =========================================================================
    window.onbeforeunload = function (e) {
        if(!stagedChangesAmount())
            return;
        var message = "All unsaved content will be lost. Continue?",
        e = e || window.event;
        // For IE and Firefox
        if (e) {
            e.returnValue = message;
        }
        // For Safari
        return message;
    };

    // =============================================================================
    // RESIZE
    // =============================================================================
    $(window).resize(function(){
        positionateEditionIcons(editorImageElements);
    });
})
