(function() {
    tinymce.create('tinymce.plugins.xamoom', {
        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        
        init : function(ed, url) {            
            ed.addButton('xamoom-insert-content', {
                title : 'xamoom',
                //cmd : 'insert-xamoom-shortcode',
                image : 'https://pbs.twimg.com/profile_images/443349334688407552/qDGgZGXO_normal.png',
                menu: [
                                {
                                    text: 'Pop-Up',
                                    onclick: function() {
                                        editor.windowManager.open( {
                                            title: 'Insert Random Shortcode',
                                            body: [
                                                {
                                                    type: 'textbox',
                                                    name: 'textboxName',
                                                    label: 'Text Box',
                                                    value: '30'
                                                },
                                                {
                                                    type: 'textbox',
                                                    name: 'multilineName',
                                                    label: 'Multiline Text Box',
                                                    value: 'You can say a lot of stuff in here',
                                                    multiline: true,
                                                    minWidth: 300,
                                                    minHeight: 100
                                                },
                                                {
                                                    type: 'listbox',
                                                    name: 'listboxName',
                                                    label: 'List Box',
                                                    'values': [
                                                        {text: 'Option 1', value: '1'},
                                                        {text: 'Option 2', value: '2'},
                                                        {text: 'Option 3', value: '3'}
                                                    ]
                                                }
                                            ],
                                            onsubmit: function( e ) {
                                                editor.insertContent( '[random_shortcode textbox="' + e.data.textboxName + '" multiline="' + e.data.multilineName + '" listbox="' + e.data.listboxName + '"]');
                                            }
                                        });
                                    }
                            }
                            ]
            });
 
            ed.addCommand('insert-xamoom-shortcode', function() {
                var selected_text = ed.selection.getContent();
                var return_text = '';
                return_text = '[xamoom]';
                ed.execCommand('mceInsertContent', 0, return_text);
            });
        },
        
        /*init : function(ed, url) {            
            ed.addButton( 'insert-xamoom-shortcode',
                         {
                            text: 'Sample Dropdown',
                            icon: false,
                            type: 'menubutton',
                            menu: [
                                {
                                    text: 'Pop-Up',
                                    onclick: function() {
                                        editor.windowManager.open( {
                                            title: 'Insert Random Shortcode',
                                            body: [
                                                {
                                                    type: 'textbox',
                                                    name: 'textboxName',
                                                    label: 'Text Box',
                                                    value: '30'
                                                },
                                                {
                                                    type: 'textbox',
                                                    name: 'multilineName',
                                                    label: 'Multiline Text Box',
                                                    value: 'You can say a lot of stuff in here',
                                                    multiline: true,
                                                    minWidth: 300,
                                                    minHeight: 100
                                                },
                                                {
                                                    type: 'listbox',
                                                    name: 'listboxName',
                                                    label: 'List Box',
                                                    'values': [
                                                        {text: 'Option 1', value: '1'},
                                                        {text: 'Option 2', value: '2'},
                                                        {text: 'Option 3', value: '3'}
                                                    ]
                                                }
                                            ],
                                            onsubmit: function( e ) {
                                                editor.insertContent( '[random_shortcode textbox="' + e.data.textboxName + '" multiline="' + e.data.multilineName + '" listbox="' + e.data.listboxName + '"]');
                                            }
                                        });
                                    }
                            }
                            ]
            });*/
 
            /*ed.addCommand('insert-xamoom-shortcode', function() {
                var selected_text = ed.selection.getContent();
                var return_text = '';
                return_text = '[xamoom]';
                ed.execCommand('mceInsertContent', 0, return_text);
            });
        },*/
 
        /**
         * Creates control instances based in the incomming name. This method is normally not
         * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
         * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
         * method can be used to create those.
         *
         * @param {String} n Name of the control to create.
         * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
         * @return {tinymce.ui.Control} New control instance or null if no control was created.
         */
        createControl : function(n, cm) {
            return null;
        },
 
        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @return {Object} Name/value array containing information about the plugin.
         */
        getInfo : function() {
            return {
                longname : 'xamoom',
                author : 'xamoom GmbH',
                authorurl : 'http://xamoom.com',
                infourl : 'http://xamoom.com',
                version : "0.1"
            };
        }
    });
    // Register plugin
    tinymce.PluginManager.add( 'xamoom', tinymce.plugins.xamoom );
})();



