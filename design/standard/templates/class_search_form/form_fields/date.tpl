<div class="form-group">
  {if is_set($label)}<label for="{$id}">{$label}</label>{/if}
  <input type="date" class="form-control" name="{$input_name}" id="{$id}" {if is_set($placeholder)}placeholder="{$placeholder}"{/if} value="{$value}">
</div>