<div class="form-group">
  <label for="{$id}"  style="margin-bottom: 40px">{$label}</label>
  <div style="padding: 0 20px">
    <div id="{$id}"></div>
  </div>
  <input id="data-{$id}" type="hidden" name="{$input_name}" value="{$value|wash()}" />
  {ezscript_require( array( 'ezjsc::jquery', 'ezjsc::jqueryUI', 'plugins/jqrangeslider/jQDateRangeSlider-min.js' ) )}
  {ezcss_require(array('plugins/jqrangeslider/ocbootstrap.css'))}
  <script type="text/javascript">
  $(function() {ldelim}
    $( "#{$id}" ).dateRangeSlider({ldelim}
      defaultValues:{ldelim}
        min: new Date({$current_bounds.start_js}),
        max: new Date({$current_bounds.end_js})
      {rdelim},
      bounds:{ldelim}
        min: new Date({$bounds.start_js}),
        max: new Date({$bounds.end_js})
      {rdelim}
    {rdelim});
    $("#{$id}").bind("userValuesChanged", function(e, data){ldelim}
      $("#data-{$id}").val( Math.floor(data.values.min.getTime()/1000) + '-' + Math.floor(data.values.max.getTime()/1000) );
    {rdelim});
  {rdelim});
  </script>
</div>