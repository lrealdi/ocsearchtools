{if and(is_set($attribute.content.class_constraint_list.0),is_set($attribute.content.default_placement.node_id))}

{def $filterParameters = getFilterParameters( true() )
     $tmpSearch = fetch(ezfind,search,
                    hash(
                        'subtree_array', array( $attribute.content.default_placement.node_id ),
                        'class_id', $attribute.content.class_constraint_list
                    ))
     $options = $tmpSearch['SearchResult']
}

    <div class="search_form_attribute {$attribute.identifier}">
        <label for="{$class.identifier}-{$attribute.identifier}">{$attribute.name}</label>
        {if $options}
        <ul id="{$class.identifier}-{$attribute.identifier}">            
            {def $selected = ''}
            {foreach $options as $option}
                {set $selected = ''}
                {if in_array_r( concat( '"', $option.name, '"'), $filterParameters[concat( $class.identifier, '/', $attribute.identifier, '/name')] ) }
                    {set $selected = 'checked="checked"'}
                {/if}
                <li><input type="checkbox" {$selected} value='{concat( '"', $option.name, '"')}' name="filter[{concat( $class.identifier, '/', $attribute.identifier, '/name')}][]" />{$option.name|wash()}</li>

            {/foreach}
            {undef $selected}
        </ul>
        {/if}
    </div>
{/if}
{undef $options}