{*
    Template richiamato dalla funzione php ezjsSearchToolsFunctionsJS::facet_search
    Le variabili che si aspetta sono:
     * $nodeID = intero
     * $subtree = array di node_id
     * $facets = array di hash
     * $view_parameters = hash FacetName => valore, .., sort => sortString, query => text
*}

{*  imposto le variabili per la ricerca delle faccette *}
{def $node = fetch( 'content', 'node', hash( 'node_id', $nodeID ) )
     $page_limit = 1
     $filters = array()
     $query = ''}

{* controllo nei view_parameters se ci sono filtri attivi selezionati dalle faccette *}
{foreach $facets as $key => $value}
    {def $name = $value.name|urlencode }
    {if and( is_set( $view_parameters.$name ), $view_parameters.$name|ne( '' ) )}    
        {set $filters = $filters|append( concat( $value.field, ':', $view_parameters.$name ) )}
    {/if}
    {undef $name}
{/foreach}

{if and( is_set( $default_filters ), $default_filters|ne('') ) }
	{set $filters = $filters|merge( $default_filters )}
{/if}

{* controllo i view_parameters per la query text *}
{if and( is_set( $view_parameters.query ), $view_parameters.query|ne( '' ) )}
    {set $query = $view_parameters.query}
{/if}

{* nota: il view_parameters contiene anche il sort per generare i link delle faccette*}

{* fetch a solr *}
{def $search_hash = hash( 'subtree_array', $subtree,
                          'query', $query,
                          'facet', $facets,
                          'class_id', $classes,
                          'publish_date', $view_parameters.dateFilter,
                          'filter', $filters,
                          'limit', $page_limit)
     $search = fetch( ezfind, search, $search_hash )
     $search_result = $search['SearchResult']
     $search_count = $search['SearchCount']
     $search_extras = $search['SearchExtras']
     $search_data = $search
}

{def $viewParametersString = ''}
{foreach $view_parameters as $key => $param}
    {if $param|ne('')}
    {set $viewParametersString = concat( $viewParametersString, '/(', $key, ')/', $param )}
    {/if}
{/foreach}

{if $useDateFilter}
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

{if $search_count|gt(0)}

{if and ( $facets|count(), is_set( $search_extras.facet_fields ) )}
    {foreach $search_extras.facet_fields as $key => $facet}
        {def $name = $facets.$key.name|urlencode()}
        <ul class="menu-list">
        <input type="hidden" name="hiddenOptions" id="hiddenOptions" value='{$viewParametersString}'' />
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
    
{/if}