<div class="form-group">
  {if is_set($label)}<label for="{$id}">{$label}</label>{/if}
  <input type="text" class="form-control" name="{$input_name}" id="{$id}" {if and( is_set($label)|not, is_set($placeholder) )}placeholder="{$placeholder}"{/if} value="{$value}">
</div>