{def $filterParameters = getFilterParameters( true() )
     $tmpSearch = fetch(ezfind,search,
                    hash(
                        'class_id', $class.id,
                        'facet', array(
                            hash( 'field', concat( $class.identifier, '/', $attribute.identifier ), 'limit', 1500 )
                        )
                    ))
    $options = $tmpSearch['SearchExtras']['facet_fields'][0]['nameList']|sort()
  }
<div class="search_form_attribute {$attribute.identifier}">
    <label for="{$attribute.identifier}">{$attribute.name}</label>
    <select id="{$attribute.identifier}" name="filter[{$class.identifier}/{$attribute.identifier}]">
        <option value="">Qualsiasi</option>
        {if $options}
            {def $selected = ''}
            {foreach $options as $counter}
                {set $selected = ''}
                {if is_set( $filterParameters[concat($class.identifier,'/',$attribute.identifier)] )}
                    {if eq($counter, $filterParameters[concat($class.identifier,'/',$attribute.identifier)].0 )}
                        {set $selected = 'selected="selected"'}
                    {/if}
                {/if}
                <option value="{$counter}" {$selected}>{$counter}</option>
            {/foreach}
        {/if}
    </select>
</div>