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
     $page_limit = 20
     $filters = array()
     $sort_by = hash()
     $query = ''}

{* controllo nei view_parameters se ci sono filtri attivi selezionati dalle faccette *}
{foreach $facets as $key => $value}
    {def $name = $value.name }
    {if and( is_set( $view_parameters.$name ), $view_parameters.$name|ne( '' ) )}
        {set $filters = $filters|append( concat( $value.field, ':', $view_parameters.$name ) )}
    {/if}
    {undef $name}
{/foreach}

{if and( is_set( $default_filters ), $default_filters|ne('') ) }
	{set $filters = $filters|merge( $default_filters )}
{/if}

{* parso il view_parameters.sort da stringa a hash *}
{if and( is_set( $view_parameters.sort ), $view_parameters.sort|ne( '' ) )}
    {def $sortArray = $view_parameters.sort|explode( '|' )} 
    {foreach $sortArray as $sortArrayPart}
        {def $sortArrayPartArray = $sortArrayPart|explode( '-' )}
        {set $sort_by = $sort_by|merge( hash( $sortArrayPartArray[0], $sortArrayPartArray[1] ) )}
        {undef $sortArrayPartArray}
    {/foreach}
    {undef $sortArray}
{/if}

{* controllo i view_parameters per la query text *}
{if and( is_set( $view_parameters.query ), $view_parameters.query|ne( '' ) )}
    {set $query = $view_parameters.query}
    {if $view_parameters.forceSort|ne(1)}
        {set $sort_by = hash( 'score', 'desc' )}
    {/if}
{/if}

{* fetch a solr *}
{def $search_hash = hash( 'subtree_array', $subtree,
                          'query', $query,
                          'class_id', $classes,
                          'filter', $filters,
                          'offset', $view_parameters.offset,
                          'publish_date', $view_parameters.dateFilter,
                          'sort_by', cond( $sort_by|count()|gt(0), $sort_by, false() ),
                          'spell_check', array( true() ),
                          'limit', $page_limit)
     $search = fetch( ezfind, search, $search_hash )
     $search_result = $search['SearchResult']
     $search_count = $search['SearchCount']
     $search_extras = $search['SearchExtras']
     $search_data = $search}

{if $search_count|gt(0)}

    {include name=navigator
         uri='design:navigator/google.tpl'
         page_uri=$node.url_alias
         item_count=$search_count
         view_parameters=$view_parameters
         item_limit=$page_limit}

    <div class="content-view-children">
        {foreach $search_result as $child }
            {node_view_gui view='line' content_node=$child}
        {/foreach}
    </div>
    
    {include name=navigator
         uri='design:navigator/google.tpl'
         page_uri=$node.url_alias
         item_count=$search_count
         view_parameters=$view_parameters
         item_limit=$page_limit}

{elseif $search_extras.spellcheck_collation}
    {def $spell_url= concat( $node.url_alias, $query|count_chars()|gt(0)|choose( '', concat( '/(query)/', $search_extras.spellcheck_collation|urlencode ) ))|ezurl}
    <h3>Cercavi <strong>{concat("<a class='spellcheck' href=",$spell_url,">")}{$search_extras.spellcheck_collation}</a></strong>?</h3>
{/if}