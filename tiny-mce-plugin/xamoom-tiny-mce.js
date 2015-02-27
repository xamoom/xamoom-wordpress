(function() {
    tinymce.create('tinymce.plugins.xamoom', {
        
        init : function(ed, url) {            
            ed.addButton('xamoom-insert-content', {
                title : 'xamoom',
                cmd : 'insert-xamoom-shortcode',
                image : 'https://pbs.twimg.com/profile_images/443349334688407552/qDGgZGXO_normal.png',
            });
 
            ed.addCommand('insert-xamoom-shortcode', function() {
                // triggers the thickbox
		var width = jQuery(window).width(), H = jQuery(window).height(), W = ( 720 < width ) ? 720 : width;
                W = W - 80;
                H = H - 84;
                tb_show( 'xamooom Shortcode Generator', '#TB_inline?width=' + W + '&height=' + H + '&inlineId=xamoom-content-shortcode-form' );
                jQuery('#TB_ajaxContent').width(750);
                /*var selected_text = ed.selection.getContent();
                var return_text = '';
                return_text = '[xamoom]';
                ed.execCommand('mceInsertContent', 0, return_text);*/
            });
        },
        createControl : function(n, cm) {
            return null;
        },
        
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
    
    //adding the actual dialog
    jQuery(function(){
	// creates a form to be displayed everytime the button is clicked
	// you should achieve this using AJAX instead of direct html code like this
	var form = jQuery('<div id="xamoom-content-shortcode-form">\
                            <p class="search-box">\
                                <label class="screen-reader-text" for="xamoom-page-search-input">Search Pages:</label>\
                                <input type="search" id="xamoom-page-search-input" name="s" value="">\
                                <input type="button" name="" id="search-submit" class="button" value="Search Pages">\
                            </p>\
                            <table class="widefat">\
                                <thead>\
                                    <tr>\
                                        <th scope="col" id="content-name" class="manage-column" style="">\
                                            Name\
                                        </th>\
                                        <th scope="col" id="comment" class="manage-column" style="">\
                                            Language\
                                        </th>\
                                        <th scope="col" id="response" class="manage-column column-response sortable desc" style="">\
                                            &nbsp;\
                                        </th>\
                                    </tr>\
                                </thead>\
                                <tfoot>\
                                    <tr>\
                                        <th colspan="3" align="center">\
                                            <p class="row-title" align="center"><a href="#">Load More</a></p>\
                                        </th>\
                                    </tr>\
                                </tfoot>\
                                <tbody>\
                                    <tr class="alternate">\
                                        <td>\
                                            <p class="row-title">A great content name</p>\
                                        </td>\
                                        <td align="center" width="50">\
                                            <select name="language" id="xamoom-page-language">\
                                                <option class="level-0" value="de">DE</option>\
                                                <option class="level-0" value="de">EN</option>\
                                            </select>\
                                        </td>\
                                        <td align="right" width="50">\
                                            <input type="button" id="shortcode-submit-id" class="button-primary" value="Insert" name="submit" />\
                                        </td>\
                                    </tr>\
                                    <tr>\
                                        <td>\
                                            <p class="row-title">A great content name</p>\
                                        </td>\
                                        <td align="center" width="50">\
                                            <select name="language" id="xamoom-page-language">\
                                                <option class="level-0" value="de">DE</option>\
                                                <option class="level-0" value="de">EN</option>\
                                            </select>\
                                        </td>\
                                        <td align="right" width="50">\
                                            <input type="button" id="shortcode-submit-id" class="button-primary" value="Insert" name="submit" />\
                                        </td>\
                                    </tr>\
                                    <tr class="alternate">\
                                        <td>\
                                            <p class="row-title">A great content name</p>\
                                        </td>\
                                        <td align="center" width="50">\
                                            <select name="language" id="xamoom-page-language">\
                                                <option class="level-0" value="de">DE</option>\
                                                <option class="level-0" value="de">EN</option>\
                                            </select>\
                                        </td>\
                                        <td align="right" width="50">\
                                            <input type="button" id="shortcode-submit-id" class="button-primary" value="Insert" name="submit" />\
                                        </td>\
                                    </tr>\
                                    <tr>\
                                        <td>\
                                            <p class="row-title">A great content name</p>\
                                        </td>\
                                        <td align="center" width="50">\
                                            <select name="language" id="xamoom-page-language">\
                                                <option class="level-0" value="de">DE</option>\
                                                <option class="level-0" value="de">EN</option>\
                                            </select>\
                                        </td>\
                                        <td align="right" width="50">\
                                            <input type="button" id="shortcode-submit-id" class="button-primary" value="Insert" name="submit" />\
                                        </td>\
                                    </tr>\
                                    <tr class="alternate">\
                                        <td>\
                                            <p class="row-title">A great content name</p>\
                                        </td>\
                                        <td align="center" width="50">\
                                            <select name="language" id="xamoom-page-language">\
                                                <option class="level-0" value="de">DE</option>\
                                                <option class="level-0" value="de">EN</option>\
                                            </select>\
                                        </td>\
                                        <td align="right" width="50">\
                                            <input type="button" id="shortcode-submit-id" class="button-primary" value="Insert" name="submit" />\
                                        </td>\
                                    </tr>\
                                    <tr>\
                                        <td>\
                                            <p class="row-title">A great content name</p>\
                                        </td>\
                                        <td align="center" width="50">\
                                            <select name="language" id="xamoom-page-language">\
                                                <option class="level-0" value="de">DE</option>\
                                                <option class="level-0" value="de">EN</option>\
                                            </select>\
                                        </td>\
                                        <td align="right" width="50">\
                                            <input type="button" id="shortcode-submit-id" class="button-primary" value="Insert" name="submit" />\
                                        </td>\
                                    </tr>\
                                    <tr class="alternate">\
                                        <td>\
                                            <p class="row-title">A great content name</p>\
                                        </td>\
                                        <td align="center" width="50">\
                                            <select name="language" id="xamoom-page-language">\
                                                <option class="level-0" value="de">DE</option>\
                                                <option class="level-0" value="de">EN</option>\
                                            </select>\
                                        </td>\
                                        <td align="right" width="50">\
                                            <input type="button" id="shortcode-submit-id" class="button-primary" value="Insert" name="submit" />\
                                        </td>\
                                    </tr>\
                                    <tr>\
                                        <td>\
                                            <p class="row-title">A great content name</p>\
                                        </td>\
                                        <td align="center" width="50">\
                                            <select name="language" id="xamoom-page-language">\
                                                <option class="level-0" value="de">DE</option>\
                                                <option class="level-0" value="de">EN</option>\
                                            </select>\
                                        </td>\
                                        <td align="right" width="50">\
                                            <input type="button" id="shortcode-submit-id" class="button-primary" value="Insert" name="submit" />\
                                        </td>\
                                    </tr>\
                                    <tr class="alternate">\
                                        <td>\
                                            <p class="row-title">A great content name</p>\
                                        </td>\
                                        <td align="center" width="50">\
                                            <select name="language" id="xamoom-page-language">\
                                                <option class="level-0" value="de">DE</option>\
                                                <option class="level-0" value="de">EN</option>\
                                            </select>\
                                        </td>\
                                        <td align="right" width="50">\
                                            <input type="button" id="shortcode-submit-id" class="button-primary" value="Insert" name="submit" />\
                                        </td>\
                                    </tr>\
                                    <tr>\
                                        <td>\
                                            <p class="row-title">A great content name</p>\
                                        </td>\
                                        <td align="center" width="50">\
                                            <select name="language" id="xamoom-page-language">\
                                                <option class="level-0" value="de">DE</option>\
                                                <option class="level-0" value="de">EN</option>\
                                            </select>\
                                        </td>\
                                        <td align="right" width="50">\
                                            <input type="button" id="shortcode-submit-id" class="button-primary" value="Insert" name="submit" />\
                                        </td>\
                                    </tr>\
                                    <tr class="alternate">\
                                        <td>\
                                            <p class="row-title">A great content name</p>\
                                        </td>\
                                        <td align="center" width="50">\
                                            <select name="language" id="xamoom-page-language">\
                                                <option class="level-0" value="de">DE</option>\
                                                <option class="level-0" value="de">EN</option>\
                                            </select>\
                                        </td>\
                                        <td align="right" width="50">\
                                            <input type="button" id="shortcode-submit-id" class="button-primary" value="Insert" name="submit" />\
                                        </td>\
                                    </tr>\
                                    <tr>\
                                        <td>\
                                            <p class="row-title">A great content name</p>\
                                        </td>\
                                        <td align="center" width="50">\
                                            <select name="language" id="xamoom-page-language">\
                                                <option class="level-0" value="de">DE</option>\
                                                <option class="level-0" value="de">EN</option>\
                                            </select>\
                                        </td>\
                                        <td align="right" width="50">\
                                            <input type="button" id="shortcode-submit-id" class="button-primary" value="Insert" name="submit" />\
                                        </td>\
                                    </tr>\
                                </tbody>\
                            </table>\
                        </div>');
        
        var table = form.find('table');
	form.appendTo('body').hide();
    });
    
})();