// closure to avoid namespace collision
(function(){
	// creates the plugin
	tinymce.create('tinymce.plugins.xamoom', {
		// creates control instances based on the control's id.
		// our button's id is "mygallery_button"
		createControl : function(id, controlManager) {
			if (id == 'xamoom-insert-content') {
				// creates the button
				var button = controlManager.createButton('xamoom-insert-content', {
					title : 'xamoom', // title of the button
					image : 'https://pbs.twimg.com/profile_images/443349334688407552/qDGgZGXO_normal.png',
					onclick : function() {
						// triggers the thickbox
						var width = jQuery(window).width(), H = jQuery(window).height(), W = ( 720 < width ) ? 720 : width;
						W = W - 80;
						H = H - 84;
						tb_show( 'xamooom Shortcode Generator', '#TB_inline?width=' + W + '&height=' + H + '&inlineId=mygallery-form' );
					}
				});
				return button;
			}
			return null;
		}
	});
	
	// Register plugin
        tinymce.PluginManager.add( 'xamoom', tinymce.plugins.xamoom );
	
	// executes this when the DOM is ready
	jQuery(function(){
		// creates a form to be displayed everytime the button is clicked
		// you should achieve this using AJAX instead of direct html code like this
		var form = jQuery('<div id="mygallery-form"><table id="mygallery-table" class="form-table">\
			<tr>\
				<th><label for="mygallery-columns">Columns</label></th>\
				<td><input type="text" id="mygallery-columns" name="columns" value="3" /><br />\
				<small>specify the number of columns.</small></td>\
			</tr>\
			<tr>\
				<th><label for="mygallery-id">Post ID</label></th>\
				<td><input type="text" name="id" id="mygallery-id" value="" /><br />\
				<small>specify the post ID. Leave blank if you want to use the current post.</small>\
			</tr>\
			<tr>\
				<th><label for="mygallery-size">Size</label></th>\
				<td><select name="size" id="mygallery-size">\
					<option value="thumbnail">Thumbnail</option>\
					<option value="medium">Medium</option>\
					<option value="large">Large</option>\
					<option value="full">Full</option>\
				</select><br />\
				<small>specify the image size to use for the thumbnail display.</small></td>\
			</tr>\
			<tr>\
				<th><label for="mygallery-orderby">Order By</label></th>\
				<td><input type="text" name="orderby" id="mygallery-orderby" value="menu_order ASC, ID ASC" /><br /><small>RAND (random) is also supported.</small></td>\
			</tr>\
			<tr>\
				<th><label for="mygallery-itemtag">Item Tag</label></th>\
				<td><input type="text" name="itemtag" id="mygallery-itemtag" value="dl" /><br />\
					<small>the name of the XHTML tag used to enclose each item in the gallery.</small></td>\
			</tr>\
			<tr>\
				<th><label for="mygallery-icontag">Icon Tag</label></th>\
				<td><input type="text" name="icontag" id="mygallery-icontag" value="dt" /><br />\
					<small>the name of the XHTML tag used to enclose each thumbnail icon in the gallery.</small></td>\
			</tr>\
			<tr>\
				<th><label for="mygallery-captiontag">Caption Tag</label></th>\
				<td><input type="text" name="captiontag" id="mygallery-captiontag" value="dd" /><br />\
					<small>the name of the XHTML tag used to enclose each caption.</small></td>\
			</tr>\
			<tr>\
				<th><label for="mygallery-link">Link</label></th>\
				<td><input type="text" name="link" id="mygallery-link" value="" /><br />\
					<small>you can set it to "file" so each image will link to the image file, otherwise leave blank.</small></td>\
			</tr>\
			<tr>\
				<th><label for="mygallery-include">Include Attachment IDs</label></th>\
				<td><input type="text" name="include" id="mygallery-include" value="" /><br />\
					<small>comma separated attachment IDs</small>\
				</td>\
			</tr>\
			<tr>\
				<th><label for="mygallery-exclude">Exclude Attachment IDs</label></th>\
				<td><input type="text" id="mygallery-exclude" name="exclude" value="" /><br />\
					<small>comma separated attachment IDs</small>\
				</td>\
			</tr>\
		</table>\
		<p class="submit">\
			<input type="button" id="mygallery-submit" class="button-primary" value="Insert Gallery" name="submit" />\
		</p>\
		</div>');
		
		var table = form.find('table');
		form.appendTo('body').hide();
		
		// handles the click event of the submit button
		form.find('#mygallery-submit').click(function(){
			// defines the options and their default values
			// again, this is not the most elegant way to do this
			// but well, this gets the job done nonetheless
			var options = { 
				'columns'    : '3',
				'id'         : '',
				'size'       : 'thumbnail',
				'orderby'    : 'menu_order ASC, ID ASC',
				'itemtag'    : 'dl',
				'icontag'    : 'dt',
				'captiontag' : 'dd',
				'link'       : '',
				'include'    : '',
				'exclude'    : '' 
				};
			var shortcode = '[gallery';
			
			for( var index in options) {
				var value = table.find('#mygallery-' + index).val();
				
				// attaches the attribute to the shortcode only if it's different from the default value
				if ( value !== options[index] )
					shortcode += ' ' + index + '="' + value + '"';
			}
			
			shortcode += ']';
			
			// inserts the shortcode into the active editor
			tinyMCE.activeEditor.execCommand('mceInsertContent', 0, shortcode);
			
			// closes Thickbox
			tb_remove();
		});
	});
})()