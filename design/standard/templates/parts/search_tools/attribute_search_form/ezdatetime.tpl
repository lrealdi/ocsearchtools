<div class="search_form_attribute {$attribute.identifier}">
    <label for="{$attribute.identifier}">{$attribute.name}</label>
    <input id="{$attribute.identifier}"
    type="text" name="subfilter_arr[{$class.identifier}/{$attribute.identifier}]" value="{if is_set($subfilter_arr[concat($class.identifier,'/',$attribute.identifier)])}{$subfilter_arr[concat($class.identifier,'/',$attribute.identifier)]}{/if}" />
</div>