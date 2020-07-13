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
	var __ = wp.i18n.__;
	var el = wp.element.createElement;
	var registerBlockType = wp.blocks.registerBlockType;

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
          error: function(e){ //something went wrong (check console)
              console.error(i18n_generic_error, e);
          },
          processData: false,
          type: 'GET',
          url: xamoom_api_endpoint  + 'contents?' + jQuery.param(params)
        });
    }

    const iconEl = el('svg', { width: 24, height: 24 },
      el('path', { d: "m 22.5,0.7092993 c -0.56,-0.53 -1.27,-0.76 -2.13,-0.7 -0.56,0.06 -1.08,0.26 -1.58,0.6 -0.5,0.34 -1.05,0.7 -1.67,1.09 -0.62,0.39 -1.35,0.74 -2.2,1.07 -0.85,0.32 -1.91,0.49 -3.18,0.49 -1.3,0 -2.37,-0.15 -3.22,-0.46 -0.85,-0.31 -1.59,-0.66 -2.2,-1.04 -0.63,-0.4 -1.19,-0.77 -1.68,-1.12 -0.5,-0.36 -1.02,-0.56 -1.58,-0.63 -0.4,-0.03 -0.79,0.02 -1.16,0.14 -0.37,0.12 -0.7,0.32 -0.97,0.58 -0.28,0.26 -0.5,0.56 -0.67,0.9 -0.18,0.34 -0.26,0.71 -0.26,1.12 0,0.59 0.18,1.1 0.53,1.53 0.36,0.43 0.75,0.92 1.16,1.48 0.42,0.56 0.81,1.24 1.16,2.06 0.36,0.82 0.53,1.91 0.53,3.2699997 0,1.36 -0.18,2.46 -0.53,3.29 -0.36,0.83 -0.74,1.53 -1.16,2.09 -0.42,0.56 -0.8,1.04 -1.16,1.46 -0.35,0.42 -0.53,0.92 -0.53,1.51 0,0.8 0.29,1.48 0.88,2.02 0.59,0.54 1.32,0.78 2.18,0.72 0.56,-0.03 1.08,-0.22 1.58,-0.58 0.49,-0.36 1.05,-0.73 1.67,-1.11 0.62,-0.39 1.35,-0.74 2.2,-1.07 0.85,-0.32 1.93,-0.49 3.22,-0.49 1.27,0 2.33,0.16 3.18,0.46 0.85,0.31 1.58,0.67 2.2,1.07 0.62,0.4 1.18,0.77 1.67,1.11 0.49,0.34 1.02,0.54 1.58,0.6 0.9,0.09 1.63,-0.14 2.2,-0.7 0.57,-0.56 0.86,-1.24 0.86,-2.04 0,-0.59 -0.18,-1.09 -0.53,-1.51 -0.36,-0.42 -0.74,-0.9 -1.16,-1.46 -0.42,-0.56 -0.8,-1.25 -1.16,-2.09 -0.36,-0.83 -0.53,-1.93 -0.53,-3.29 0,-1.3599997 0.18,-2.4499997 0.53,-3.2699997 0.36,-0.82 0.74,-1.51 1.16,-2.06 0.42,-0.56 0.8,-1.05 1.16,-1.48 0.36,-0.43 0.53,-0.94 0.53,-1.53 -0.05,-0.83 -0.36,-1.51 -0.92,-2.03" } )
    );

	registerBlockType( 'xamoom/content', {
		title: 'xamoom',
		icon: iconEl,
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
                                            'loading'
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