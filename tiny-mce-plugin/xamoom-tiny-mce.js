/*
xamoom Wordpress Plugin
Copyright (C) 2015  xamoom GmbH

This file is part of xamoom-wordpress.

xamoom-wordpress is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

xamoom-wordpress is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with xamoom-wordpress.  If not, see <http://www.gnu.org/licenses/>.
*/

// the current search cursor for page queries.
var xamoom_search_cursor = "";

//currently set search options
var xamoom_search_last_search_params = null;

/**
 * Initializes a new search (page 1) for content pages in the xamoom backend
 * @return void
 */
function xamoomSearchPages(){
  //get the search query
  var query = jQuery('#xamoom-page-search-input').val();

  //prepare search params
  var params = { page_size: "10", ft_query: query};

  //load the pages using these params from the xamoom backend.
  xamoomLoadPages(params,false,null);
}

/**
 * Inserts a Wordpress Shortcode according to the users selection to the current
 * page at the current cursor position and closes the popup window.
 * @param content_id - the selected content id
 * @return void
 */
function xamoomInsertShortCode(content_id){
  //get currently selected text in page editor.
  var selected_text = tinyMCE.activeEditor.selection.getContent();

  //get the selected language of the content to insert.
  var selected_lang = jQuery("#xamoom-page-language" + content_id + " option:selected").text();

  //prepare shortcode text.
  var return_text = "";
  return_text = "[xamoom lang=" + selected_lang + " id=" + content_id + "]";

  //insert shortcode into editor
  tinyMCE.activeEditor.execCommand("mceInsertContent", 0, return_text);

  //close popup
  tb_remove();

  //get content to update title and excerpt
  jQuery.ajax({
    contentType: 'application/json',
    dataType: 'json',
    headers: {"Authorization":xamoom_api_key},
    success: function(data){
                            jQuery('#title').val(data.title);
                            jQuery('#excerpt').val(data.description);
                            jQuery('#title').focus();
                           },
    error: function(){
                      alert(i18n_api_key_error);
                     },
    type: 'GET',
    url: xamoom_api_endpoint  + 'content/' + content_id + "/" + selected_lang
  });
}

/**
 * Loads a page of pages by doing a call to the xamoom backend api
 * @param params - search params dict
 * @param append - start a new list or append next page to the list (boolean)
 * @param the current search cursor (paging) for the next request
 * @return void
 */
