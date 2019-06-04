jQuery(function($){
    console.log(rbCustomizer);
    //Customizer settings
    var settings = rbCustomizer.settings;
    //WP Url
    var templateUrl = rbCustomizer.templateUrl;
    //Changes to save
    var stagedChanges = {};
    //Saving status
    var saving = false;

    //Amount of changes staged to save
    function stagedChangesAmount(){
        return Object.keys(stagedChanges).length;
    }

    //Update save button markup
    function updateMarkup(){
        console.log($('#rb-customizer-save-container'));
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
            data: control,
        };
        $.ajax(config)
        .always(function( msg ) {
            console.log(msg);
        });
    }

    $(document).ready(function(){
        //getControl(rbCustomizer.controls[0]);
        for(let i = 0; i < settings.length; i++){
            let setting = settings[i];
            let selectiveRefresh = setting.selective_refresh;
            if(typeof selectiveRefresh.selector === 'string' && !selectiveRefresh.has_user_callback){
                setting.frontEdition = {
                    currentValue: '',
                }
                setting.$elements = $(selectiveRefresh.selector);

                // =================================================================
                // MAKE EDITABLE - SAVE EDIT
                // =================================================================
                setting.$elements.attr('contenteditable', '');
                setting.$elements.addClass('rb-customizer-editable-element');
                setting.$elements.on('focus', function(){
                    setting.frontEdition.currentValue = $(this).html();
                    console.log('focus', setting);
                });
                setting.$elements.on('blur', function(){
                    let currentElementText = $(this).html();
                    if( currentElementText != setting.frontEdition.currentValue ){
                        setting.frontEdition.currentValue = currentElementText;
                        stageChange(setting);
                        //updateSetting(setting);
                    }
                    console.log('blur', setting);
                });
            }
        }

        $('#rb-customizer-settings-save-button').click(function(){
            if(!saving)
                saveStagedChanges();
        });
    });

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
})
