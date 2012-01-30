{if and(is_set($attribute.content.class_constraint_list.0),is_set($attribute.content.default_placement.node_id))}

{def $filterParameters = getFilterParameters( true() )
     $tmpSearch = fetch(ezfind,search,
                    hash(
                        'subtree_array', array( $attribute.content.default_placement.node_id ),
                        'class_id', $attribute.content.class_constraint_list,
                        'limit', 100
                    ))
     $options = $tmpSearch['SearchResult']
}            

    <div class="search_form_attribute {$attribute.identifier}">
        <label>{$attribute.name}</label>
        <select name="filter[{concat( $class.identifier, '/', $attribute.identifier, '/name')}]" id="{$attribute.identifier}">
            <option value="">qualsiasi</option>
        {if $options}
            {def $selected = ''}
            {foreach $options as $option}
                {set $selected = ''}
                {if in_array_r( concat( '"', $option.name, '"'), $filterParameters[concat( $class.identifier, '/', $attribute.identifier, '/name')] ) }
                    {set $selected = 'selected="selected"'}
                {/if}
                <option value='{concat( '"', $option.name, '"')}' {$selected}>{$option.name|wash()}</option>
            {/foreach}
            {undef $selected}
        {/if}
        </select>
    </div>
{/if}
{undef $options}