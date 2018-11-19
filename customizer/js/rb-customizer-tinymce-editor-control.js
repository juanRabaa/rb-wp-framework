( function( $ ) {
	var tinymceSettings = {
		quicktags: {
			buttons:"strong,em,link,ul,ol,li,aligncenter"
		},
		tinymce: {
			branding:false,
			browser_spellcheck:true,
			cache_suffix:"wp-mce-4607-20180123",
			convert_urls:false,
			elementpath:false,
			end_container_on_empty_block:true,
			entities:"38,amp,60,lt,62,gt",
			entity_encoding:"raw",
			fix_list_elements:true,
			//formats:{alignleft: Array(2), aligncenter: Array(2), alignright: Array(2), strikethrough: {…}},
			indent:true,
			keep_styles:false,
			language:"es",
			menubar:false,
			plugins:"charmap,colorpicker,hr,lists,paste,tabfocus,textcolor,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wptextpattern",
			preview_styles:"font-family font-size font-weight font-style text-decoration text-transform",
			relative_urls:false,
			remove_script_host:false,
			resize:"vertical",
			skin:"lightgray",
			theme:"modern",
			toolbar1:"bold,italic,underline,strikethrough,alignleft,aligncenter,justifyleft,alignright,alignjustify,justifycenter,justifyright,justifyfull,bullist,numlist,outdent,indent,cut,copy,paste,undo,redo,link,unlink,image,cleanup,help,code,hr,removeformat,formatselect,fontselect,fontsizeselect,styleselect,sub,sup,forecolor,backcolor,forecolorpicker,backcolorpicker,charmap,visualaid,anchor,newdocument,blockquote,separator",
			wp_keep_scroll_position:false,
			wp_lang_attr:"es-ES",
			wp_shortcut_labels:{
				'Align center':"accessC",
				'Align left':"accessL",
				'Align right':"accessR",
				'Blockquote':"accessQ",
				'Bold':"metaB",
				'Bullet list':"accessU",
				'Code':"accessX",
				'Copy':"metaC",
				'Cut':"metaX",
				'Distraction-free writing mode':"accessW",
				'Heading 1':"access1",
				'Heading 2':"access2",
				'Heading 3':"access3",
				'Heading 4':"access4",
				'Heading 5':"access5",
				'Heading 6':"access6",
				'Insert Page Break tag':"accessP",
				'Insert Read More tag':"accessT",
				'Insert/edit image':"accessM",
				'Italic':"metaI",
				'Justify':"accessJ",
				'Keyboard Shortcuts':"accessH",
				'Numbered list':"accessO",
				'Paragraph':"access7",
				'Paste':"metaV",
				'Redo':"metaY",
				'Remove link':"accessS",
				'Select all':"metaA",
				'Strikethrough':"accessD",
				'Toolbar Toggle':"accessZ",
				'Underline':"metaU",
				'Undo':"metaZ"
			},
			wpautop:false,
			wpeditimage_html5_captions:true
		}
	};

	var currentEditorData = {
		tinyMCE: null,
		$controlPanel: null,
		editorPanelID: 'rb-tinymce-editor-panel',
		editorID: "rb-tinymce-editor",
		contentTimeout: null,
		isSubcontrol: function(){
			return this.$controlPanel.hasClass('rb-tinymce-control');
		},
		triggerControlChange: function(){
			this.$controlPanelInput().trigger('input');
		},
		getEditorContent: function(){
			return this.tinyMCE.getContent();
		},
		$controlPanelInput: function(){
			return this.$controlPanel.find('.rb-tinymce-input');
		},
		$controlPanelPlaceholder: function(){
			return this.$controlPanel.find('.tinymce-content-preview');
		},
		updateControl: function(){
			console.log(this.$controlPanelInput());
			console.log(this.getEditorContent());
			this.$controlPanelInput().val( this.getEditorContent() );
			this.triggerControlChange();
			console.log(this.$controlPanelPlaceholder());
			this.$controlPanelPlaceholder().html( this.getEditorContent() );
			this.$controlPanelPlaceholder().val( this.getEditorContent() );
		},
		linkEditorContentToControl: function(){
			var _this = this;
			this.tinyMCE.on("change KeyDown KeyUp", function(data) {
				_this.updateControl();
			});
		},
		initilize: function(_$controlPanel){
			if ( this.tinyMCE ){
				alert("Close current editor before opening another");
				return;
			}
			wp.editor.initialize( this.editorID, tinymceSettings);
			this.tinyMCE = tinyMCE.get(this.editorID);
			this.$controlPanel = _$controlPanel;
			var controlContent = currentEditorData.$controlPanelInput().val();
			currentEditorData.tinyMCE.setContent( controlContent );
			if( controlContent != currentEditorData.tinyMCE.getContent() ){
				clearTimeout(this.contentTimeout);
				this.contentTimeout = setTimeout(function(){
					currentEditorData.tinyMCE.setContent( currentEditorData.$controlPanelInput().val() );
				}, 1000);
			}

			this.linkEditorContentToControl();
			$('#' + this.editorPanelID).css({
				height: 'calc(100vh - 4.5rem)',
				paddingBottom: '10rem',
			});
		},
		remove: function(){
			$('#' + this.editorPanelID).css({
				height: 0,
				paddingBottom: 0,
			});
			wp.editor.remove(this.editorID);
			this.tinyMCE.destroy();
			this.tinyMCE = null;
			this.$controlPanel = null;
		},
	};

	$(document).ready(function(){
		$('#customize-controls').append(
		'<div class="rb-control-panel" id="rb-tinymce-editor-panel">'
		+'<div class="rb-control-panel-title-container controls-bar">'
		+	'<i class="fas fa-chevron-circle-left rb-control-panel-close-button close-button"></i>'
		+	'<h5 class="rb-control-panel-title"></h5>'
		+'</div>'
		+'<div class="multimedia-button rb-hollow-button">Insert multimedia</div>'
		+'<div id="rb-tinymce-editor">'
		+'</div>'
		+'</div>');
	});

	$(document).on('click', '#rb-tinymce-editor-panel .multimedia-button', function(e) {
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
			var finalHTML = "";
			imagesArr.forEach( function( image, index ){
				finalHTML += '<img src="'+ image.changed.url +'"/>';
			});
			currentEditorData.tinyMCE.insertContent(finalHTML);
		});
		custom_uploader.open();
	});

	$(document).on('click', '#rb-tinymce-editor-panel .controls-bar .close-button', function(){
		closeTinymceEditorPanel( $(this).closest('.customize-tinymce-control, .rb-tinymce-control') );
	});

	$(document).on('click', '.customize-tinymce-control .edit-button, .rb-tinymce-control .edit-button', function(){
		openTinymceEditorPanel( $(this).closest('.customize-tinymce-control, .rb-tinymce-control') );
	});

	function closeTinymceEditorPanel( $controlPanel ){
		currentEditorData.remove();
	}

	function openTinymceEditorPanel( $controlPanel ){
		currentEditorData.initilize($controlPanel);
	}

} )( jQuery );
