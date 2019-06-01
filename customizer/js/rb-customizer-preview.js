jQuery(function($){
    var settings = rbCustomizer.settings;
    var templateUrl = rbCustomizer.templateUrl;

    function updateSetting(setting){
        console.log("Update", setting.id, setting.frontEdition.currentValue);
        var config = {
            method: 'POST',
            url: templateUrl + '/wp-json/rb-customizer/v1/setting/update',
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

    for(let i = 0; i < settings.length; i++){
        let setting = settings[i];
        let selectiveRefresh = setting.selective_refresh;
        if(typeof selectiveRefresh.selector === 'string'){
            setting.frontEdition = {
                currentValue: '',
            }
            var $elements = $(selectiveRefresh.selector);

            // =================================================================
            // MAKE EDITABLE - SAVE EDIT
            // =================================================================
            $elements.attr('contenteditable', '');
            $elements.on('focus', function(){
                setting.frontEdition.currentValue = $(this).text();
                console.log('focus', setting);
            });
            $elements.on('blur', function(){
                let currentElementText = $(this).text();
                if( currentElementText != setting.frontEdition.currentValue ){
                    setting.frontEdition.currentValue = currentElementText;
                    updateSetting(setting);
                }
                console.log('blur', setting);
            });
        }
    }
})
