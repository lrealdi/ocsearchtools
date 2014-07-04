<div class="form-group">
  {if is_set($label)}<label for="{$id}">{$label}</label>{/if}
  <select class="form-control" name="{$input_name}" id="{$id}">
	<option value=""></option>
	{foreach $values as $value}	  
	  <option value="{$value.query}" {if $value.active}selected="selected"{/if}>{$value.name}</option>
	{/foreach}
  </select>
</div>