( function( $ ) {

	function onInputsChange( $input ){
		var $controlPanel = $input.closest('.customize-control-inputs-generator');
		var data_dinamic_label_id = $controlPanel.attr('data-dinamic-label-id');

		toggleInputDependencies( $input );
		updateValue( $controlPanel );
		if( $input.attr('name') == data_dinamic_label_id )
			updateLabel( $input );
	}

	function getInputValue( $input ){
		if ( $input.attr('type') == 'checkbox' )
			return $input.is(':checked');
		else
			return $input.val();
	}

	$(document).ready(function(){
		$(document).on('input', ".customize-control-multiple-inputs:not(.customize-control-inputs-generator) input.rb-sub-input", function(){
			updateValueSimple( $(this).closest('.customize-control-multiple-inputs') );
		});
		$(document).on('input', ".customize-control-inputs-generator .rb-sub-input", function(){
			onInputsChange( $(this) );
		});
		$(document).on('change', ".customize-control-inputs-generator .inputs-generator-inputs-holder input[type='checkbox'].rb-sub-input", function(){
			onInputsChange( $(this) );
		});
		$(document).on('click', '.inputs-generator-inputs-holder .remove-image-button i', function( event ){
			event.stopPropagation();
			emptyImageInput( $(this).closest('.inputs-generator-inputs-holder') );
		});
		setTimeout(function(){
			$(".customize-control-inputs-generator .inputs-generator-inputs-holder .rb-sub-input").each(function(){
				toggleInputDependencies( $(this) );
			});

			$(".customize-control-multiple-inputs.customize-control-inputs-generator").each(function(){
				var dinamicLabelID = $(this).attr("data-dinamic-label-id");

				if ( dinamicLabelID != '' ){
					$(this).find(".customizer-inputs-group").each(function(){
						console.log(dinamicLabelID);
						updateLabel( $(this).find('input[name="'+ dinamicLabelID +'"], textarea[name="'+ dinamicLabelID +'"], select[name="'+ dinamicLabelID +'"]') );
					})
				}
			});

			$(".customize-control-multiple-inputs.customize-control-inputs-generator .customizer-sortable-ul").sortable({
				update: function(event, ui) {
					var $item = ui.item;
					updateValue( $item.closest(".customize-control-multiple-inputs") );
				},
				handle: ".draggable-ball",
				stop: function( event, ui ){
					var $item = ui.item;
					var $controlPanel = $item.closest(".customize-control-multiple-inputs");
					var $trashCan = $controlPanel.find('.delete-item-on-drop');
					setTimeout(function(){
						if ($trashCan.is(':hover')) {
							deleteItem( $item );
						}
						$trashCan.removeClass("trashcan-activated");
					}, 1);
				},
				start: function( event, ui ){
					var $item = ui.item;
					var $controlPanel = $item.closest(".customize-control-multiple-inputs");
					var $trashCan = $controlPanel.find('.delete-item-on-drop');
					$trashCan.addClass("trashcan-activated");
				},
			});
		}, 1)
		$(document).on("click", ".customize-control-multiple-inputs.customize-control-inputs-generator .add-new-li", function(){
			addNewInputs( $(this).closest(".customize-control-multiple-inputs") );
		});
		$(document).on('click', '.input-wp-media-image-holder', function(e) {
			e.stopPropagation();
			e.preventDefault();
			var $button = $(this);
			$button.attr('media-open');
			console.log( $(this));
			var $controlPanel = $(this).closest(".customize-control-multiple-inputs");
			var $input_field = $(this).find("input");
			var $image_holder = $(this).find(".input-image-src");
			var custom_uploader = wp.media.frames.file_frame = wp.media({
				title: 'Add Image',
				button: {
					text: 'Add Image',
				},
				multiple: false
			});
			custom_uploader.on('select', function() {
				var attachment = custom_uploader.state().get('selection').first().toJSON();
				//console.log(attachment.url);
				$input_field.val(attachment.url)
				updateValue( $controlPanel );
				$image_holder.attr('src', attachment.url );
				$button.removeAttr('media-open');
			});
			custom_uploader.open();
		});

	})

	function updateValue( $controlPanel ){
		if ( $controlPanel.hasClass('single-inputs-group-control') ){
			updateValueSimple( $controlPanel );
		}
		else if ( $controlPanel.hasClass('single-input-generator-control') ){
			updateValueSingleGenerator( $controlPanel );
		}
		else if ( $controlPanel.hasClass('single-input-control') ){
			updateValueSingleInput( $controlPanel );
		}
		else
			updateValueGenerator( $controlPanel );
	}

	function updateValueSimple( $controlPanel ){
		//console.log($controlPanel);
		var $inputsGroup = $controlPanel.find(".customizer-inputs-group");
		var $inputs = $inputsGroup.find('.rb-sub-input');
		var $valueInput = $controlPanel.find('input[type="hidden"].control-value' );
		var finalValue = {};

		$inputs.each(function(){
			finalValue[$(this).attr('name')] = getInputValue( $(this) );
		});

		console.log(finalValue);
		$valueInput.val( JSON.stringify(finalValue) ).trigger( 'change' );
	}

	function updateValueSingleInput( $controlPanel ){
		//console.log($controlPanel);
		var $inputGroup = $controlPanel.find(".customizer-inputs-group").first();
		var $input = $inputGroup.find('.rb-sub-input').first();
		var $valueInput = $controlPanel.find('input[type="hidden"].control-value' );
		var finalValue = getInputValue( $input );

		//console.log(finalValue);
		$valueInput.val( finalValue ).trigger( 'change' );
	}

	function updateValueSingleGenerator( $controlPanel ){
		//console.log($controlPanel);
		var $inputGroups = $controlPanel.find(".customizer-sortable-ul > li");
		var $valueInput = $controlPanel.find('input[type="hidden"].control-value' );
		var finalValue = {};
		var counter = 0;

		$inputGroups.each(function( index ){
			var input =  $(this).find(".rb-sub-input").first();
			finalValue['input_' + counter] = getInputValue( input );
			counter = parseInt(counter) + 1;
		});

		//console.log(finalValue);
		$valueInput.val( JSON.stringify(finalValue) ).trigger( 'change' );
	}

	function updateValueGenerator( $controlPanel ){
		//console.log($controlPanel);
		var $inputGroups = $controlPanel.find(".customizer-sortable-ul > li");
		var $valueInput = $controlPanel.find('input[type="hidden"].control-value' );
		var finalValue = {};

		$inputGroups.each(function( index ){
			var currentIndex = index;
			finalValue[index] = {};
			var $inputs = $(this).find(".rb-sub-input");
			$inputs.each(function(){
				finalValue[currentIndex][$(this).attr('name')] = getInputValue( $(this) );
			});
		});

		//console.log(finalValue);
		$valueInput.val( JSON.stringify(finalValue) ).trigger( 'change' );
	}

	function addNewInputs( $controlPanel ){
		var newli = $controlPanel.attr("data-base-inputs");
		var $ul = $controlPanel.find(".customizer-sortable-ul");
		$(newli).appendTo($ul).find('input, select, textarea').each(function(){
			toggleInputDependencies( $(this) );
		});
		$controlPanel.find('input[type="hidden"].control-value').trigger("change");
	};

	function deleteItem( $item ){
		var $controlPanel = $item.closest(".customize-control-multiple-inputs");
		$item.remove();
		updateValue( $controlPanel );
	}

	function updateLabel( $input ){
		var $inputsGroup = $input.closest(".customizer-inputs-group");
		var $inputsGroupTitle = $inputsGroup.find(".customize-control-title");

		var inputValue = getInputValue($input);
		if ( $input.is("select") )
			inputValue = $input.find('option:selected').text();

		console.log($input);
		if(inputValue)
			$inputsGroupTitle.text(inputValue)
		else
			$inputsGroupTitle.text($inputsGroup.attr('name'));
	}

	function findDependency(dependencies, id){
		var result = -1;
		console.log(dependencies);
		if(dependencies){
			var arrLength = dependencies.length;
			for(var i = 0; i < arrLength; i++){
				var dependency = dependencies[i];
				console.log(dependency);
				switch(dependency){
					case id: result = {
						index: i,
						key: '',
					}; break;
					case '!'+id: result = {
						index: i,
						key: '!',
					}; break;
				}
				if(result != -1)
					break;
			}
		}
		return result;
	}

	function toggleInputDependencies( $input ){
		var inputID = $input.attr('name');
		var $inputHolder = $input.closest(".inputs-generator-inputs-holder");
		var $inputsGroup = $inputHolder.closest(".customizer-inputs-group");
		var $inputs = $inputsGroup.find(".rb-sub-input");
		var inputVisibility = $inputHolder.attr('data-input-show');
		var inputValue = getInputValue($input);
		var dependencies = $inputHolder.attr('data-inputs-dependencies');
		var reverseDependencies = $inputHolder.attr('data-reverse-dependencies');
		var outOfLimitsDependencies = [];

		//console.log( "------- New Activation -------");
		//console.log( "Input: ", inputID );
		if ( dependencies ){
			var dependenciesArray = dependencies.split(',');
			dependenciesArray.map( inputID => inputID.trim() );
			//console.log( "Iterating..." );
			$inputs.each( function(){
				var currentInputID = $(this).attr('name');

				//if its not the same input
				if ( inputID != currentInputID ){
					var dependencyInfo = findDependency(dependenciesArray, currentInputID)
					console.log(dependencyInfo);
					//console.log("Current input:" , currentInputID);
					//console.log( outOfLimitsDependencies );
					if ( (dependencies == 1 || (dependencyInfo != -1)) && (outOfLimitsDependencies.indexOf(currentInputID) == -1 ) ){
						var $currentInputHolder = $(this).closest(".inputs-generator-inputs-holder");
						var $currentInput = $currentInputHolder.find("input, select, textarea");

						var currentInputDependencies = $currentInputHolder.attr('data-inputs-dependencies').split(',');
						currentInputDependencies.forEach(function( element ){
							if ( outOfLimitsDependencies.indexOf(element) )
								outOfLimitsDependencies.push(element);
						});

						//console.log("Visibility: ", inputVisibility);

						if ((inputVisibility == 'true') && (
							(((inputValue  && dependencyInfo.key != '!') || (!inputValue  && dependencyInfo.key == '!'))
							&& !reverseDependencies)
							||
							(((inputValue  && dependencyInfo.key == '!') || (!inputValue  && dependencyInfo.key != '!') )
							&& reverseDependencies )
						))
							$currentInputHolder.stop().slideDown().attr('data-input-show', true);
						else
							$currentInputHolder.stop().slideUp().attr('data-input-show', false);

						toggleInputDependencies( $currentInput  );
					}
				}
			});
		}
	}

	function emptyImageInput( $inputHolder ){
		var $controlPanel = $inputHolder.closest(".customize-control-multiple-inputs");
		var $image = $inputHolder.find('.input-image-src');
		var $input = $inputHolder.find('input');
		$image.attr('src','');
		$input.val('');
		console.log($controlPanel);
		updateValue( $controlPanel );
	}


} )( jQuery );
