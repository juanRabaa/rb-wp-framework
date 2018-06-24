( function( $ ) {

	function openListEditionPanel( $listEditionPanel ){
		$listEditionPanel.css("height", "calc(100vh - 4.5rem)");
		$listEditionPanel.css("padding-bottom", "10rem");
	}

	function closeListEditionPanel( $listEditionPanel ){
		$listEditionPanel.css("height", "0");
		$listEditionPanel.css("padding-bottom", "0");
	}

	$(document).ready(function(){
		$(document).on("click", ".list-edition-button", function(){
			openListEditionPanel( $(this).siblings(".list-edition-panel") );
		});

		$(document).keydown(function(e) {
			var $focusedInput = $(".insert-item-content textarea:focus");
			if( $focusedInput.length && e.which == 13 ){
				e.preventDefault();
				saveEditorChanges( $focusedInput.closest(".insert-item-content") );
			}
		});

		setTimeout(function(){
			$( ".insert-item-content" ).draggable();

			$(".lists-organization input[type='hidden']").each( function(){
				$(this).val( $(this).attr("data-value") );
				console.log(this);
			});

			$(".list-edition-panel .lists-organization .sortables-ul").sortable({
				update: function(event, ui) {
					var $item = ui.item;
					var $parentListOrg =  $item.parents(".list-edition-panel").find('.lists-organization');
					var input = $parentListOrg.find('input[type="hidden"]');

					organizeLists( $parentListOrg );
					input.trigger( 'change' );
				}
			});

			$(".list-edition-panel .view-list .sortables-ul").sortable({
				update: function(event, ui) {
					var $item = ui.item;
					updateList($item.parents(".list-edition-panel"));
				}
			});

			$(document).on( "click", ".list-edition-panel .view-list .list-fast-edition-button", function(event){
				var $viewList = $(this).closest(".view-list");
				var $listItems = $viewList.find('li');
				var csv = "";
				var counter = 1;
				$listItems.each( function( index ){
					csv +=  $(this).find('.list-item-name').text().trim();
					if ( counter != index )
						csv += ', ';
				})
				console.log(csv);
			})

			$(document).on( "click", ".list-edition-panel .view-list .sortables-ul li .edit-button", function(event){
				openItemEdition( $(this).closest('li') );
			})

			$(document).on( "click", ".list-edition-panel .lists-organization .sortables-ul li .edit-button", function(event){
				createListView($(this).parents(".lists-organization").siblings(".view-list"), $(this).closest('li'));
				showSection( $(this).parents(".lists-visualization"), "view-list" );
			})

			$(document).on( "click", ".list-edition-panel .list-selection .organize-button:not(.active)", function(event){
				showSection( $(this).parents(".list-selection").siblings(".lists-visualization"), "lists-organization" );
				$(this).addClass("active");
			})

			$(document).on( "click", ".item-edition-buttons i", function(event){
				closeItemEdition( $(this).closest(".insert-item-content") );
			})

			$(".item-edition-field").on('input',function(e){
				$(this).siblings(".item-edition-buttons").removeClass("awaiting-edition");
			});

			$(document).on( "click", ".save-changes-button", function(event){
				saveEditorChanges( $(this).closest(".insert-item-content") );
			})

			$(document).on( "click", ".add-list-item i", function(event){
				addListItem( $(this).closest(".list-edition-panel") );
			})

			$(document).on( "click", ".add-list i", function(event){
				addList( $(this).closest(".list-edition-panel") );
			})

			$(document).on( "click", ".lists-visualization li .delete-list", function(event){
				event.preventDefault();
				event.stopPropagation();
				removeList( $(this).closest("li") );
			})

			$(document).on( "click", ".list-edition-panel .close-lists-panel-button", function(event){
				closeListEditionPanel($(this).closest(".list-edition-panel"));
			})

			$(document).on( "click", ".list-edition-panel .edit-name", function(event){
				editListName($(this).closest(".list-edition-panel"));
			})

			$(document).on( "click", ".view-list li .delete-list-item", function(event){
				removeListItem( $(this).closest("li") );
				console.log("remove");
			})
		}, 10);
	})

	function editListName( $listEditionPanel ){
		var $viewList = $listEditionPanel.find(".view-list");
		var listID = $viewList.attr("data-list-id");
		var $listsOrganization = $listEditionPanel.find(".lists-organization");
		var $list = $listsOrganization.find("li[data-list-id='"+ listID +"']");
		var $theName = $viewList.find(".the-list-name");
		var oldName = $theName.text();
		var newName = prompt("Enter the list title/name", oldName);

		if ( newName ){
			$list.find(".list-name").text(newName);
			$list.attr("data-list-name", newName);
			$theName.text(newName);
		}

		organizeLists( $listsOrganization );
	}

	function removeList( $list ){
		var $listsOrganization = $list.closest(".lists-organization");
		$list.remove();
		organizeLists( $listsOrganization );
	}

	function removeListItem( $listItem ){
		var $listEditionPanels = $listItem.closest(".list-edition-panel");
		console.log($listItem);
		$listItem.remove();
		updateList($listEditionPanels);
	}

	function addList( $listEditionPanel ){
		var title = prompt("Enter the list title/name");
		var $list = $listEditionPanel.find(".lists-organization ul");
		var $listsOrganization = $listEditionPanel.find(".lists-organization");
		var listLength = $list.find("li").length;
		if( title ){
			$list.append(
			'<li data-list-name="'+ title +'"  data-list-id="list_'+ (listLength + 1) +
			'" data-list-items="" class="sortable-li"><span class="list-name">'+ title +'</span>'
			+'<i class="fas fa-pencil-alt edit-button" title="Edit"></i><i class="far fa-trash-alt delete-list" title="Delete List"></i></li>');
			organizeLists( $listsOrganization );
		}
	}

	function addListItem( $listEditionPanel ){
		var $list = $listEditionPanel.find(".view-list ul");
		$list.append('<li class="sortable-li"><span class="list-item-name"></span>'
		+'<i class="fas fa-pencil-alt edit-button" title="Edit"></i><i class="far fa-trash-alt delete-list-item" title="Delete List"></i></li>');
	}

	function saveEditorChanges( $editor ){
		var $currentPanel = $editor.closest(".list-edition-panel");
		var liIndex = $editor.attr("data-list-item-index");
		var $listItemContent = $currentPanel.find(".view-list ul li:nth-child("+ liIndex +") .list-item-name");
		var newContent = $editor.find(".item-edition-field").val();

		$listItemContent.text(newContent);
		updateList($currentPanel);
		closeItemEdition( $editor );
	}

	function closeItemEdition( $editionContainer ){
		$editionContainer.fadeOut();
		$editionContainer.closest(".list-edition-panel").removeClass("editing-list-item");
		$editionContainer.find(".item-edition-buttons").addClass("awaiting-edition");
	}

	function openItemEdition( $listItem ){
		var $listPanel = $listItem.closest(".list-edition-panel");
		var $editionContainer = $listItem.closest(".lists-visualization").find(".insert-item-content");
		var $listItemsAll = $listItem.closest("ul").find("li");
		var $listItemContent = $listItem.find(".list-item-name").text();

		$listPanel.addClass("editing-list-item");
		$editionContainer.fadeIn();
		$editionContainer.attr("data-old-value", $listItemContent);
		$editionContainer.attr("data-list-id",$listItem.attr("data-list-id"));
		$editionContainer.attr("data-list-item-index", $listItemsAll.index( $listItem ) + 1);
		$editionContainer.find("textarea").val( $listItemContent );
		$editionContainer.find("textarea").addClass("awaiting-edition");
		$editionContainer.find("textarea").focus();
	}

	function updateList($listEditionPanels){
		var newValue = "";
		//console.log($listEditionPanels);
		var $viewList = $listEditionPanels.find(".view-list");
		var $listItems = $viewList.find("li");
		$listItems.each(function( index ){
			newValue += $(this).find(".list-item-name").text();
			if ( index < $listItems.length - 1 )
				newValue += ',';
		});

		//console.log(newValue);
		updateListValue($listEditionPanels, $viewList.attr("data-list-id"), newValue);
	}

	function updateListValue($listEditionPanels, listID, value){
		$listEditionPanels.find(".lists-organization li[data-list-id='"+ listID +"']").attr("data-list-items", value);
		organizeLists( $listEditionPanels.find('.lists-organization') );
	}

	function slideUpSections( $listEditionPanels ){
		$listEditionPanels.find(".view-list").slideUp();
		$listEditionPanels.find(".lists-organization").slideUp();
	}

	function slideDownSection( $listEditionPanels, sectionClass ){
		$listEditionPanels.find('.' + sectionClass).slideDown();
	}

	function showSection( $listEditionPanels, sectionClass ){
		if ( sectionClass != "lists-organization" )
			$(".list-edition-panel .list-selection .organize-button").removeClass("active");
		slideUpSections( $listEditionPanels );
		slideDownSection( $listEditionPanels, sectionClass );
	}

	function organizeLists( $listsOrganization ){
		var newOrganization = {};
		var input = $listsOrganization.find("input[type='hidden']");

		$listsOrganization.find("ul li").each(function(){
			var arrayListItems = $(this).attr("data-list-items").split(",");
			var listName = $(this).attr("data-list-name");
			newOrganization[$(this).attr("data-list-id")] = {};
			newOrganization[$(this).attr("data-list-id")]["items"] = arrayListItems;
			newOrganization[$(this).attr("data-list-id")]["name"] = listName;
		});

		var jsonString = JSON.stringify(newOrganization);

		input.attr("data-value", jsonString);
		input.val(jsonString);
		input.trigger( 'change' );
		//console.log(newOrganization);
	}

	function createListView( $viewList, $list ){
		var $sortableList = $viewList.find(".sortables-ul");
		var arrayListItems = $list.attr("data-list-items").split(",");

		$viewList.attr("data-list-id", $list.attr("data-list-id"));
		$viewList.find("span.the-list-name").html( $list.attr("data-list-name") );
		$sortableList.html("");
		arrayListItems.forEach(function(item, index, arr){
			$sortableList.append('<li class="sortable-li"><span class="list-item-name">'+ item +'</span><i class="fas fa-pencil-alt edit-button" title="Edit"></i><i class="far fa-trash-alt delete-list-item" title="Delete List"></i></li>');
		})
	}

} )( jQuery );
