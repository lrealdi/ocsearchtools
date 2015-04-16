<div class="form-group">
  <label for="{$id}">{$label}</label>
  <div id="{$id}" style="padding: 10px 20px">
    <div id="{$id}-slider"></div>
    <p style="margin-top: 10px;">
      <small class="numeric-start"><strong>Da </strong><span></span></small>      
      <small class="numeric-end"><strong>a </strong><span></span></small>
      <input id="data-{$id}" type="text" name="{$input_name}" value="{$value|wash()}" class="form-control input-sm" style="display: inline; width: 100px; font-size: 0.8em;" />
    </p>
  </div>  
  {ezscript_require( array( 'ezjsc::jquery', 'ezjsc::jqueryUI', 'plugins/noUiSlider/jquery.nouislider.all.js' ) )}
  {ezcss_require(array('plugins/noUiSlider/jquery.nouislider.min.css'))}
  <script type="text/javascript">
  $(function() {ldelim}
    {literal}
    function setValue( value ){ $(this).html(Math.floor(value)); }
    {/literal}
    $( "#{$id}-slider" ).noUiSlider({ldelim}
      range: {ldelim}
        min: {$bounds.start_js},
        max: {$bounds.end_js}
      {rdelim},
      start: [ {$current_bounds.start_js}, {$current_bounds.end_js} ]{if $decimals},
      format: wNumb({ldelim}
        decimals: 0
      {rdelim}){/if}
    {rdelim});
    
    $("#{$id}-slider").Link('lower').to($("#{$id} .numeric-start span"), setValue);
    $("#{$id}-slider").Link('upper').to($("#{$id} .numeric-end span"), setValue);
    $("#{$id}-slider").on({ldelim}
      change: function(){ldelim}
        var range = $(this).val();
        $("#data-{$id}").val( Math.floor(range[0]) + '-' + Math.floor(range[1]) );
      {rdelim}
    {rdelim});
    
  {rdelim});
  </script>
</div>
