( function($){
    setTimeout(function(){
        var ctrSel = {
            controlPanel: '.rb-tax-images-control',
            addButton:  '.rb-tax-add',
            removeButton: '.rb-remove',
            imagesHolder: '.rb-tax-images',
            image: '.rb-tax-image',
            imgBoxesHolder: '.rb-tax-images-boxes',
            controlValueInput: '.rb-tax-value',
            unique: 'data-unique',
        };

        $(window).resize(function(){
            $('.rb-collapsible-title + .rb-collapsible-body').finish();
        });

        $(document).ready(function(){
            //Set sortable
            $(ctrSel.controlPanel + ' ' + ctrSel.imgBoxesHolder).sortable({
                forcePlaceholderSize: true,
                update: function( event, ui ){
                    //If the sorting stopped and the order changes, update value
                    var $controlPanel = getControlPanel(ui.item);
                    updateControlValue($controlPanel);
                },
            });
        });

        $(document).on('click', ctrSel.controlPanel + ' ' + ctrSel.addButton, function(){
            var $controlPanel = getControlPanel($(this));

            var rbMediaGalleryUploader = wp.media.frames.file_frame = wp.media({
                title: 'Add images',
                button: {
                    text: 'Add images',
                },
                multiple: 'add',
                type: 'image',
            });

            rbMediaGalleryUploader.off("select");
            rbMediaGalleryUploader.on('select', function() {
                var imagesArr = rbMediaGalleryUploader.state().get('selection').models;
                console.log(imagesArr);
                if(!controlCanRepeatAttachments($controlPanel))
                    clearImagesHolder($controlPanel);
                imagesArr.forEach( function( image, index ){
                    addImage($controlPanel, image);
                    //If last, add ids to set of values
                    if(index == imagesArr.length - 1){
                        updateControlValue($controlPanel);
                    }
                });
            });

            rbMediaGalleryUploader.off("open");
            rbMediaGalleryUploader.on('open',function() {
                var selection = rbMediaGalleryUploader.state().get('selection');
                var ids = getCurrentImagesIds($controlPanel);
                console.log(ids);
                if(ids && !controlCanRepeatAttachments($controlPanel)){
                    ids.forEach(function(id) {
                        attachment = wp.media.attachment(id);
                        attachment.fetch();
                        selection.add( attachment ? [ attachment ] : [] );
                    });
                }
            });

            rbMediaGalleryUploader.open();
            console.log(rbMediaGalleryUploader);
        });

        $(document).on('click', ctrSel.controlPanel + ' ' + ctrSel.removeButton, function(){
            var $controlPanel = getControlPanel($(this));
            console.log($controlPanel);
            removeImage($controlPanel, $(this).closest(ctrSel.image) );
        });
        // =========================================================================
        // AUX
        // =========================================================================
        function getImageInfo(media){
            var attr = media.attributes;
            console.log(attr);
            if(attr){
                var info = {
                    id: attr.id,
                    title: attr.title,
                    date: attr.date,
                    dateFormatted: attr.dateFormatted,
                    type: attr.type,
                    mime: attr.mime,
                    link: attr.link,
                    url: attr.url,
                    thumbnail: attr.url,
                    caption: attr.caption,
                    description: attr.description,
                }

                if (attr.sizes && attr.sizes.thumbnail)
                    info.thumbnail = attr.sizes.thumbnail.url;
                else if (attr.image)
                    info.thumbnail = attr.image.src;

                return info;
            }
            return null;
        }

        function getItemImageInfo($item){
            return JSON.parse( $item.attr('data-rb-media-info') );
        }

        function getImageItemId($item){
            return $item.attr('rel');
        }

        // =========================================================================
        // ELEMENTS GETTERS
        // =========================================================================
        function getControlPanel($panelChildElement){
            return $panelChildElement.closest(ctrSel.controlPanel);
        }

        function getAddItemButton($controlPanel){
            return $controlPanel.find(ctrSel.addButton);
        }

        function getImagesHolder($controlPanel){
            return $controlPanel.find(ctrSel.imagesHolder);
        }

        function getImagesBoxesHolder($controlPanel){
            return $controlPanel.find(ctrSel.imgBoxesHolder);
        }

        function getControlValueInput($controlPanel){
            console.log($controlPanel);
            return $controlPanel.find(ctrSel.controlValueInput);
        }

        function getValueFilter($controlPanel){
            var events = $controlPanel.data('events')
            if( events && events.filterValue )
                return events.filterValue[0].handler;
            return null;
        }
        // =========================================================================
        // METHODS
        // =========================================================================
        function addImage($controlPanel, image){
            //console.log(image);
            insertImageBox($controlPanel, image);
        }

        function removeImage($controlPanel, $imageBox){
            removeImageBox($imageBox);
            updateControlValue($controlPanel);
        }

        function controlCanRepeatAttachments($controlPanel){
            return !$controlPanel[0].hasAttribute(ctrSel.unique);
        }

        function getFilteredValue($controlPanel, value, items){
            var filter = getValueFilter($controlPanel);
            var result = value;
            if( filter )
                result = filter(value,items);
            return result;
        }

        function updateControlValue($controlPanel){
            console.log("Updating value");
            var $input = getControlValueInput($controlPanel);
            console.log($input);
            var items = $controlPanel.find(ctrSel.image);
            var value = "";
            console.log("Current Items:", items);
            items.each(function(index){
                if(index > 0)
                    value += ',';
                value += getImageItemId($(this));
            });
            if(!controlCanRepeatAttachments($controlPanel))
            console.log("New Value:", value);
            var data = {value: value, items: items};
            var value = getFilteredValue($controlPanel, value, items);
            return $input.val( value ).trigger('input');
        }

        function clearImagesHolder($controlPanel){
            $controlPanel.find(ctrSel.image).each(function(idx){
                $(this).remove();
            });
        }

        function getCurrentImagesIds($controlPanel){
            var ids = [];
            $controlPanel.find(ctrSel.image).each(function(){
                var id = $(this).attr('rel');
                if(id)
                    ids.push(id);
            });
            return ids;
        }
        // =========================================================================
        // GALLERY MARKUP
        // =========================================================================
        function insertImageBox($controlPanel, image){
            var $imgBoxesHolder = getImagesBoxesHolder($controlPanel);

            //Media info
            var info = getImageInfo(image);
            var stringInfo = JSON.stringify(info);
            var thumbnail = info.thumbnail ? info.thumbnail : info.url;
            //console.log(stringInfo);
            //New element in the gallery control
            var stringElement =
                '<div class="rb-tax-image rb-gallery-box" rel="'+ info.id +'" style="background-image: url('+ thumbnail +');" data-src="'+ thumbnail +'">'
                +    '<i class="fas fa-times rb-remove"></i>'
                +'</div>';

            //Insert before the add new element control
            $imgBoxesHolder.append( stringElement );

        }

        function removeImageBox($imageBox){
            $imageBox.remove();
        }
    }, 400);
})( jQuery );
