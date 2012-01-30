 DEBUG START

{def $test = getFilterParameters()}
{$test|attribute(show,2)}

---

{def $test2 = getFilterParameters(true())}
{$test2|attribute(show,2)}

END DEBUG 

{if $use_template_search}
    {def $class_identifier = 'letter'
         $search = false()}

    {set $page_limit=40}

    {*switch match=$search_page_limit}
    {case match=1}
        {set $page_limit=5}
    {/case}
    {case match=2}
        {set $page_limit=10}
    {/case}
    {case match=3}
        {set $page_limit=20}
    {/case}
    {case match=4}
        {set $page_limit=30}
    {/case}
    {case match=5}
        {set $page_limit=50}
    {/case}
    {case}
	{/case}
    {/switch*}
    
    {def $dateFilter=0}
    {if ezhttp_hasvariable( 'dateFilter', 'get' )}
        {set $dateFilter = ezhttp( 'dateFilter', 'get' )}
		{switch match=$dateFilter}
		   {case match=1}
		      {def $dateFilterLabel="Last day"|i18n("design/standard/content/search")}
		   {/case}
           {case match=2}
              {def $dateFilterLabel="Last week"|i18n("design/standard/content/search")}
           {/case}
           {case match=3}
              {def $dateFilterLabel="Last month"|i18n("design/standard/content/search")}
           {/case}      
           {case match=4}
              {def $dateFilterLabel="Last three months"|i18n("design/standard/content/search")}
           {/case}      
           {case match=5}
              {def $dateFilterLabel="Last year"|i18n("design/standard/content/search")}
           {/case}                 
		{/switch}        
    {/if} 

    {def $activeFacetParameters = array()}
    {if ezhttp_hasvariable( 'activeFacets', 'get' )}
        {set $activeFacetParameters = ezhttp( 'activeFacets', 'get' )}
    {/if}

    
