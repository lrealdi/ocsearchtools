{include uri = 'design:class_search_form/form_fields/date_range.tpl'
		 label = $input.label
		 placeholder = $input.label
		 value = $input.value
     bounds = $input.bounds
     current_bounds = $input.current_bounds
		 input_name = $input.name
		 id = concat('search-for-',$helper.class.identifier,'-',$input.id)}