;(function ( $, window, document, undefined ) {

    var pluginName = "facetnavigation",
        defaults = {
            navigationContainer: ".nav-facets",            
            paginationContainer: ".pagination",
            contentContainer: ".facet-content",
            inputId: "searchfacet",
            template:{
                content: {
                    name: "parts/children-facet.tpl",
                    view: "line",
                    pageLimit: 10
                },
                navigation: "nav/nav-section-facet.tpl",
            },
            json: '',
            token: '',
            eZJsCoreCallMethod: 'ocst::facetnavigation'
        };
        
    var timeout;

    function FacetNavigation ( element, options ) {
        this.element = element;
        this.settings = $.extend( {}, defaults, options );
        this._defaults = defaults;
        this._name = pluginName;        
        this.selectedParameters = {};
        var currentParameters = {};
        $( this.settings.navigationContainer + ' a.active' ).each(function(){
            var key = $(this).data( 'key' ),
                value = $(this).data( 'value' );
            currentParameters[key] = value;
        });
        this.currentParameters = currentParameters;
        this.init();
    }

    FacetNavigation.prototype = {
        init: function () {            
            var self = this;                        
            var input = '#' + $(this.element).attr('id') + ' input#' + this.settings.inputId;
            var nav = '#' + $(this.element).attr('id') + ' ' + this.settings.navigationContainer + ' a, ' + '#' + $(this.element).attr('id') + ' ' + this.settings.paginationContainer + ' a';
            $(input).show();            
            $(document).on( 'keyup', input, self, this.onInput );            
            $(document).on( 'click', nav, self, this.onClick );
        },
        fetch: function(){                        
            var settings = this.settings;
            var  data = {
                json: settings.json,                
                token: settings.token,
                userParameters: $.extend( {}, this.currentParameters, this.selectedParameters ),
                template: settings.template
            }            
            $.ez( this.settings.eZJsCoreCallMethod, data, function( response ){
                if (response.error_text != '') {
                    alert(response.error_text);
                }else{
                    $( settings.navigationContainer ).replaceWith( response.content.navigation );
                    $( settings.contentContainer ).replaceWith( response.content.content );
                }
            });
        },
        onClick: function (event) {
            var self = event.data;
            if ( typeof $(event.target).data( 'key' ) !== 'undefined' ) {
                var key = $(event.target).data( 'key' ),
                    value = $(event.target).data( 'value' );
                if ( $(event.target).hasClass( 'active' ) ){                    
                    self.selectedParameters[key] = null;
                }
                else{                    
                    self.selectedParameters[key] = value;                
                }
            }else{                
                var parts = $(event.target).closest('a').attr( 'href' ).split('/(offset)/');                
                if (typeof parts[1] !== 'undefined' ) {
                    var splitParts = parts[1].split( '/' );
                    self.selectedParameters.offset = splitParts[0]; 
                }else{
                    self.selectedParameters.offset = null;
                }
            }
            self.fetch();
            event.preventDefault();
        },
        onInput: function (event) {            
            var self = event.data;
            var queryString = $(event.target).val();                        
            self.selectedParameters.query = queryString;
            if( timeout ) {
                clearTimeout( timeout );
                timeout = null;
            }
            var delay = function() { self.fetch(); };
            timeout = setTimeout(delay, 600);            
        },
    };

    $.fn[ pluginName ] = function ( options ) {                
        this.each(function() {            
            if ( !$.data( this, "plugin_" + pluginName ) ) {
                $.data( this, "plugin_" + pluginName, new FacetNavigation( this, options ) );                
            }
        });
        return this;
    };
})( jQuery, window, document );
