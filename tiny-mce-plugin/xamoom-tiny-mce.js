var xamoom_search_cursor = "";
var xamoom_search_last_search_params = null;

function xamoomSearchPages(){
    var query = jQuery('#xamoom-page-search-input').val();
    var params = { page_size: "10", ft_query: query};
    
    xamoomLoadPages(params,false,null);
}

function xamoomInsertShortCode(content_id){
    var selected_text = tinyMCE.activeEditor.selection.getContent();
    var selected_lang = jQuery("#xamoom-page-language" + content_id + " option:selected").text();
    var return_text = "";
    return_text = "[xamoom lang=" + selected_lang + " id=" + content_id + "]";
    tinyMCE.activeEditor.execCommand("mceInsertContent", 0, return_text);
    tb_remove();
    
    //get content to update title and excerpt
    jQuery.ajax({
        contentType: 'application/json',
        data: JSON.stringify({content_id:content_id,language:selected_lang,api_key:xamoom_api_key}),
        dataType: 'json',
        success: function(data){
                jQuery('#title').val(data.title);
                jQuery('#excerpt').val(data.description);
                jQuery('#title').focus();
            },
        error: function(){
            alert("Something went wrong. Please check you API Key on Settings->xamoom.");
        },
        type: 'POST',
        url: 'https://xamoom-api-dot-xamoom-cloud-dev.appspot.com/_ah/api/xamoomIntegrationApi/v1/get_content_by_content_id'
    });
}

function xamoomLoadPages(params,append,cursor){
    if (params == null) {
        params = xamoom_search_last_search_params;
    } else {
        xamoom_search_last_search_params = params; //save params of last search for paging
    }
    
    if (cursor != null) {
        params['cursor'] = cursor;
    }
    
    params['api_key'] = xamoom_api_key;
    
    jQuery.ajax({
        contentType: 'application/json',
        data: JSON.stringify(params),
        dataType: 'json',
        success: function(data){
            if (!append) {
                jQuery("#xamoom-pages-list").empty()
            }
            
            //save cursor
            xamoom_search_cursor = data.cursor;
            
            //hide load more if has_more is false
            if (data.has_more == "False") {
               jQuery('#xamoom-load-more').hide();
            } else {
                jQuery('#xamoom-load-more').show();
            }
            
            for(i = 0; i < data.items.length; i++){
                langs = ""
                for (j = 0; j < data.items[i].languages.length; j++) {
                    langs += "<option class='level-0' value='" + data.items[i].languages[j] + "'>" + data.items[i].languages[j] + "</option>"
                }
                
                alternate = "alternate";
                if (i % 2 != 0) {
                    alternate = "";
                }
                
                jQuery("#xamoom-pages-list").append('\
                                                    <tr class="' +  alternate + '">\
                                                        <td>\
                                                            <p class="row-title">' + data.items[i].name + '</p>\
                                                        </td>\
                                                        <td align="center" width="50">\
                                                            <select name="language" id="xamoom-page-language' + data.items[i].content_id + '">' + langs + '</select>\
                                                        </td>\
                                                        <td align="right" width="50">\
                                                            <input type="button" onClick="xamoomInsertShortCode(\'' + data.items[i].content_id + '\')" id="shortcode-submit-id" class="button-primary" value="Insert" name="submit" />\
                                                        </td>\
                                                    </tr>\
                                                    ');
            }
            },
        error: function(){
            alert("Something went wrong...");
        },
        processData: false,
        type: 'POST',
        url: 'https://xamoom-api-dot-xamoom-cloud-dev.appspot.com/_ah/api/xamoomIntegrationApi/v1/content_query'
    });
}

//the acual button in tiny mce and initial loading of pages  
(function() {
    
    //Create Tiny MCE Button
    tinymce.create('tinymce.plugins.xamoom', {
        
        init : function(ed, url) {            
            ed.addButton('xamoom-insert-content', {
                title : 'xamoom',
                cmd : 'insert-xamoom-shortcode',
                image : 'https://pbs.twimg.com/profile_images/443349334688407552/qDGgZGXO_normal.png',
            });
 
            ed.addCommand('insert-xamoom-shortcode', function() {
                //load pages initially
                xamoomLoadPages({ page_size: "10" },false,null);
                
                // triggers the thickbox
		var width = jQuery(window).width(), H = jQuery(window).height(), W = ( 720 < width ) ? 720 : width;
                W = W - 80;
                H = H - 84;
                tb_show( 'xamooom Shortcode Generator', '#TB_inline?width=' + W + '&height=' + H + '&inlineId=xamoom-content-shortcode-form' );
                jQuery('#TB_ajaxContent').width(750);
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
    
    //adding the page search dialog
    jQuery(function(){
	var form = jQuery('<div id="xamoom-content-shortcode-form">\
                            <p class="search-box">\
                                <label class="screen-reader-text" for="xamoom-page-search-input">Search Pages:</label>\
                                <input type="search" id="xamoom-page-search-input" name="s" value="">\
                                <input type="button" onClick="xamoomSearchPages();" name="" id="search-submit" class="button" value="Search Pages">\
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
                                <tfoot id="xamoom-load-more">\
                                    <tr>\
                                        <th colspan="3" align="center">\
                                            <p align="center"><input type="button" onClick="xamoomLoadPages(null,true,xamoom_search_cursor);" name="" class="button" value="Load More"></p>\
                                        </th>\
                                    </tr>\
                                </tfoot>\
                                <tbody id="xamoom-pages-list">\
                                </tbody>\
                            </table>\
                        </div>');
        
        var table = form.find('table');
	form.appendTo('body').hide();
    });
    
})();