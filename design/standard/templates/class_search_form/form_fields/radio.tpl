<div class="form-group">
  {if is_set($label)}<label for="{$id}">{$label}</label>{/if}

  {foreach $values as $value}
	<div class="radio">
	  <label>
		<input type="radio" class="form-control" name="{$input_name}" id="{$id}" {if $value.active}checked="checked"{/if} value="{$value.query}" />
		{$value.name}
	  </label>
	</div>
  {/foreach}
</div>

