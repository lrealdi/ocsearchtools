{def $filterParameters = getFilterParameters( true() )}

<div class="search_form_attribute {$attribute.identifier}">
    <label for="{$attribute.identifier}">{$attribute.name}</label>
    <input id="{$attribute.identifier}"
    type="text" name="filter[{$class.identifier}/{$attribute.identifier}]" value="{if is_set( $filterParameters[concat($class.identifier,'/',$attribute.identifier)] )}{$filterParameters[concat($class.identifier,'/',$attribute.identifier)][0]}{/if}" />
</div>