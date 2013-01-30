{ezscript_require( array( 'ezjsc::jquery', 'jquery.ba-bbq.min.js' ) )}
<script type="text/javascript">
{literal}
$(document).ready(function(){
    var pageUrl = "{/literal}{$node.url_alias|ezurl(no)}{/literal}";
    $(window).bind( 'hashchange', function(e) {
        $("#children").css( 'opacity', '0.3' );
        var href = $.bbq.getState('page');
         $.get(href, function(data) {
            var children = $('#children', data).html();
            var select = $('#select', data).html();
            $("#children").html(children);
            $("#select").html(select);
            $("#children").css( 'opacity', '1' );
            lineCompact();
        });        
    });
    $(window).trigger( 'hashchange' );
    $( '#facetsearch .facet-list a, #facetsearch .pagenavigator a, #facetsearch a.spellcheck, #facetsearch a.helper' ).live( 'click', function(event){        
        if (parseInt(jQuery.fn.jquery.split('.').join('')) > 159)
            var href = $(event.currentTarget).is( 'a' ) ? $(event.currentTarget).prop( 'href' ): $(event.currentTarget).parent().prop( 'href' );
        else
            var href = $(event.currentTarget).is( 'a' ) ? $(event.currentTarget).attr( 'href' ): $(event.currentTarget).parent().attr( 'href' );                            
        $.bbq.pushState({ page: href });       
        return false;
    });    
})
{/literal}
</script>

{if is_set( $forceSort )|not()}
    {def $forceSort = 0}
{/if}

{if not( is_set( $default_filters ) )}
{def $default_filters = array()}
{/if}

{if not( is_set( $useDateFilter ) )}
{def $useDateFilter = false()}
{/if}

{def $page_limit = 20
     $sort_by = hash()
     $query = ''
     $filters = array()}

{if is_set( $view_parameters['Tipologia_di_contenuto'] )}
    {def $attributi_da_escludere_dalla_ricerca = ezini( 'GestioneAttributi', 'attributi_da_escludere_dalla_ricerca', 'content.ini' )
         $selected_class = fetch( 'content', 'class', hash( 'class_id', $view_parameters['Tipologia_di_contenuto'] ) )
         $selected_class_attributes = fetch( 'content', 'class_attribute_list', hash( 'class_id', $selected_class.id )) }
    {foreach $selected_class_attributes as $attribute}
        {if and( $attribute.is_searchable, $attribute.identifier|ne('errors'), $attributi_da_escludere_dalla_ricerca|contains( $attribute.identifier )|not() )}
            {def $do = true()}
            {foreach $default_filters as $default_filter}                                
                {if $default_filter|implode(' ')|contains( concat( 'submeta_', $attribute.identifier, '___id_si:' ) )}
                    {set $do = false()}
                    {break}
                {/if}
            {/foreach}
            {foreach $facets as $facet}
                {if $facet.field|eq( concat( 'submeta_', $attribute.identifier, '___id_si' ) )}
                    {set $do = false()}
                    {break}
                {/if}
            {/foreach}
            {if $do}            
                {switch match=$attribute.data_type_string}
                    {case in=array('ezobjectrelationlist')}
                        {set $facets = $facets|append( hash( 'field', concat( 'submeta_', $attribute.identifier, '___id_si' ),
                                                             'name', $attribute.name|explode( ' ' )|implode( '_' )|explode( ':' )|implode( '' ),
                                                             'limit', 10000,
                                                             'sort', 'alpha' ) )}
                    {/case}
                    {case}{/case}
                {/switch}
            {/if}
            {undef $do}
        {/if}
    {/foreach}
{/if}

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
                          'facet', $facets,
                          'filter', $filters,
                          'offset', $view_parameters.offset,
                          'publish_date', $dateFilter,
                          'sort_by', cond( $sort_by|count()|gt(0), $sort_by, false() ),
                          'spell_check', array( true() ),
                          'limit', $page_limit)
     $search = fetch( ezfind, search, $search_hash )
     $search_result = $search['SearchResult']
     $search_count = $search['SearchCount']
     $search_extras = $search['SearchExtras']
     $search_data = $search}


