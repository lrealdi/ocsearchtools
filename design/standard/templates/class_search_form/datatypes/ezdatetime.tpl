{include uri = 'design:class_search_form/form_fields/date_slider.tpl'
        label = $input.class_attribute.name
        placeholder = 'gg-mm-aaaa'
        value = $input.value
        input_name = $input.name
        bounds = $input.bounds
        current_bounds = $input.current_bounds
        id = concat('search-for-',$input.id)}