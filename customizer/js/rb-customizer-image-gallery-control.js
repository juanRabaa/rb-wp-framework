( function( $ ) {
	
	$(document).ready(function(){
		setTimeout(function(){			
			$(".customize-control-image-gallery .customizer-sortable-ul").sortable({
				update: function(event, ui) {
					var $item = ui.item;
					updateValue( $item.closest(".customize-control-image-gallery") );
				},
				handle: ".drag-image",
				start: function( event, ui ){
					var $item = ui.item;
					var imagesrc = $item.find('.gallery-image-holder').attr('data-image-src');
					var $placeholder = $item.siblings("li.ui-sortable-placeholder");
					$placeholder.css('background-image', 'url(' + imagesrc + ')');
					console.log($placeholder);
					console.log(imagesrc);
				},				
			});	
		}, 1)
		$(document).on('click', '.customize-control-image-gallery .customizer-add-new-button', function(e) {
			e.preventDefault();
			var $controlPanel = $(this).closest(".customize-control-image-gallery");
			var custom_uploader = wp.media.frames.file_frame = wp.media({
				title: 'Add Image',
				button: {
					text: 'Add Image',
				},
				multiple: 'add',
			});
			custom_uploader.on('select', function() {
				var imagesArr = custom_uploader.state().get('selection').models;
				imagesArr.forEach( function( image, index ){
					var update = false;
					if ( index == (imagesArr.length - 1) )
						update = true;
					addNewImage( $controlPanel, image.changed.url, update );
				});
			});
			custom_uploader.open();
		});
		$(document).on('click', '.customize-control-image-gallery .edit-image', function(e) {
			e.preventDefault();
			var $imageli = $(this).closest('li');
			var custom_uploader = wp.media.frames.file_frame = wp.media({
				title: 'Add Image',
				button: {
					text: 'Add Image',
				},
				multiple: false,
			});
			custom_uploader.on('select', function() {
				var newSrc = custom_uploader.state().get('selection').first().changed.url;
				changeImage ( $imageli, newSrc, true );
			});
			custom_uploader.open();
		});		
		$(document).on('click', '.customize-control-image-gallery .gallery-image-controls .remove-image', function(e) {
			removeImage( $(this).closest('li'), true);
		});		
	});	
	
	function updateValue( $controlPanel ){
		var finalValue = {};
		var currentIndex = 0;
		var $images = $controlPanel.find('.gallery-image-holder');
		var $valueInput = $controlPanel.find('input.control-value');
		
		$images.each( function(){
			finalValue[currentIndex] = $(this).attr('data-image-src');
			currentIndex++;
		});
		
		$valueInput.val(JSON.stringify(finalValue)).trigger('change');
		//console.log(finalValue);
	}
	
	function addNewImage( $controlPanel, src, update ){
		var $newli = $($.parseHTML($controlPanel.attr('data-gallery-base-li')));
		var $ul = $controlPanel.find('.customizer-sortable-ul');
		$newli.find('.gallery-image-holder').attr('data-image-src', src);
		$newli.find('.gallery-image-holder').css('background-image', 'url(' + src + ')');
		$ul.append($newli);
		if (update)
			updateValue( $controlPanel );		
	}
	
	function removeImage( $imageli, update ){
		var $controlPanel = $imageli.closest(".customize-control-image-gallery")
		$imageli.remove();
		if (update)
			updateValue( $controlPanel );
	}
	
	function changeImage ( $imageli, src, update ){
		var $imageHolder = $imageli.find('.gallery-image-holder');
		$imageHolder.attr('data-image-src', src);
		$imageHolder.css('background-image', 'url(' + src + ')');	
		if (update)
			updateValue( $imageli.closest(".customize-control-image-gallery") );		
	}
	
} )( jQuery );