<div class="global-view-full content-view-full">
    <div class="attribute-header">
        <h1>{$node.name|wash()}</h1>
    </div>

    {def $current_node_path3 = fetch(content,node,hash(node_id,$node.path_array[3]))}
    {def $is_area_tematica = false()}
    {if $current_node_path3.class_identifier|eq('area_tematica')}
        {set $is_area_tematica = true()}
    {/if}
    
    {def $oggetti_correlati_centro = ezini( 'DisplayBlocks', 'oggetti_correlati_centro', 'content.ini')
         $oggetti_senza_label = array('collegamenti', 'descrizione')
         $attributi_da_escludere = ezini( 'GestioneAttributi', 'attributi_da_escludere', 'content.ini')
         $attributi_da_evidenziare = ezini( 'GestioneAttributi', 'attributi_da_evidenziare', 'content.ini')
         $attributi_a_destra = ezini( 'GestioneAttributi', 'attributi_a_destra', 'content.ini')}
    
    
    <div class="attributi-principali float-break col col-notitle">
    <div class="col-content"><div class="col-content-design">                
        {if and( is_set( $node.data_map.abstract ), $node.data_map.abstract.has_content )}
            {attribute_view_gui is_area_tematica=$is_area_tematica attribute=$node.data_map.abstract}
        {/if}
    </div></div>
    </div>
    
    
    <div class="attributi-base">
    {def $style='col-odd'}
        {foreach $node.object.contentobject_attributes as $attribute}
        {if $attribute.has_content}
            {if $attributi_da_escludere|contains($attribute.contentclass_attribute_identifier)|not()}
                {if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
                {if $oggetti_senza_label|contains($attribute.contentclass_attribute_identifier)|not()}
                   <div class="{$style} col float-break attribute-{$attribute.contentclass_attribute_identifier}">
                        <div class="col-title"><span class="label">{$attribute.contentclass_attribute_name}</span></div>
                        <div class="col-content"><div class="col-content-design">
                            {attribute_view_gui is_area_tematica=$is_area_tematica attribute=$attribute}
                        </div></div>
                   </div>
                {else}
                   <div class="{$style} col col-notitle float-break attribute-{$attribute.contentclass_attribute_identifier}">
                    <div class="col-content"><div class="col-content-design">
                        {attribute_view_gui is_area_tematica=$is_area_tematica attribute=$attribute}
                    </div></div>
                   </div>
                {/if}
            {/if}			
        {/if}
    {/foreach}
    </div>
</div>


<div class="columns-blog float-break extrainfo" id="facetsearch">
    <div class="main-column-position">
        <div class="main-column float-break">
            <div class="border-box">
    
            <div id="children">
            {if $search_count|gt(0)}
    
                {include name=navigator
                     uri='design:navigator/google.tpl'
                     page_uri=$node.url_alias
                     item_count=$search_count
                     view_parameters=$view_parameters
                     item_limit=$page_limit}
    
                <div class="content-view-children">
                    {foreach $search_result as $child sequence array( 'col-odd', 'col-even' ) as $_style }                
                    <div class="{$_style} col col-notitle float-break">
                    <div class="col-content"><div class="col-content-design">
                        {node_view_gui view='line' content_node=$child}
                    </div></div>
                    </div>
                    {/foreach}
                </div>
    
                {include name=navigator
                     uri='design:navigator/google.tpl'
                     page_uri=$node.url_alias
                     item_count=$search_count
                     view_parameters=$view_parameters
                     item_limit=$page_limit}
    
            {else}
                <div class="message-warning warning">Nessun dato corrispondente al filtro selezionato</div>
            {/if}
            </div>
            
            </div>
        </div>
    </div>
    
    <div class="extrainfo-column-position">
        <div class="extrainfo-column" id="extrainfo">
            <div class="border-box">
    
                <div id="select">
                {if useDateFilter}    
                    {def $dateString = ''
                         $dateStyle = ''}
                    {foreach $view_parameters as $key2 => $value}
                        {if and( $value|ne(''), $key2|ne( 'offset' ) )}
                            {set $dateString = concat( $dateString, '/(' , $key2, ')/', $value )}
                        {/if}
                    {/foreach}
                    
                    {def $dateFilters = hash( 1, "Last day", 2, "Last week", 3, "Last month", 4, "Last three months", 5, "Last year" )}
                    
                    <h3 class="block-title">{'Creation time'|i18n( 'extension/ezfind/facets' )}</h3>
                    <div class="square-box-soft-gray extrainfo-box">
                    <ul class="facet-list">
                        {if and( is_set( $view_parameters.dateFilter ), $view_parameters.dateFilter|gt( 0 ), $view_parameters.dateFilter|lt( 6 ) )}
                            <li><div class="current">                
                                {set $dateString = $dateString|explode( concat( '/(dateFilter)/', $view_parameters.dateFilter ) )|implode( '' )
                                     $dateStyle = 'current'}
                                <a class="helper" href="{concat( $node.url_alias, $dateString )|ezurl(no, full)}" title="Rimuovi filtro"><small>[x]</small></a>
                                <a class="{$dateStyle}" href="{concat( $node.url_alias, $dateString )|ezurl(no, full)}">{$dateFilters[$view_parameters.dateFilter]|i18n("design/standard/content/search")}</a>
                            </div></li>
                        {else}
                            {foreach $dateFilters as $index => $date}
                                <li><div>
                                <a href="{concat( $node.url_alias, $dateString, '/(dateFilter)/', $index )|ezurl(no, full)}">{$date|i18n("design/standard/content/search")}</a>
                                </div></li>
                            {/foreach}
                        {/if}
                    </ul>
                    </div>
                {/if}   
            
                
                {if and( $facets|count(), is_set( $search_extras.facet_fields ) )}
                {foreach $search_extras.facet_fields as $key => $facet}
                    {def $name = $facets.$key.name|urlencode()}
                        
                    {if $facet.nameList|count()|gt(0)}
                        <h3 class="block-title">{$facets.$key.name|explode( '_' )|implode( ' ' )|wash()}</h3>
                        <div class="square-box-soft-gray extrainfo-box"> 
                            <ul class="facet-list">
                                {foreach $facet.nameList as $clean => $dash }                    
                                    {def $currentstring = concat( '/(' , $name, ')/', $dash|urlencode() )
                                         $uristring = $currentstring
                                         $cssstyle = array()
                                         $current = false()}
                                    {foreach $view_parameters as $key2 => $value}
                                        {if and( $value|ne(''), $key2|ne( $name ), $key2|ne( 'offset' ) )}
                                            {set $uristring = concat( $uristring, '/(' , $key2, ')/', $value )}
                                        {elseif and( $value|ne(''), $key2|eq( $name ), $value|eq( $dash ) )}
                                            {set $cssstyle = $cssstyle|append( 'current')
                                                 $current = true()}
                                        {/if}                                        
                                    {/foreach}
                                    
                                    {if and( $current|not(), $search_extras.facet_fields.$key.countList[$clean]|eq($search_count) )}
                                        {set $cssstyle = $cssstyle|append( 'current current-inherit')}
                                    {/if}
                                    
                                    <li>
                                        <div {if $cssstyle|count()}class="{$cssstyle|implode( ' ' )}"{/if}>                                
                                            {if $current}
                                                {set $uristring = ''}
                                                {foreach $view_parameters as $key2 => $value}
                                                    {if and( $value|ne(''), $key2|ne( $name ), $key2|ne( 'offset' ) )}
                                                        {set $uristring = concat( $uristring, '/(' , $key2, ')/', $value )}                                        
                                                    {/if}
                                                {/foreach}
                                                <a class="helper" href="{concat( $node.url_alias, $uristring )|ezurl(no, full)}" title="Rimuovi filtro"><small>[x]</small></a>
                                            {/if}                                
                                            <a {if $cssstyle|count()}class="{$cssstyle|implode( ' ' )}"{/if} href="{concat( $node.url_alias, $uristring )|ezurl(no, full)}">
                                                {def $calcolate_name = false()}
                                                {if or( is_numeric( $clean ), $facets.$key.field|eq( 'meta_class_identifier_ms' ) )}
                                                    {set $calcolate_name = true()}        
                                                {/if}
                                                {if $calcolate_name}
                                                    {if $facets.$key.field|eq( 'meta_class_identifier_ms' )}
                                                    {fetch( 'content', 'class', hash( 'class_id', $clean ) ).name|wash()|explode( '(')|implode( ' (' )|explode( ',')|implode( ', ' )}
                                                    {elseif $facets.$key.field|eq( 'meta_main_parent_node_id_si' )}
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
                                    
                                    {undef $uristring $cssstyle $current $currentstring}
                                {/foreach}
                            </ul>
                        </div>
                    {/if}
                    {undef $name}
                {/foreach}
                {/if}
                </div>

            
            </div>
        </div>
    </div>

</div>
