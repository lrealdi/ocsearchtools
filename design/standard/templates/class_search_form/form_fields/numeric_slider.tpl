<div class="form-group">
  <label for="{$id}">{$label}</label>
  <div id="{$id}" style="padding: 10px 20px">
    <div id="{$id}-slider"></div>
    <p style="margin-top: 10px;">
      <small class="numeric-start"><strong>Da </strong><span></span></small><br />
      <small class="numeric-end"><strong>a </strong><span></span></small>
    </p>
  </div>
  <input id="data-{$id}" type="hidden" name="{$input_name}" value="{$value|wash()}" />
  {ezscript_require( array( 'ezjsc::jquery', 'ezjsc::jqueryUI', 'plugins/noUiSlider/jquery.nouislider.all.js' ) )}
  {ezcss_require(array('plugins/noUiSlider/jquery.nouislider.min.css'))}
  <script type="text/javascript">
  $(function() {ldelim}
    {literal}
    function setValue( value ){ $(this).html(value); }
    {/literal}
    $( "#{$id}-slider" ).noUiSlider({ldelim}
      range: {ldelim}
        min: {$bounds.start_js},
        max: {$bounds.end_js}
      {rdelim},
      start: [ {$current_bounds.start_js}, {$current_bounds.end_js} ]
    {rdelim});
    
    $("#{$id}-slider").Link('lower').to($("#{$id} .numeric-start span"), setValue);
    $("#{$id}-slider").Link('upper').to($("#{$id} .numeric-end span"), setValue);
    $("#{$id}-slider").on({ldelim}
      change: function(){ldelim}
        var range = $(this).val();
        $("#data-{$id}").val( range[0] + '-' + range[1] );
      {rdelim}
    {rdelim});
    
  {rdelim});
  </script>
</div>
