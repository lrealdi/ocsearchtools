/*
{ezscript_require( array( 'ezjsc::jquery', 'ezjsc::jqueryio', 'jquery.classsearchform.js' ) )}
<script type="text/javascript">{literal}
$(document).ready(function(){
  $('#page-contents').classsearchform({
	paginationContainer:'.pagenavigator',
	pageUrl: "{/literal}{$node.url_alias}{literal}",
	pageLimit: "{/literal}{$page_limit}{literal}"
  });	  
});
{/literal}</script>
*/

;(function ( $, window, document, undefined ) {

    var pluginName = "classsearchform",
        defaults = {
            navigationContainer: ".navigation",
            paginationContainer: ".pagination",
            searchFormName: "form[name^='class_search_form_']",
            pageUrl: null,
            pageLimit: 10,
            ajaxUrl: '/ocsearch/action/1/'
        };

    function ClassSearchForm ( element, options ) {        
        this.element = element;
        this.settings = $.extend( {}, defaults, options );        
        if (this.settings.pageUrl != null) {
            this.init();
        }
    }

    ClassSearchForm.prototype = {
        init: function () {
            $(document).on( 'submit', this.settings.searchFormName, this.onSubmit );
        },        
        onSubmit: function (event) {
            var formData = $(event.currentTarget).closest( 'form' ).serializeArray();            
            //event.preventDefault();
        }
    };

    $.fn[ pluginName ] = function ( options ) {                
        this.each(function() {            
            if ( !$.data( this, "plugin_" + pluginName ) ) {
                $.data( this, "plugin_" + pluginName, new ClassSearchForm( this, options ) );                
            }
        });
        return this;
    };
})( jQuery, window, document );
