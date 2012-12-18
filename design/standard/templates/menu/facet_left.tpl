{* carico js e css *}
{ezscript_require( array( 'ezjsc::jquery', 'ezjsc::jqueryio', 'folderFacets.js' ) )}
{ezcss_require( array( 'folderFacets.css' ) )}

{*def $node = cond( is_set( $pagedata.persistent_variable.node ), $pagedata.persistent_variable.node, fetch( 'content', 'node', hash( 'node_id', $nodeID ) ) )
     $sortString = cond( is_set( $pagedata.persistent_variable.sortString ), $pagedata.persistent_variable.sortString, false() )
     $forceSort = cond( is_set( $pagedata.persistent_variable.forceSort ), $pagedata.persistent_variable.forceSort, 0 )
     $classes = cond( is_set( $pagedata.persistent_variable.classes ), $pagedata.persistent_variable.classes, array() )
     $subtree = cond( is_set( $pagedata.persistent_variable.subtree ), $pagedata.persistent_variable.subtree, array( $pagedata.extra_menu_node_id ) )
     $facets = cond( is_set( $pagedata.persistent_variable.facets ), $pagedata.persistent_variable.facets, array() )*}
{def $node = cond( is_set( $params.node ), $params.node, fetch( 'content', 'node', hash( 'node_id', $nodeID ) ) )
     $sortString = cond( is_set( $params.sortString ), $params.sortString, false() )
     $forceSort = cond( is_set( $params.forceSort ), $params.forceSort, 0 )
     $classes = cond( is_set( $params.classes ), $params.classes, array() )
     $subtree = cond( is_set( $params.subtree ), $params.subtree, array( $pagedata.extra_menu_node_id ) )
     $facets = cond( is_set( $params.facets ), $params.facets, array() )
     $view_parameters = cond( is_set( $params.view_parameters ), $params.view_parameters, array() )
     $default_filters = cond( is_set( $params.default_filters ), $params.default_filters, array() )
     $dateFilter = cond( and( $params.useDateFilter, is_set( $view_parameters.dateFilter ), $view_parameters.dateFilter|gt( 0 ), $view_parameters.dateFilter|lt( 6 ) ), $view_parameters.dateFilter, 0 )}     
{* @TODO *}
{def $filters = array()
     $query = ''
     $page_limit = 1}    

{* controllo nei view_parameters se ci sono filtri attivi selezionati dalle faccette *}

{def $facetStringArray = array()}
{foreach $facets as $key => $value}
    {if and( is_set( $value.field ), is_set( $value.name ), is_set( $value.limit ) )}
        {* preparo le faccette in forma di stringa ("subattr__test_t;Test;10") *}
        {set $facetStringArray = $facetStringArray|append( concat( $value.field, ';', $value.name, ';', $value.limit ) )}
        {def $name = $value.name|urlencode }
        {if and( is_set( $view_parameters.$name ), $view_parameters.$name|ne( '' ) )}    
            {set $filters = $filters|append( concat( $value.field, ':', $view_parameters.$name|urldecode ) )}
        {/if}
        {undef $name}
    {/if}
{/foreach}

{if and( $default_filters, $default_filters|ne('') ) }
	{set $filters = $filters|merge( $default_filters )}
{/if}

{* controllo i view_parameters per la query text *}
{if and( is_set( $view_parameters.query ), $view_parameters.query|ne( '' ) )}
    {set $query = $view_parameters.query}
{/if}

{* controllo i view_parameters per il sort: se non c'è lo applico (non lo converto in hash perché in questa fetch non mi serve, serve per il js e per l'uristring) *}
{if and( $sortString, is_set( $view_parameters.sort )|not() )}    
    {set $view_parameters = $view_parameters|merge( hash( 'sort', $sortString, 'forceSort', $forceSort ) )}
{/if}

{def $viewParametersString = ''}
{foreach $view_parameters as $key => $param}
    {if $param|ne('')}
    {set $viewParametersString = concat( $viewParametersString, '/(', $key, ')/', $param )}
    {/if}
{/foreach}

{* fetch a solr *}
{def $search_hash = hash( 'subtree_array', $subtree,
                          'query', $query,
                          'class_id', $classes,
                          'facet', $facets,
                          'filter', $filters,
                          'publish_date', $dateFilter,
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
        defaultFilters: '{$default_filters|implode(';')}',
        facets: "{$facetStringArray|implode( '::' )}",
        classes: "{$classes|implode('::')}",        
        sort: "{$sortString}",        
        useDateFilter: "{$params.useDateFilter|int()}",        
        forceSort: "{$forceSort}"
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
        <input class="box" size="30" name="query" id="query" />
        <span id="clearSearch" style="display:none">x</span> 
    </div>
    
    <div id="select">
    <input type="hidden" name="hiddenOptions" id="hiddenOptions" value='{$viewParametersString}' />
        
    {if $params.useDateFilter}    
        {def $dateString = ''
             $dateStyle = ''}
        {foreach $view_parameters as $key2 => $value}
            {if and( $value|ne(''), $key2|ne( 'offset' ) )}
                {set $dateString = concat( $dateString, '/(' , $key2, ')/', $value )}
            {/if}
        {/foreach}
        
        {def $dateFilters = hash( 1, "Last day", 2, "Last week", 3, "Last month", 4, "Last three months", 5, "Last year" )}
        
        <ul class="menu-list"> 
            <li><div><strong>{'Creation time'|i18n( 'extension/ezfind/facets' )}</strong></div>  
            <ul class="submenu-list">
                {if and( is_set( $view_parameters.dateFilter ), $view_parameters.dateFilter|gt( 0 ), $view_parameters.dateFilter|lt( 6 ) )}
                    <li><div>                
                        {set $dateString = $dateString|explode( concat( '/(dateFilter)/', $view_parameters.dateFilter ) )|implode( '' )
                             $dateStyle = 'current'}
                        <a class="helper" href={concat( $node.url_alias, $dateString )|ezurl()} title="Rimuovi filtro"><small>Rimuovi filtro</small></a>
                        <a class="{$dateStyle}" href={concat( $node.url_alias, $dateString )|ezurl}>{$dateFilters[$view_parameters.dateFilter]|i18n("design/standard/content/search")}</a>
                    </div></li>
                {else}
                    {foreach $dateFilters as $index => $date}
                        <li><div>
                        <a href={concat( $node.url_alias, $dateString, '/(dateFilter)/', $index )|ezurl}>{$date|i18n("design/standard/content/search")}</a>
                        </div></li>
                    {/foreach}
                {/if}
            </ul>
        </li></ul> 
    {/if}   

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
                                        {if $facets.$key.field|eq( 'meta_main_parent_node_id_si' )}
                                        {fetch( 'content', 'node', hash( 'node_id', $clean ) ).name|wash()|explode( '(')|implode( ' (' )|explode( ',')|implode( ', ' )}
                                        {else}
                                        {fetch( 'content', 'object', hash( 'object_id', $clean ) ).name|wash()|explode( '(')|implode( ' (' )|explode( ',')|implode( ', ' )}
                                        {/if}
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
