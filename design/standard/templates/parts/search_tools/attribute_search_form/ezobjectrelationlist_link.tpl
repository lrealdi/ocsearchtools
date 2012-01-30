{if and(is_set($attribute.content.class_constraint_list.0),is_set($attribute.content.default_placement.node_id))}

{def $filterParameters = getFilterParameters( true() )
     $tmpSearch = fetch(ezfind,search,
                    hash(
                        'subtree_array', array( $attribute.content.default_placement.node_id ),
                        'class_id', $attribute.content.class_constraint_list
                    ))
     $options = $tmpSearch['SearchResult']}            

        {if $options}      
            {foreach $options as $option}                   
                <a href={concat( $baseURI, '&filter[]=', $class.identifier, '/', $attribute.identifier, ':', $option.name|wash(), '&activeFacets[', $class.identifier, '/', $attribute.identifier, ':', $facetName, ']=', $option.name, $uriSuffix )|ezurl}>{$option.name|wash()}</a>,
            {/foreach}
        {/if}
   
{/if}
{undef $options}