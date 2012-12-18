<section class="content-view-folder-facet row">
{ezpagedata_set( 'left_menu', false() )}

{if is_set( $forceSort )|not()}
    {def $forceSort = 0}
{/if}

{if not( is_set( $default_filters ) )}
{def $default_filters = array()}
{/if}

{if not( is_set( $useDateFilter ) )}
{def $useDateFilter = false()}
{/if}

{if not( is_set( $container_size ) )}
{def $container_size = 8}
{/if}

{if not( is_set( $menu_size ) )}
{def $menu_size = 2}
{/if}

{def $content_size = $container_size|sub( $menu_size )}
{set $view_parameters = $view_parameters|merge( hash( 'contentSize', $content_size ) )}

{def $params = hash( 'node', $node,
                     'facets', $facets,
                     'default_filters', $default_filters,
                     'sortString', $sortString,
                     'classes', $classes,
                     'subtree', $subtree,
                     'forceSort', $forceSort,
                     'useDateFilter', $useDateFilter,
                     'view_parameters', $view_parameters )}

<div id="sidemenu" class="span{$menu_size}">
{include uri='design:menu/facet_left.tpl' name=facet_left params=$params}
</div>

{def $page_limit = 20
     $sort_by = hash()
     $query = ''
     $filters = array()}


{* controllo nei view_parameters se ci sono filtri attivi selezionati dalle faccette *}
{foreach $facets as $key => $value}
    {if $value|ne('')}
    {def $name = $value.name|urlencode }
    {if and( is_set( $view_parameters.$name ), $view_parameters.$name|ne( '' ) )}
        {set $filters = $filters|append( concat( $value.field, ':', $view_parameters.$name|urldecode ) )}
    {/if}
    {undef $name}
    {/if}
{/foreach}

{if and( is_set( $default_filters ), $default_filters|ne('') ) }
	{set $filters = $filters|merge( $default_filters )}
{/if}

{* controllo i view_parameters per il sort: se non c'è lo applico *}
{if is_set( $view_parameters.sort )|not()}
    {set $view_parameters = $view_parameters|merge( hash( 'sort', $sortString ) )}
{/if}

{if is_set( $view_parameters.forceSort )|not()}
    {set $view_parameters = $view_parameters|merge( hash( 'forceSort', $forceSort ) )}
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

{def $dateFilter=0}
{if and( is_set( $view_parameters.dateFilter ), $view_parameters.dateFilter|ne( '' ), $view_parameters.dateFilter|lt( 6 ) )}
    {set $dateFilter = $view_parameters.dateFilter}
{/if}

{* fetch a solr *}
{def $search_hash = hash( 'subtree_array', $subtree,
                          'query', $query,
                          'class_id', $classes,
                          'filter', $filters,
                          'offset', $view_parameters.offset,
                          'publish_date', $dateFilter,
                          'sort_by', cond( $sort_by|count()|gt(0), $sort_by, false() ),
                          'limit', $page_limit)
     $search = fetch( ezfind, search, $search_hash )
     $search_result = $search['SearchResult']
     $search_count = $search['SearchCount']
     $search_extras = $search['SearchExtras']
     $search_data = $search}


    <div class="class-folder span{$content_size}">  

        <div class="attribute-header">
            <h1>
                {$node.name|wash()}
            </h1>
        </div>

        <section class="content-view-children" id="children">
        {if $search_count|gt(0)}

            {include name=navigator
                 uri='design:navigator/google.tpl'
                 page_uri=$node.url_alias
                 item_count=$search_count
                 view_parameters=$view_parameters
                 item_limit=$page_limit}

            <div class="content-view-children">
                {foreach $search_result as $child }
                    {node_view_gui view='line' content_node=$child content_size=$content_size}
                {/foreach}
            </div>

            {include name=navigator
                 uri='design:navigator/google.tpl'
                 page_uri=$node.url_alias
                 item_count=$search_count
                 view_parameters=$view_parameters
                 item_limit=$page_limit}

        {/if}
        </section>

    </div>
</section>
