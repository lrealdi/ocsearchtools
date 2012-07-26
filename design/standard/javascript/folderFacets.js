/*
esempio di oprions:
{ldelim}
    baseurl: "{$node.url_alias|ezurl( no, full )}",
    nodeID: "{$node.node_id}",
    subtree: "{$subtree|implode('::')}",
    facets: "{$facetStringArray|implode( '::' )}",
    classes: "{$classes|implode('::')}",        
    sort: "{$sortString}",
{rdelim};
*/

(function($) {
    
    $.folderFacets = function( options ){
        
        var xTriggered = 0;
        var timeout;
        var postDataCommon = [];
            postDataCommon.push( {'name': 'nodeID', 'value': options.nodeID} ); //intero
            postDataCommon.push( {'name': 'facets', 'value': options.facets} ); //stringa "subattr__test_t;Test;10;subattr__test_t;Test;10;...""
            postDataCommon.push( {'name': 'classes', 'value': options.classes} ); //stringa
            postDataCommon.push( {'name': 'subtree', 'value': options.subtree} );
        
        /* event handlers */
        
        var handlerClick = function(event){                        
            var postData = $.merge([], postDataCommon);            
            var url = $(event.target).is( 'a' ) ? $(event.target).prop( 'href' ).replace( options.baseurl, '' ) : $(event.target).parent().prop( 'href' ).replace( options.baseurl, '' );
            if ( url.length ){            
                if ( options.sort ){
                    var data = 'sort::' + options.sort + ';';
                }else{
                    var data = '' ;
                }            
                var parts = url.split('/');            
                for(var i=0;i<parts.length;i++) {
                        if( (i % 2) != 0 ){
                            var name = parts[i].replace( '(', '' ).replace( ')', '' );
                            var value = parts[i+1]
                            data += name + '::' + value + ';';
                        }
                }
                postData.push( {'name': 'view_parameters', 'value': data} );            
            }            
            $.ez( 'ocst::facet_search', postData, function( data ){ $('#children').html(data.content.result); $('#select').html(data.content.select) } );                    
            return false;
        }
        
        var handlerKeypress = function(event){
            var postData = $.merge([], postDataCommon);
            xTriggered++;
            if ( $('#clearSearch').is(':hidden') )
            {
                $('#clearSearch').show();
            }
            var url = '/(query)/'+$(event.target).val();
            if ( url.length == 9 )
            {
                $('#clearSearch').hide();
            }
            if ( url.length >= 12 || url.length == 9 ){                
                if ( options.sort ){
                    var data = 'sort::' + options.sort + ';';
                }else{
                    var data = '' ;
                }
                var parts = url.split('/');            
                for(var i=0;i<parts.length;i++) {
                        if( (i % 2) != 0 ){
                            var name = parts[i].replace( '(', '' ).replace( ')', '' );
                            var value = parts[i+1]
                            data += name + '::' + value + ';';
                        }
                }
                postData.push( {'name': 'view_parameters', 'value': data} );            
                $.ez( 'ocst::facet_search', postData, function( data ){ $('#children').html(data.content.result); $('#select').html(data.content.select) } );            
            }
        }
        
        /* event */
        
        $( '.submenu-list a, .pagenavigator a, a.spellcheck' ).live( 'click', function(e){ handlerClick(e); e.preventDefault() });
        
        $( '#query' ).bind( 'keypress', function(e){
            if( timeout ) {
                clearTimeout( timeout );
                timeout = null;
            }
            var delay = function() { handlerKeypress(e); };
            timeout = setTimeout(delay, 600);
        });
        
        $('#clearSearch').bind( 'click', function(e){
            $('#query').val('');            
            handlerKeypress(e);
        });
        
        $('#children').ajaxStart(function() { $(this).css( 'opacity', '0.3' ); }).ajaxStop(function() { $(this).css( 'opacity', '1' ); });

    }
    
})(jQuery);