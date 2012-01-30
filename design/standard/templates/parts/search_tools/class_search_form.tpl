{def $classFormSetting = ezini( 'SearchFormSettings', 'ClassFormSetting', 'site.ini')}

{def $class = fetch( 'content', 'class', hash( 'class_id', $class_identifier ) )
     $attribute_form_template = false()}
    {foreach $class.data_map as $attribute}
        {if $attribute.is_searchable}        
            {set $attribute_form_template = false()}             
            {if ezini_hasvariable( concat($class_identifier,'_AttributeFormSettings'), $attribute.identifier, 'site.ini' )}                
                {set $attribute_form_template = ezini( concat($class_identifier,'_AttributeFormSettings'), $attribute.identifier, 'site.ini' ) }               
                {if or( $attribute_form_template|not(), $attribute_form_template|eq('disabled') )}
                    {skip}
                {/if}
            {/if}
            {if $attribute_form_template|not()}
               {set $attribute_form_template = $attribute.data_type_string}
            {/if}
            {include name=concat('search_form_', $attribute.data_type_string)
                class=$class
                attribute=$attribute
                uri=concat('design:parts/search_tools/attribute_search_form/',$attribute_form_template,'.tpl')
                }    
        {/if}
    {/foreach}
    <div class="break"></div>
    