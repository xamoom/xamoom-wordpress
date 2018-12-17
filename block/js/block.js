const { __, setLocaleData } = wp.i18n;
const { registerBlockType } = wp.blocks;

const blockStyle = {
	backgroundColor: '#900',
	color: '#fff',
	padding: '20px',
};

//current functio to select content
var func_xamoom_select = null;
var xamoom_content_id = null;

function xamoomSelectContent(content_id){
  //get the selected language of the content to insert.
  var selected_lang = jQuery("#xamoom-page-language" + content_id + " option:selected").text();
  func_xamoom_select(content_id, selected_lang)

  //get content to update title and excerpt
  jQuery.ajax({
    contentType: 'application/json',
    dataType: 'json',
    headers: {"ApiKey":xamoom_api_key},
    success: function(data){
                            wp.data.dispatch( 'core/editor' ).editPost( {
                                                title: data['data']['attributes']['display-name'] ,
                                                excerpt: data['data']['attributes']['description']
                            } );

                            jQuery('#xamoom-search-form').hide();
                            jQuery('#xamoom-selected-page-container').show();
                            jQuery('#xamoom-selected-page-title').text(data['data']['attributes']['display-name'])
                           },
    error: function(){
                      alert(i18n_api_key_error);
                     },
    type: 'GET',
    url: xamoom_api_endpoint  + 'contents/' + content_id + "?lang=" + selected_lang
  });
}


