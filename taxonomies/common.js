( function($){
    // =========================================================================
    // IMAGE ZOOM
    // =========================================================================
    function getGalleryImages($gallery){
        return $gallery.children('[rb-zoom-src]');
    }

    function getImageSrc($el){
        var src = '';
        console.log($el.attr('rb-zoom-src'));
        if ( $el.attr('rb-zoom-src') )
            src = $el.attr('rb-zoom-src');
        else if ( $el.is('img') && $el.attr('src') )
            src = $el.attr('src');
        else if ( $el.css('background-image') )
            src = $el.css('background-image').replace(/.*\s?url\([\'\"]?/, '').replace(/[\'\"]?\).*/, '');
        return src;
    }

    $(document).on('click', '[rb-zoom-gallery]', function(){

    });

    function prepareZoomViewHtml(){
        var $zoomContainer = $('.rb-image-zoom-container');
        var $rbZoomed = $($.parseHTML( '<img class="rb-zoomed"/>' ));
        if( $zoomContainer.length == 0  ){
            $zoomContainer = $($.parseHTML( '<div class="rb-image-zoom-container"></div>' ));
            $zoomContainer.append($rbZoomed);
            $('body').append( $zoomContainer );
        }
        else if ($zoomContainer.find('.rb-zoomed').length == 0){
            $zoomContainer.append( $rbZoomed );
        }
        else{
            $rbZoomed = $zoomContainer.find('.rb-zoomed');
        }
        return $zoomContainer;
    }

    function openRbZoomView($img){
        var $zoomContainer = prepareZoomViewHtml();
        var $rbZoomed = $zoomContainer.find('.rb-zoomed');

        var isOpen = $zoomContainer.hasClass('open');
        var isAnimating = $zoomContainer.hasClass('animating');
        if(!isOpen && !isAnimating){
            $zoomContainer.addClass('animating');
            setTimeout(function(){
                var imageSrc = getImageSrc($img);
                console.log(imageSrc);
                if( imageSrc != '' ){
                    $rbZoomed.attr('src', imageSrc );
                    $zoomContainer.addClass('open');
                    $zoomContainer.animate({
                        'opacity':  1,
                    }, 200, function(){
                        $zoomContainer.removeClass('animating');
                    })
                    $zoomContainer.addClass('open');
                }
            }, 10);
        }
        return $zoomContainer;
    }

    var rbZoomGallery = {
        sources: [],
        index: 0,
        $container: null,
        getSrc: function(index){
            return this.sources[index];
        },
        changeSrc: function(src){
            this.$container.find('.rb-zoomed').attr('src', src);
        },
        goNext: function(){
            var length = this.sources.length;
            if( this.index == (length - 1) )
                this.index = 0;
            else
                this.index++;

            console.log(this.index);
            this.changeSrc(this.sources[this.index]);
        },
        goPrev: function(){
            var length = this.sources.length;
            if( this.index == 0 )
                this.index = length - 1;
            else
                this.index--;

            this.changeSrc(this.sources[this.index]);
        },
        initialize: function($zoomContainer, src, index){
            this.sources = src;
            this.index = index;
            console.log(index);
            this.$container = $zoomContainer;
            this.$container.attr('rb-zoom-gallery-container', src);
            this.$container.attr('rb-not-close-on-click', true);
            this.$container.append('<div class="rb-zoom-gallery-close">x</div>');
        },
    }

    $(document).on('click', '[rb-zoom-gallery-container]', function(event){
        var x = event.clientX;
        console.log(x, window.innerWidth);
        if(x > window.innerWidth/2)
            rbZoomGallery.goNext();
        else
            rbZoomGallery.goPrev();
    });
    $(document).on('click', '.rb-image-zoom-container[rb-zoom-gallery-container] .rb-zoom-gallery-close', function(event){
        event.stopPropagation();
        rbZoomGallery.$container.removeAttr('rb-not-close-on-click');
        closeRbZoomContainer(rbZoomGallery.$container);
    });

    function transformToGallery($img, $zoomContainer){
        var sources = [];
        var $srcHolder = $img.parent('[rb-zoom-gallery]');
        var imageIndex = $srcHolder.find('[rb-zoom-src]').index($img);
        if($srcHolder.length){
            $srcHolder.children('[rb-zoom-src]').each(function(){
                sources.push($(this).attr('rb-zoom-src'));
            });
            $zoomContainer.attr('rb-zoom-gallery-container', sources);
            rbZoomGallery.initialize($zoomContainer, sources, imageIndex);
        }
    }

    $(document).on('click', '.rb-image-zoom', function(){
        var $zoomContainer = openRbZoomView($(this));
        transformToGallery($(this), $zoomContainer);
    });

    $(document).on('click', '.rb-image-zoom-container', function(){
        closeRbZoomContainer($(this));
    });

    function closeRbZoomContainer($zoomContainer){
        if( !$zoomContainer.hasClass('animating') && !$zoomContainer.attr('rb-not-close-on-click')){
            $zoomContainer.addClass('animating');
            $zoomContainer.animate({
                'opacity':  0,
            }, 200, function(){
                $zoomContainer.removeClass('open');
                $zoomContainer.removeClass('animating');
            })
        }
    }

    // =========================================================================
    // CHECKBOX FORM TAX
    // =========================================================================
    $(document).on('change', '.rb-tax-field input[type = checkbox]', function(){
        var val = $(this).is(":checked");
        if(val){
            $(this).attr('checked', '');
            $(this).val(1).trigger('input');
        }
        else{
            $(this).removeAttr('checked');
            $(this).val('').trigger('input');
        }
    });

    // =========================================================================
    // COLLAPSIBLE
    // =========================================================================
    function closeCollapsible($title){
        var $body = $title.next('.rb-collapsible-body');
        if( $body.length != 0){
            $body.finish().animate({
                height: '0',
                paddingTop: '0',
                paddingBottom: '0',
            }, 400, function() {
                $title.removeClass('animating');
                $title.removeClass('open');
            });
        }
    }

    function openCollapsible($title, closeRelatives){
        var $body = $title.next('.rb-collapsible-body');
        var $collapsibleHolder = $title.parent('.rb-collapsible-holder');
        if( $body.length != 0){
            if(closeRelatives)
                closeRelativesCollapsibles($collapsibleHolder);
            $body.height('100%');
            var fullHeight = $body.height();
            $body.height('0');
            $body.finish().animate({
                height: fullHeight + 'px',
                paddingTop: '1rem',
                paddingBottom: '1rem',
            }, 400, function() {
                $title.removeClass('animating');
                $title.addClass('open');
                $body.height('auto');
            });
        }
    }

    function closeRelativesCollapsibles($collapsibleHolder){
        var $collapsiblesPanel = $collapsibleHolder.parent('[rb-collapsibles-accordion]');
        var $title = $collapsibleHolder.children('rb-collapsible-title');

        if ( $collapsiblesPanel.length != 0 ){
            var $collapsibles = $collapsiblesPanel.children('.rb-collapsible-holder');
            $title.attr('rb-data-collapsible-closing-relatives', true);
            $collapsibles.each(function(){
                var $curTitle = $(this).children('.rb-collapsible-title');
                if( !$curTitle.attr('rb-data-collapsible-closing-relatives') )
                    closeCollapsible($curTitle);
                else
                    $curTitle.removeAttr('rb-data-collapsible-closing-relatives');
            });
        }
    }

    function toggleCollapsible($title){
        var isAnimating = $title.hasClass('animating');
        if (!isAnimating){
            var isOpen = $title.hasClass('open');
            $title.addClass('animating');

            if(isOpen){
                closeCollapsible($title);
            }
            else{
                openCollapsible($title, true);
            }
        }
    }

    $(document).on('click', '.rb-collapsible-title', function(){
        toggleCollapsible($(this));
    });

    // =========================================================================
    // REPEATER
    // =========================================================================
    function getNextEmptyControlID($controlPanel){
        var finalID;
        var results = [];
        var $controls = $controlPanel.find('.rb-tax-repeater-field');
        $controls.each(function(){
            results[$(this).attr('rb-tax-repeater-id')] = true;
        });
        finalID = results.length;
        for(var i = 0; i < results.length; i++){
            if( !results[i] ){
                finalID = i;
                break;
            }
        }
        return finalID;
    }

    function getRepeaterNewOrder($controlPanel){
        var $controls = $controlPanel.find('.rb-tax-repeater-field');
        var newOrder = '';
        $controls.each(function(index){
            newOrder += $(this).attr('rb-tax-repeater-id');
            if(index != $controls.length-1)
                newOrder += ',';
        });
        return newOrder;
    }

    function updateRepeaterOrder($controlPanel){
        var $controlOrderInput = $controlPanel.find('.rb-tax-repeater-order')
        $controlOrderInput.val(getRepeaterNewOrder($controlPanel)).trigger('input');
    }

    $(document).on('click', '.rb-tax-repeated .rb-add-item-button', function(){
        var $controlPanel = $(this).closest('.rb-tax-repeated');
        var $controlsHolder = $controlPanel.find('.rb-tax-repeated-controls').first();
        var controlPlaceholder = $controlPanel.attr('rb-data-control-placeholder');
        var newControlHtml = controlPlaceholder.replace(/__rb_placeholder_replace/g, getNextEmptyControlID($controlPanel));
        var $newControl = $($.parseHTML( newControlHtml ));console.log($newControl);
        $controlsHolder.append(newControlHtml);
        updateRepeaterOrder($controlPanel);
    });

    $(document).on('click', '.rb-tax-repeated .rb-tax-delete-button', function(){
        var $controlPanel = $(this).closest('.rb-tax-repeated');
        var $controlHolder = $(this).closest('.rb-tax-repeater-field');
        $controlHolder.remove();
        updateRepeaterOrder($controlPanel);
    });


    $(document).ready(function(){
        //Set sortable
        $('.rb-tax-repeated-controls').sortable({
            forcePlaceholderSize: true,
            start: function(e, ui){
                ui.placeholder.height(ui.helper.outerHeight());
            },
            update: function( event, ui ){
                var $controlPanel = $(this).closest('.rb-tax-repeated');
                updateRepeaterOrder($controlPanel);
            },
        });
    });

    // =========================================================================
    // DINAMIC TITLE
    // =========================================================================
    function changeTaxCollapsibleTitle($controlHolder, value){
        var dinamicID = $controlHolder.attr('rb-data-dinamic-title');
        var $title = $controlHolder.children('.rb-collapsible-title').find('.rb-title').first();
        if($title.length){
            if( value != '' )
                $title.text(value);
            else
                $title.text($controlHolder.attr('rb-data-base-title'));
        }
    }

    function isDinamicInput($input){
        var $controlHolder = $input.closest('[rb-data-dinamic-title]');
        if ($controlHolder.length != 0)
            var dinamicID = $controlHolder.attr('rb-data-dinamic-title');
        return $input.attr('name') == dinamicID;
    }

    function findDinamicInput($controlHolder){
        var dinamicID = $controlHolder.attr('rb-data-dinamic-title');
        return $controlHolder.find('[name='+ dinamicID +']');
    }

    $(document).on('input change', '[rb-data-dinamic-title] *', function(){
        if(isDinamicInput($(this)))
            changeTaxCollapsibleTitle( $(this).closest('[rb-data-dinamic-title]'), $(this).val() );
    });

    $(document).ready(function(){
        $('[rb-data-dinamic-title]').each(function(){
            var $dinamicInput = findDinamicInput($(this));
            console.log($dinamicInput.val());
            if($dinamicInput.length != 0)
                changeTaxCollapsibleTitle( $(this), $dinamicInput.val() );
        });
    });
})( jQuery );
