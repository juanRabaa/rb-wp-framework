( function( $ ) {

	$(document).on('dblclick','.selection-with-image-input', function(event){
		console.log("Element with zoom available clicked");
		var $clickedElement = $(event.target);
		var $imageHolder = $clickedElement.siblings('.image-selection-image');
		var $imageToZoomIn = $imageHolder.children('img');
		var imageSrc = $imageToZoomIn.attr('src');
		
		if( imageSrc != "" ){
			if ( $("#zoomed-images-holder").length == 0 )
				$('body').prepend('<div id="zoomed-images-holder" class="zoom-out-available"><span class="zoom-out-text">Click to zoom out</span><div><img/></div></div>');
			$("#zoomed-images-holder").css("height", "100vh");
			$("#zoomed-images-holder > div > img").attr("src", imageSrc);
		}
	});

	$(document).on('click',"#zoomed-images-holder", function(event){
		$("#zoomed-images-holder").css("height", "0");
	});	

} )( jQuery );