(function($){
    //valueInputSelector: selector directly unde the rb-control, of the input that contains the value

    // =========================================================================
    // AUX
    // =========================================================================
    function getInputValue( $input ){
        var value = '';
        if( $input.attr('type') == 'checkbox' )
            value = $input.is(':checked');
        else
            value = $input.val();

        if( $input.attr('rb-json') )
            value = JSON.parse(value);

        if(typeof $input.attr('value-as-number') !== typeof undefined && $input.attr('value-as-number') !== false)
            value = parseInt(value);

        return value;
    }

    // =========================================================================
    // SINGLE
    // =========================================================================
    var singleType = {
        valueInputSelector: '[rb-control-value]',
        getValue: function($panel){
            return getInputValue(this.getValueInput($panel));
        },
        getValueInput: function($panel){
            return $panel.find(this.valueInputSelector).first();
        },
        isSingle: function($panel){ return $panel.hasClass('rb-form-control-single-field'); },
    };

    // =========================================================================
    // GROUP
    // =========================================================================
    var groupType = {
        valueInputSelector: '> .control-body > [rb-control-group-value]',
        getPanel: function($elem){ return $elem.closest('.rb-form-control-group-field'); },
        getImmediatePanel: function($childControl){ return $childControl.parent('.group-child-control').parent('.controls').parent('.control-body').parent('.rb-form-control-group-field'); },
        getValueInput: function($panel){ return $panel.find(this.valueInputSelector); },
        getChildrens: function($panel){ return $panel.find('> .control-body > .controls > .group-child-control'); },
        getChildrenControl: function($groupChildControl){ return $groupChildControl.children('.rb-form-control'); },
        getChildValue: function($groupChildControl){ return fieldsController.getControlValue( this.getChildrenControl($groupChildControl) ); },
        getValue: function($panel){ return this.getValueInput($panel).val(); },
        updateValue: function($panel){
            var $childrens = this.getChildrens($panel);
            var $valueInput = this.getValueInput($panel);
            var groupValue = {};
            //console.log(`Group update: ${$panel.attr('id')}`, $childrens);
            $childrens.each(function(){
                let $childControl = groupType.getChildrenControl($(this));
                //console.log($childControl);
                //if(groupType.isGroup($childControl))
                    //return;
                let childValue = groupType.getChildValue($(this));
                let childID = $(this).attr('data-id');
                groupValue[childID] = childValue;
            });
            $valueInput.val(JSON.stringify(groupValue)).trigger('input');
            //console.log('Group value: ', groupValue);
        },
        attachEvents: function(){
            //console.log('Groups events attached');
            $(document).on('input change', `
            .rb-form-control-group-field > .control-body > .controls > .group-child-control > .rb-form-control-single-field ${singleType.valueInputSelector},
            .rb-form-control-group-field > .control-body > .controls > .group-child-control > .rb-form-control-repeater-field ${repeaterType.valueInputSelector}`
            , function(){
                groupType.updateValue(groupType.getPanel($(this)));
            });

            $(document).on('input change', `.rb-form-control-group-field > .control-body > .controls > .group-child-control > .rb-form-control-group-field ${groupType.valueInputSelector}`
            , function(){
                let $controlPanel = groupType.getPanel( groupType.getPanel($(this)).parent() );
                groupType.updateValue($controlPanel);
            });
        },
        isGroup: function($panel){ return $panel.hasClass('rb-form-control-group-field'); },
        initialize: function(){
            this.attachEvents();
        },
    };

    // =========================================================================
    // REPEATER
    // =========================================================================
    var repeaterType = {
        valueInputSelector: '> .control-body > [rb-control-repeater-value]',
        itemSelector: '.repeater-item:not(.item-placeholder)',
        getPanel: function($elem){ return $elem.closest('.rb-form-control-repeater-field'); },
        getRepeaterContainer: function($panel){ return $panel.find('> .control-body > .repeater-container'); },
        getValue: function($panel){ return this.getValueInput($panel).val(); },
        getValueInput: function($panel){ return $panel.find(this.valueInputSelector); },
        getItem: function($elem){ return $elem.closest(this.itemSelector); },
        getItemsContainer: function($panel){ return $panel.find('> .control-body > .repeater-container > .rb-repeater-items'); },
        getItems: function($panel){ return $panel.find(`> .control-body > .repeater-container > .rb-repeater-items > ${this.itemSelector}`); },
        getItemsAmount: function($panel){ return this.getItems($panel).length; },
        getItemControl: function($item){ return $item.find('> .item-content > .rb-form-control'); },
        getItemValue: function($item){ return fieldsController.getControlValue( this.getItemControl($item) ); },
        getItemPlaceholder: function($panel){ return $panel.find(`> .control-body > .repeater-container > .repeater-empty-control > ${repeaterType.itemSelector}`); },
        getBaseTitle: function($panel){ return $panel.attr('data-base-title'); },
        getBaseTitleFor: function($panel, $item){
            let baseTitle = this.getBaseTitle($panel);
            return baseTitle.replace(/\(\$n\)/g, $item.index() + 1);
        },
        getEmptyItem: function($panel, index){
            let $emptyItem = this.getItemPlaceholder($panel).clone();
            var $tempDiv = $('<div>');
            $tempDiv.append($emptyItem).html(function(i, oldHTML) {
                return oldHTML.replace(/__\(\$RB_REPEATER_PLACEHOLDER\)/g, index);
            });
            $emptyItem = $tempDiv.children(repeaterType.itemSelector);
            return $emptyItem;
        },
        getTitleLink: function($panel){ return $panel.attr('data-title-link'); },
        updateItemTitle: function($panel, $item){
            let linkedFieldID = this.getTitleLink($panel);
            if(!linkedFieldID) return false;

            let $itemControl = this.getItemControl($item);

            if( this.itemIsSingle($item) )
                linkedFieldID = fieldsController.getID($panel) + `-${$item.index() + 1}`;
            else if( this.itemIsGroup($item) )
                linkedFieldID = fieldsController.getID($itemControl) + `-${linkedFieldID}`;

            let $linkedFieldControl = fieldsController.getPanelByID(linkedFieldID);
            if( !singleType.isSingle($linkedFieldControl) ) return false;

            let linkedValue = singleType.getValue($linkedFieldControl);
            let $itemTitle = $item.find('> .item-header > .item-title');

            if(linkedValue)
                $itemTitle.text(linkedValue);
            else
                $itemTitle.text( this.getBaseTitleFor($panel, $item) );
        },
        updateItemsTitles: function($panel){
            this.getItems($panel).each(function(index){
                repeaterType.updateItemTitle($panel, $(this));
            });
        },
        updateValue: function($panel){
            var $items = this.getItems($panel);
            var $valueInput = this.getValueInput($panel);
            var repeaterValue = [];
            $items.each(function(){
                //console.log('Item value', repeaterType.getItemValue($(this)));
                repeaterValue.push(repeaterType.getItemValue($(this)));
            });
            $valueInput.val(JSON.stringify(repeaterValue)).trigger('input');
            //console.log('Repeater value:', repeaterValue);
        },
        itemIsRepeater: function($item){ return repeaterType.isRepeater( repeaterType.getItemControl($item) ); },
        itemIsGroup: function($item){ return groupType.isGroup( repeaterType.getItemControl($item) ); },
        itemIsSingle: function($item){ return singleType.isSingle( repeaterType.getItemControl($item) ); },
        isRepeater: function($panel){ return $panel.hasClass('rb-form-control-repeater-field'); },
        isEmpty: function($panel){ return !this.getItemsAmount($panel); },
        updateStatus: function($panel){
            let $container = this.getRepeaterContainer($panel);
            if( this.isEmpty($panel) )
                $container.addClass('empty');
            else
                $container.removeClass('empty');
        },
        addNewItem: function($panel){
            var $itemsContainer = this.getItemsContainer($panel);
            var $item = this.getEmptyItem($panel, this.getItemsAmount($panel) + 1);
            $item.css('display', 'none');
            $item.appendTo($itemsContainer);
            $item.slideDown(200);
            this.updateStatus($panel);
            if(this.itemIsRepeater($item))//If item is repeater, attach sortable
                this.makeSortable( this.getItemControl($item) );
        },
        deleteItem: function($item){
            let $panel = this.getPanel($item);
            $item.addClass('item-placeholder');
            $item.slideUp(200, function(){ $item.remove(); });
            this.updateStatus($panel);
            this.updateValue($panel);
            this.updateItemsTitles($panel);
        },
        makeSortable: function($panel){
            this.getItemsContainer($panel).sortable({
                handle: '> .item-header',
                update: function( event, ui ){
                    let $panel = repeaterType.getPanel(ui.item);
                    repeaterType.updateValue($panel);
                    repeaterType.updateItemsTitles($panel);
                },
            });
        },
        attachEvents: function(){
            //Changes in item values when item is a single or a group
            $(document).on('input change', `.rb-form-control-repeater-field > .control-body > .repeater-container > .rb-repeater-items > ${repeaterType.itemSelector} > .item-content > .rb-form-control-single-field ${singleType.valueInputSelector},
            .rb-form-control-repeater-field > .control-body > .repeater-container > .rb-repeater-items > ${repeaterType.itemSelector} > .item-content > .rb-form-control-group-field ${groupType.valueInputSelector}`
            , function(){
                repeaterType.updateValue( repeaterType.getPanel($(this)) );
            });

            //Changes in item when it is a repeater
            $(document).on('input change', `.rb-form-control-repeater-field > .control-body > .repeater-container > .rb-repeater-items > ${repeaterType.itemSelector} > .item-content > .rb-form-control-repeater-field ${repeaterType.valueInputSelector}`
            , function(){
                let $controlPanel = repeaterType.getPanel( repeaterType.getPanel($(this)).parent() );
                repeaterType.updateValue( $controlPanel );
            });

            //Changes in item (single or group), when the items title is linked to changes in a field
            $(document).on('input change',
            `.rb-form-control-repeater-field[data-title-link] > .control-body > .repeater-container > .rb-repeater-items > ${repeaterType.itemSelector} > .item-content > .rb-form-control-single-field ${singleType.valueInputSelector},
            .rb-form-control-repeater-field[data-title-link] > .control-body > .repeater-container > .rb-repeater-items > ${repeaterType.itemSelector} > .item-content > .rb-form-control-group-field ${groupType.valueInputSelector}`
            , function(){
                let $controlPanel = repeaterType.getPanel($(this));
                let $item = repeaterType.getItem($(this));
                repeaterType.updateItemTitle($controlPanel, $item);
            });

            $(document).on('click', '.rb-form-control-repeater-field > .control-body > .repeater-container > .repeater-add-button > .add-button'
            , function(){
                repeaterType.addNewItem( repeaterType.getPanel($(this)) );
            });

            $(document).on('click', `.rb-form-control-repeater-field > .control-body > .repeater-container > .rb-repeater-items > ${repeaterType.itemSelector} > .item-header .delete-button`
            , function(e){
                e.preventDefault();
                e.stopPropagation();
                repeaterType.deleteItem( $(this).closest(repeaterType.itemSelector) );
            });

            $(document).ready(function(){
                setTimeout(function(){
                    $('.rb-form-control-repeater-field').each(function(){
                        repeaterType.makeSortable($(this));
                    });

                    $('.rb-form-control-repeater-field').each(function(){
                        repeaterType.updateItemsTitles($(this));
                    });
                }, 0);
            });

        },
        initialize: function(){
            this.attachEvents();
        },
    };

    // =========================================================================
    // GENERAL
    // =========================================================================
    var fieldsController = {
        getPanelType: function($panel){
            if(singleType.isSingle($panel))
                return singleType;
            if(groupType.isGroup($panel))
                return groupType;
            if(repeaterType.isRepeater($panel))
                return repeaterType;
            return null;
        },
        getControlValue: function($panel){
            let panelType = this.getPanelType($panel);
            //console.log('Getting value from control: ', $panel, panelType);
            return panelType ? panelType.getValue($panel) : '';
        },
        getPanelByID: function(id){ return $(`#rb-field-control-${id}`); },
        getID: function($panel){ return $panel.attr('data-id'); },
        getDependencies: function($panel){ return $panel.attr('data-dependencies') ? JSON.parse($panel.attr('data-dependencies')) : null; },
        hasGlobalDependencies: function($panel){ return typeof $panel.attr('data-global-dependencies') != typeof undefined; },
        checkFieldDependencies: function($panel, processedFields){
            processedFields = typeof processedFields === typeof undefined ? {} : processedFields;
            var fieldID = this.getID($panel);
            var dependencies = this.getDependencies($panel);
            var controlValue = this.getControlValue($panel);
            var hiddenByDependencies = false;
            var $parentGroup = groupType.getImmediatePanel($panel);
            var hasGlobalDependencies = this.hasGlobalDependencies($panel);
            var idPrefix = !hasGlobalDependencies && $parentGroup.length ? this.getID($parentGroup) + '-' : '';
            processedFields[fieldID] = false;

            if(dependencies){//Has dependencies

                for(let dependencyID of dependencies[1]){
                    //Check for the not operator in the dependencyID
                    let notOperator = false;
                    if(dependencyID.charAt(0) == '!'){
                        notOperator = true;
                        dependencyID = dependencyID.slice(1);
                    }
                    dependencyID = idPrefix + dependencyID;

                    let $dependencyField = this.getPanelByID(dependencyID);
                    //If it has been already processed, take the status from the processedFields, if not, run checkFieldDependencies on $dependencyField
                    let dependencyStatus = processedFields[dependencyID] != null ? processedFields[dependencyID] : this.checkFieldDependencies($dependencyField, processedFields);
                    dependencyStatus = notOperator ? !dependencyStatus : dependencyStatus;

                    if(dependencies[0] == 'AND' && !dependencyStatus){
                        hiddenByDependencies = true;
                        break;
                    }
                    else if(dependencies[0] == 'OR' && dependencyStatus){
                        hiddenByDependencies = false;
                        break;
                    }
                }

                //Hide/show based on dependencies result
                if(hiddenByDependencies)
                    $panel.stop().slideUp();
                else
                    $panel.stop().slideDown();
            }

            //If it is hidden by its dependencies, or if the value is false, the status will be false
            processedFields[fieldID] = !hiddenByDependencies && !!controlValue;
            return processedFields[fieldID];
        },
        checkFieldsDependencies: function(){
            $('.rb-form-control[data-dependencies]').each(function(){
                fieldsController.checkFieldDependencies($(this));
            });
        },
        initialize: function(){
            groupType.initialize();
            repeaterType.initialize();

            $(document).ready(function(){
                setTimeout(function(){ fieldsController.checkFieldsDependencies(); }, 0);
            });

            $(document).on('change input', '[rb-control-value]', function(){
                fieldsController.checkFieldsDependencies();
            });
        },
    };
    fieldsController.initialize();

    // =========================================================================
    // CUSTOMIZER
    // =========================================================================
    var customizerController = {
        getPanel: function($elem){ return $elem.closest('.rb-customizer-control'); },
        getValueInput: function($panel){ return $panel.children('[rb-customizer-control-value]'); },
        updateValue: function($controlInput){
            this.getValueInput( this.getPanel($controlInput) ).val($controlInput.val()).trigger('input');
        },
        initialize: function(){
            $(document).on('input change', `.rb-customizer-control > .rb-form-control-single-field ${singleType.valueInputSelector},
            .rb-customizer-control > .rb-form-control-group-field ${groupType.valueInputSelector},
            .rb-customizer-control > .rb-form-control-repeater-field ${repeaterType.valueInputSelector}`
            , function(){
                customizerController.updateValue($(this));
            });
        }
    }
    customizerController.initialize();

})(jQuery);