<div class="content-advancedsearch">
<form action={"/content/advancedsearch/"|ezurl} method="get">
    
    <div class="border-box">
    <div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
    <div class="border-ml"><div class="border-mr"><div class="border-mc float-break">
        <div class="attribute-header">
            <h1 class="long">{'Advanced search'|i18n( 'design/ezwebin/content/advancedsearch' )}</h1>
        </div>
        
        <div id="search_text_fields">
            <div class="block">
                <label for="search_text">{'Search all the words'|i18n( 'design/ezwebin/content/advancedsearch' )}</label>
                <input id="search_text" class="box" type="text" size="40" name="SearchText" value="{$full_search_text|wash}" />
            </div>
            <div class="block">
                <label for="search_phrase">{'Search the exact phrase'|i18n( 'design/ezwebin/content/advancedsearch' )}</label>
                <input id="search_phrase" class="box" type="text" size="40" name="PhraseSearchText" value="{$phrase_search_text|wash}" />
            </div>  
        </div>
        
        <div class="buttonblock object-right">
            <a class="button" href={"content/advancedsearch"|ezurl}>Nuova ricerca</a>
            <input class="button" type="reset" name="ResetButton" value="{'Reset'|i18n('design/ezwebin/content/advancedsearch')}" />
            <input class="defaultbutton" type="submit" name="SearchButton" value="{'Search'|i18n('design/ezwebin/content/advancedsearch')}" />
        </div>    
    </div></div></div>
    <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
    </div>     

    <div class="columns-advancedsearch float-break">
        <div class="main-column-position">
            <div class="main-column float-break">
                <div class="border-box">
                <div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
                <div class="border-ml"><div class="border-mr"><div class="border-mc float-break">   
                    
                    <div id="search_attribute_fields" class="block">
                        
                        {include name=search_form
                             class_identifier=$class_identifier
                             uri='design:parts/search_tools/class_search_form.tpl'         
                             }
                                                
                        <div class="buttonblock object-right">
                            <input class="button" type="reset" name="ResetButton" value="{'Reset'|i18n('design/ezwebin/content/advancedsearch')}" />
                            <input class="defaultbutton" type="submit" name="SearchButton" value="{'Search'|i18n('design/ezwebin/content/advancedsearch')}" />
                        </div>  
                    
                    </div>
                    
                    {def $filterParameters = getFilterParameters()
                         $search_hash = hash(
                                            'query' , $search_text,
                                            'section_id',$search_section_id,
                                            'subtree_array',$search_sub_tree,
                                            'class_id',$search_contentclass_id,
                                            'offset',$view_parameters.offset,
                                            'filter', $filterParameters,
                                            'publish_date',$search_date,
                                            'limit',$page_limit,
                                            'sort_by', hash('attr_anno_s', 'asc', 'attr_mese_s', 'asc')
                                        )
                    }
                    {set $search=fetch( ezfind, search, $search_hash)}
                
                    {set $search_result=$search['SearchResult']}
                    {set $search_count=$search['SearchCount']}
                    {set $stop_word_array=$search['StopWordArray']}
                    {set $search_data=$search}
                
                
                    {def $uriSuffix = ''}
                    {foreach $activeFacetParameters as $facetField => $facetValue}
                        {set $uriSuffix = concat( $uriSuffix, '&activeFacets[', $facetField, ']=', $facetValue )}
                    {/foreach}
                    
                    {set $uriSuffix = concat( $uriSuffix, $filterParameters|getFilterUrlSuffix() )}
                    
                    {if gt( $dateFilter, 0 )}
                        {set $uriSuffix = concat( $uriSuffix, '&dateFilter=', $dateFilter )}
                    {/if}    

                </div></div></div>
                <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
                </div>   
            </div>
        </div>

        <div class="extrainfo-column-position">
            <div class="extrainfo-column">
                <div class="border-box">
                <div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
                <div class="border-ml"><div class="border-mr"><div class="border-mc float-break">   
    
                    {if or( $search_text, eq( ezini( 'SearchSettings', 'AllowEmptySearch', 'site.ini'), 'enabled' ) )}
                    <br/>
                    {switch name=Sw match=$search_count}
                      {case match=0}
                    <div class="search-message warning">
                    <h2>{'No results were found when searching for "%1"'|i18n( 'design/ezwebin/content/advancedsearch', , array( $search_text|wash ) )}</h2>
                    </div>
                      {/case}
                      {case}
                    <div class="search-message message-feedback">
                    <h2>La ricerca ha prodotto {$search_count} risultati</h2>                    
                    </div>
                      {/case}
                    {/switch}
                    
                    {include name=navigator
                             uri='design:navigator/google.tpl'
                             page_uri='/content/advancedsearch'
                             page_uri_suffix=concat('?SearchText=',$search_text|urlencode,$search_sub_tree|gt(0)|choose( '', concat( '&', 'SubTreeArray[]'|urlencode, '=', $search_sub_tree|implode( concat( '&', 'SubTreeArray[]'|urlencode, '=' ) ) ) ),$search_timestamp|gt(0)|choose('',concat('&SearchTimestamp=',$search_timestamp)), $uriSuffix )
                             item_count=$search_count
                             view_parameters=$view_parameters
                             item_limit=$page_limit}
                             
                    <table class="list">
                    <tr>
                        <th>Numero</th>
                        <th>Data</th>
                        <th>Data topica</th>
                        <th>Luogo</th>
                        <th>Mittente</th>
                        <th>Destinatario</th>
                        {*<th>Segnatura</th>*}
                        <th>Tipologia</th>
                        <th>Archivio</th>
                    </tr>
                    {if $search_result|count()}
                        {foreach $search_result as $element sequence array('bglight', 'bgdark') as $style}
                            {node_view_gui view=table_line content_node=$element style=$style}
                        {/foreach}
                    {/if}
                    </table>
                    
                    {/if}
                    
                    {include name=navigator
                             uri='design:navigator/google.tpl'
                             page_uri='/content/advancedsearch'
                             page_uri_suffix=concat('?SearchText=',$search_text|urlencode,$search_sub_tree|gt(0)|choose( '', concat( '&', 'SubTreeArray[]'|urlencode, '=', $search_sub_tree|implode( concat( '&', 'SubTreeArray[]'|urlencode, '=' ) ) ) ),$search_timestamp|gt(0)|choose('',concat('&SearchTimestamp=',$search_timestamp)), $uriSuffix )
                             item_count=$search_count
                             view_parameters=$view_parameters
                             item_limit=$page_limit}
                                        

                </div></div></div>
                <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
                </div>    
            </div>
        </div>
    
    </div>
    
</form>    
</div>

{/if}