function xamoomLoadPages(params,append,cursor){
    //if this is a paging request, we have to keep the params from the last search.
    if (params == null) { //take the params from the last page
        params = xamoom_search_last_search_params;
    } else { //save params for the next pages
        xamoom_search_last_search_params = params; //save params of last search for paging
    }

    //of there is a cursor (page > 1) we have to ad it to the request
    if (cursor != null) {
        params['cursor'] = cursor;
    }

    //the actual search request to the xamoom backend API
    jQuery.ajax({
      contentType: 'application/json',
      data: JSON.stringify(params),
      headers: {"Authorization":xamoom_api_key},
      dataType: 'json',
      success: function(data){ //process response if everything went well
        if (!append) { //if append == False clear the list.
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

        //loop through the pages in the response and add them to the list
        for(i = 0; i < data.items.length; i++){
          //append available language options to the page entry
          langs = ""
          for (j = 0; j < data.items[i].languages.length; j++) {
              langs += "<option class='level-0' value='" + data.items[i].languages[j] + "'>" + data.items[i].languages[j] + "</option>"
          }

          //every second line get'S an "alternate" css attribute to make it look different.
          alternate = "alternate";
          if (i % 2 != 0) {
              alternate = "";
          }

          //add the page entry to the list as HTML
          jQuery("#xamoom-pages-list").append('\
                                              <tr class="' +  alternate + '">\
                                                  <td>\
                                                      <p class="row-title">' + data.items[i].name + '</p>\
                                                  </td>\
                                                  <td align="center" width="50">\
                                                      <select name="language" id="xamoom-page-language' + data.items[i].content_id + '">' + langs + '</select>\
                                                  </td>\
                                                  <td align="right" width="50">\
                                                      <input type="button" onClick="xamoomInsertShortCode(\'' + data.items[i].content_id + '\')" id="shortcode-submit-id" class="button-primary" value="' + i18n_insert + '" name="submit" />\
                                                  </td>\
                                              </tr>\
                                            ');
        }
      },
      error: function(){ //something went wrong (check console)
          alert(i18n_generic_error);
      },
      processData: false,
      type: 'GET',
      url: xamoom_api_endpoint  + 'content?' + jQuery.param(params)
    });
}

//the acual button in tiny mce and initial loading of pages
(function() {

  //Create Tiny MCE Button
  tinymce.create('tinymce.plugins.xamoom', {

    //Initializes the tiny mce plugin
    init : function(ed, url) {
        //add the button with click command
        ed.addButton('xamoom-insert-content', {
            title : 'xamoom',
            cmd : 'insert-xamoom-shortcode',
            image : 'https://storage.googleapis.com/xamoom-public-resources/icon.png',
        });

        //register click command
        ed.addCommand('insert-xamoom-shortcode', function() {
          //load first page of pages
          xamoomLoadPages({ page_size: "10" },false,null);

          //build popup
          var width = jQuery(window).width(), H = jQuery(window).height(), W = ( 720 < width ) ? 720 : width;
              W = W - 80;
              H = H - 84;
              tb_show( 'xamoom Shortcode Generator', '#TB_inline?width=' + W + '&height=' + H + '&inlineId=xamoom-content-shortcode-form' );
              jQuery('#TB_ajaxContent').width(750);
          });
    },

    //skip this - we don't need this in this case
    createControl : function(n, cm) {
      return null;
    },

    //set plugin info
    getInfo : function() {
          return {
              longname : 'xamoom',
              author : 'xamoom GmbH',
              authorurl : 'http://xamoom.com',
              infourl : 'http://xamoom.com',
              version : "1.0"
          };
      }

  });

  // Register plugin
  tinymce.PluginManager.add('xamoom', tinymce.plugins.xamoom );

  //adding the page search dialog
  jQuery(function(){
  	var form = jQuery('<div id="xamoom-content-shortcode-form">\
                              <p class="search-box">\
                                  <label class="screen-reader-text" for="xamoom-page-search-input">Search Pages:</label>\
                                  <input type="search" id="xamoom-page-search-input" name="s" value="">\
                                  <input type="button" onClick="xamoomSearchPages();" name="" id="search-submit" class="button" value="' + i18n_search_pages + '">\
                              </p>\
                              <div id="xamoom-content-list">\
                                <table class="widefat">\
                                    <thead>\
                                        <tr>\
                                            <th scope="col" id="content-name" class="manage-column" style="">\
                                                ' + i18n_name + '\
                                            </th>\
                                            <th scope="col" id="comment" class="manage-column" style="">\
                                                ' + i18n_languages + '\
                                            </th>\
                                            <th scope="col" id="response" class="manage-column column-response sortable desc" style="">\
                                                &nbsp;\
                                            </th>\
                                        </tr>\
                                    </thead>\
                                    <tfoot id="xamoom-load-more">\
                                        <tr>\
                                            <th colspan="3" align="center">\
                                                <p align="center"><input type="button" onClick="xamoomLoadPages(null,true,xamoom_search_cursor);" name="" class="button" value="' + i18n_load_more + '"></p>\
                                            </th>\
                                        </tr>\
                                    </tfoot>\
                                    <tbody id="xamoom-pages-list">\
                                    </tbody>\
                                </table>\
                              </div>\
                          </div>');

    //get popup container and add search dialog html
    var table = form.find('table');
  	form.appendTo('body').hide();
  });

  jQuery('input[type=search]').on('search', function () {
    xamoomSearchPages();
  });

})(); //excute plugin code
