<div class="form-group">
  {if is_set($label)}<label for="{$id}">{$label}</label>{/if}
  <div class="cloud text-center">  
  {foreach $values as $item}          
      <span style="white-space: nowrap">
        <input type="checkbox" style="display: inline" name="{$input_name}[]" id="{$id}" {if $item.active}checked="checked"{/if} value="{$item.query}" />
        <span style="white-space: nowrap;line-height:.5;{if $item.active}color:#f00{else}color:#333{/if};font-size:1.{1|mul($item.count)}em;"> {$item.raw_name|wash()}</span>
      </span>
  {/foreach}
  </div>
</div>