( function() {
	var __ = wp.i18n.__; // The __() for internationalization.
	var el = wp.element.createElement; // The wp.element.createElement() function to create elements.
	var registerBlockType = wp.blocks.registerBlockType; // The registerBlockType() to register blocks.

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
      var query = jQuery('#xamoom-page-search-box').val();

      //prepare search params
      var params = { "page[size]": "10", "filter[name]": query};

      //load the pages using these params from the xamoom backend.
      xamoomLoadPages(params,false,null);
    }

    function xamoomInit(repeat){
        jQuery.ajax({
            contentType: 'application/json',
            dataType: 'json',
            headers: {"ApiKey":xamoom_api_key},
            success: function(data){
                                    jQuery('#xamoom-search-form').hide();
                                    jQuery('#xamoom-selected-page-container').show();
                                    jQuery('#xamoom-selected-page-title').text(data['data']['attributes']['display-name'])
                                   },
            error: function(){
                              jQuery('#xamoom-search-form').show();
                              jQuery('#xamoom-selected-page-container').hide();

                              if(repeat){
                                xamoomSearchPages();
                                xamoomInit(false);
                              }
                             },
            type: 'GET',
            url: xamoom_api_endpoint  + 'contents/' + xamoom_content_id
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
            params['page[cursor]'] = cursor;
        }

        jQuery('#xamoom-load-more').hide();

        //the actual search request to the xamoom backend API
        jQuery.ajax({
          contentType: 'application/json',
          data: JSON.stringify(params),
          headers: {"ApiKey":xamoom_api_key},
          dataType: 'json',
          success: function(data){ //process response if everything went well
            if (!append) { //if append == False clear the list.
                jQuery("#xamoom-search-list").empty()
            }

            //save cursor
            xamoom_search_cursor = data['meta']['cursor'];

            //loop through the pages in the response and add them to the list
            for(i = 0; i < data['data'].length; i++){
              //append available language options to the page entry
              langs = ""
              for (j = 0; j < data['data'][i]['attributes']['languages'].length; j++) {
                  langs += "<option class='level-0' value='" + data['data'][i]['attributes']['languages'][j] + "'>" + data['data'][i]['attributes']['languages'][j] + "</option>"
              }

              //every second line get'S an "alternate" css attribute to make it look different.
              alternate = "alternate";
              if (i % 2 != 0) {
                  alternate = "";
              }

              row_html = "<div style='display: table-row;'>";

              row_html += "<div style='display: table-cell;'>";
              row_html += "<p>" + data['data'][i]['attributes']['display-name'] + "</p>";
              row_html += "</div>";

              row_html += "<div style='display: table-cell; align:right;'>";
              row_html += "<select name='language' id='xamoom-page-language" + data['data'][i]['id'] + "'>" + langs + "</select>";
              row_html += "</div>";

              row_html += "<div style='display: table-cell; align:right;'>";
              row_html += '<input type="button" onClick="xamoomSelectContent(\'' + data['data'][i]['id'] + '\')" id="shortcode-submit-id" class="button" value="' + i18n_insert + '" name="submit" />';
              row_html += "</div>";


              row_html += "</div>";

              jQuery("#xamoom-search-list").append(row_html);

              //hide load more if has_more is false
              if (data['meta']['has-more'] == false) {
                jQuery('#xamoom-load-more').hide();
              } else {
                jQuery('#xamoom-load-more').show();
              }
            }
          },
          error: function(){ //something went wrong (check console)
              alert(i18n_generic_error);
          },
          processData: false,
          type: 'GET',
          url: xamoom_api_endpoint  + 'contents?' + jQuery.param(params)
        });
    }

	registerBlockType( 'xamoom/content', {
		title: 'xamoom',
		icon: 'shield-alt',
		category: 'embed',
		supports: { multiple: false },
        attributes: {
            content_id: {
                type: 'string',
                default: '-1'
            },
            content_lang: {
                type: 'string',
                default: 'en'
            }
        },


		//
		edit: function( props ) {
		    var select = function(content_id, lang){
		        var attributes = props.attributes;
		        attributes['content_id'] = content_id
		        attributes['content_lang'] = lang
		    };

		    var search = function( event ) {
                xamoomSearchPages();
            };

            var reset = function( event ) {
                xamoom_content_id = '-1'
                xamoomInit(true);
            };

            var attributes = props.attributes;
            func_xamoom_select = select
            xamoom_content_id = attributes['content_id']

            xamoomInit(true);

            var loadmore = function( event ) {
                func_xamoom_select = select
                xamoomLoadPages(null,true,xamoom_search_cursor);
            };

			return el(
                        'div',
                        { className: props.className, id: 'xamoom-gutenberg-container' },
			            [
                            el(
                                'div',
                                { className: props.className, id: 'xamoom-search-form' },
                                [
                                    el(
                                        'p',
                                        { className: props.className },
                                        [
                                            el(
                                                'input',
                                                {
                                                    className: 'xamoom-page-search-box',
                                                    type: 'text',
                                                    id: 'xamoom-page-search-box',
                                                    name: 's'
                                                }
                                            ),
                                            el(
                                                'input',
                                                {
                                                    className: 'button',
                                                    type: 'button',
                                                    onClick: search,
                                                    value: i18n_search_pages
                                                }
                                            )
                                        ]
                                    ),
                                    el(
                                        'div',
                                        { className: props.className, id: 'xamoom-search-list' }
                                    ),
                                    el(
                                        'div',
                                        { className: props.className, id: 'xamoom-load-more' },
                                        el(
                                            'input',
                                            {
                                                className: 'button',
                                                type: 'button',
                                                onClick: loadmore,
                                                value: i18n_load_more
                                            }
                                        )
                                    )
                                ]

                            ),
                            el(
                                'div',
                                { className: props.className, id: 'xamoom-selected-page-container' },
                                el(
                                    'p',
                                    { className: props.className },
                                    [
                                        el(
                                                'input',
                                                {
                                                    className: 'button',
                                                    type: 'button',
                                                    onClick: reset,
                                                    value: 'x'
                                                }
                                        ),
                                        el(
                                            'span',
                                            { className: props.className, id: 'xamoom-selected-page-title' },
                                            'hallo'
                                        )
                                    ]
                                )
                            )
                        ]
                  );

		},

		// The "save" property must be specified and must be a valid function.
		save: function( props ) {
		    var attributes = props.attributes;
		    return "[xamoom lang=" + attributes.content_lang + " id=" + attributes.content_id + "]";
		},
	} );

	xamoomInit(true);
})();