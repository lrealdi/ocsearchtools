{* carico js e css *}
{ezscript_require( array( 'ezjsc::jquery', 'ezjsc::jqueryio', 'folderFacets.js' ) )}
{ezcss_require( array( 'folderFacets.css' ) )}

{def $node = cond( is_set( $pagedata.persistent_variable.node ), $pagedata.persistent_variable.node, fetch( 'content', 'node', hash( 'node_id', $nodeID ) ) )
     $sortString = cond( is_set( $pagedata.persistent_variable.sortString ), $pagedata.persistent_variable.sortString, false() )
     $classes = cond( is_set( $pagedata.persistent_variable.classes ), $pagedata.persistent_variable.classes, array() )
     $subtree = cond( is_set( $pagedata.persistent_variable.subtree ), $pagedata.persistent_variable.subtree, array( $pagedata.extra_menu_node_id ) )
     $facets = cond( is_set( $pagedata.persistent_variable.facets ), $pagedata.persistent_variable.facets, array() )}
{* @TODO *}
{def $filters = array()
     $query = ''
     $page_limit = 1}    

{* controllo nei view_parameters se ci sono filtri attivi selezionati dalle faccette *}
{def $facetStringArray = array()}
{foreach $facets as $key => $value}
    {* preparo le faccette in forma di stringa ("subattr__test_t;Test;10") *}
    {set $facetStringArray = $facetStringArray|append( concat( $value.field, ';', $value.name, ';', $value.limit ) )}
    {def $name = $value.name|urlencode }
    {if and( is_set( $view_parameters.$name ), $view_parameters.$name|ne( '' ) )}    
        {set $filters = $filters|append( concat( $value.field, ':', $view_parameters.$name|urldecode ) )}
    {/if}
    {undef $name}
{/foreach}

{* controllo i view_parameters per la query text *}
{if and( is_set( $view_parameters.query ), $view_parameters.query|ne( '' ) )}
    {set $query = $view_parameters.query}
{/if}

{* controllo i view_parameters per il sort: se non c'è lo applico (non lo converto in hash perché in questa fetch non mi serve, serve per il js e per l'uristring) *}
{if and( $sortString, is_set( $view_parameters.sort )|not() )}
    {set $view_parameters = $view_parameters|merge( hash( 'sort', $sortString ) )}
{/if}

{* fetch a solr *}
{def $search_hash = hash( 'subtree_array', $subtree,
                          'query', $query,
                          'class_id', $classes,
                          'facet', $facets,
                          'filter', $filters,
                          'spell_check', array( true() ),
                          'limit', $page_limit)
     $search = fetch( ezfind, search, $search_hash )
     $search_result = $search['SearchResult']
     $search_count = $search['SearchCount']
     $search_extras = $search['SearchExtras']
     $search_data = $search
}

{* inizializzo ajax *}
<script type="text/javascript">
//<![CDATA[
$(function() {ldelim}  
    var options =
    {ldelim}
        baseurl: "{$node.url_alias|ezurl( no, full )}",
        nodeID: "{$node.node_id}",
        subtree: "{$subtree|implode('::')}",
        facets: "{$facetStringArray|implode( '::' )}",
        classes: "{$classes|implode('::')}",        
        sort: "{$sortString}",
    {rdelim};
    $.folderFacets( options );
{rdelim});
//]]>
</script>


<div class="border-box">
<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
<div class="border-ml"><div class="border-mr"><div class="border-mc">

    <h4><a href={$node.url_alias|ezurl}>{$node.name|wash()}</a></h4>
    
    <div class="block no-js-hide queryContainer">
        <label for="query">Ricerca libera:</label>
        <input class="box" size="30" name="query" id="query">
        <span id="clearSearch" style="display:none">x</span> 
    </div>
    
    <div id="select">
    {if and( $facets|count(), is_set( $search_extras.facet_fields ) )}
    {foreach $search_extras.facet_fields as $key => $facet}
        {def $name = $facets.$key.name|urlencode()}
        <ul class="menu-list">
        {if $facet.nameList|count()|gt(0)}
            <li><div><strong>{$facets.$key.name|explode( '_' )|implode( ' ' )|wash()}</strong></div>                
                <ul class="submenu-list">
                    {foreach $facet.nameList as $clean => $dash }                        
                        {def $currentstring = concat( '/(' , $name, ')/', $dash|urlencode() )
                             $uristring = $currentstring
                             $style = array()
                             $current = false()}
                        {foreach $view_parameters as $key2 => $value}
                            {if and( $value|ne(''), $key2|ne( $name ), $key2|ne( 'offset' ) )}
                                {set $uristring = concat( $uristring, '/(' , $key2, ')/', $value )}
                            {elseif and( $value|ne(''), $key2|eq( $name ), $value|eq( $dash ) )}
                                {set $style = $style|append( 'current')
                                     $current = true()}
                            {/if}
                        {/foreach}
                        
                        <li>
                            <div>                                
                                {if $current}
                                    {set $uristring = ''}
                                    {foreach $view_parameters as $key2 => $value}
                                        {if and( $value|ne(''), $key2|ne( $name ), $key2|ne( 'offset' ) )}
                                            {set $uristring = concat( $uristring, '/(' , $key2, ')/', $value )}                                        
                                        {/if}
                                    {/foreach}
                                    <a class="helper" href={concat( $node.url_alias, $uristring )|ezurl()} title="Rimuovi filtro"><small>Rimuovi filtro</small></a>
                                {/if}                                
                                <a {if $style|count()}class="{$style|implode( ' ' )}"{/if} href={concat( $node.url_alias, $uristring )|ezurl()}>
                                    {def $calcolate_name = false()}
                                    {if is_numeric( $clean )}
                                        {set $calcolate_name = true()}        
                                    {/if}
                                    {if $calcolate_name}
                                        {fetch( 'content', 'object', hash( 'object_id', $clean ) ).name|wash()|explode( '(')|implode( ' (' )|explode( ',')|implode( ', ' )}
                                    {else}    
                                        {$clean|wash()|explode( '(')|implode( ' (' )|explode( ',')|implode( ', ' )}
                                    {/if}
                                    ({$search_extras.facet_fields.$key.countList[$clean]})
                                    {undef $calcolate_name}
                                </a>
                            </div>
                        </li>
                        
                        {undef $uristring $style $current $currentstring}
                    {/foreach}
                </ul>
            </li>            
        {/if}
        </ul>
        {undef $name}
    {/foreach}
    {/if}
    </div>

</div></div></div>
<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
</div>
