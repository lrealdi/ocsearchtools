{run-once}
{ezscript_require( array( 'ezjsc::jquery', 'ezjsc::jqueryUI' ) )}
{ezcss_require( array('datepicker.css') )}
<script type="text/javascript">
{literal}
$(function() {	
  $( ".calendar_picker" ).datepicker({
      defaultDate: "+1w",
      changeMonth: true,
      changeYear: true,
      dateFormat: "dd-mm-yy",
      numberOfMonths: 1
  });    
});
{/literal}
</script>
{/run-once}
<div class="form-group">
  {if is_set($label)}<label for="{$id}">{$label}</label>{/if}
  <input type="date" class="form-control calendar_picker" name="{$input_name}" id="{$id}" {if is_set($placeholder)}placeholder="{$placeholder}"{/if} value="{$value|wash()}">
</div>