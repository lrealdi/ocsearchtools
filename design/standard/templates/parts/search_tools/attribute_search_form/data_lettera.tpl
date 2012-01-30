                {if $attribute.identifier|eq('anno')}
                    
{def $filterParameters = getFilterParameters( true() )
     $tmpSearch = fetch(ezfind,search,
                    hash(
                        'class_id', $class.id,
                        'facet', array(
                            hash( 'field', concat( $class.identifier, '/', $attribute.identifier ), 'limit', 1500 )
                        )
                    ))
    $options = $tmpSearch['SearchExtras']['facet_fields'][0]['nameList']
  }
                    
                    <div class="float-left-date">
                        <label for="{$attribute.identifier}">{$attribute.name}</label>
                        <select id="{$attribute.identifier}" name="subfilter_arr[{$class.identifier}/{$attribute.identifier}]">
                            <option value="">Qualsiasi</option>
                        {foreach $options as $counter}
                            <option value="{$counter}">{$counter}</option>
                        {/foreach}
                        </select>
                    </div>
                    <div class="break"></div>
                {elseif $attribute.identifier|eq('giorno')}
                    <div class="float-left-date">
                        <label for="{$attribute.identifier}">{$attribute.name}</label>
                        <select id="{$attribute.identifier}" name="subfilter_arr[{$class.identifier}/{$attribute.identifier}]">
                            <option value="">Qualsiasi</option>
                        {for 1 to 31 as $counter}
                            <option {if is_set($subfilter_arr[concat($class.identifier,'/',$attribute.identifier)])|eq($counter)} class="marked" selected="selected" {/if} value="{$counter}">{$counter}</option>
                        {/for}
                        </select>
                    </div>
                {elseif $attribute.identifier|eq('mese')}
                    <div class="float-left-date">
                        <label for="{$attribute.identifier}">{$attribute.name}</label>
                        <select id="{$attribute.identifier}" name="subfilter_arr[{$class.identifier}/{$attribute.identifier}]">
                            <option value="">Qualsiasi</option>
                        {for 1 to 12 as $counter}
                            <option {if is_set($subfilter_arr[concat($class.identifier,'/',$attribute.identifier)])|eq($counter)} class="marked" selected="selected" {/if} value="{$counter}">{$counter}</option>
                        {/for}
                        </select>
                    </div>
                {/if}