{*

  Esempi di inclusione

  {include uri='design:datatable/view.tpl'
		   subtree=$parameters.subtree
		   classes=$parameters.classes
		   class_names=$parameters.class_names
		   fields=$parameters.fields
		   keys=$parameters.keys
		   filters=$parameters.filters
		   table_id=$parameters.table_id}
		   
  {include uri='design:datatable/view.tpl'
		   subtree=array( $node.object.main_node_id )
		   classes=array( 'persona', 'societa', 'dipendente' )
		   class_names=array( 'Persona', 'Organizzazione', 'Dipendente' )
		   fields=array( 'name', 'meta_class_name_ms', 'attr_email_t', 'main_url_alias' )
		   keys=array( 'Nome', 'Tipo', 'Email' )
		   filters=array()
		   table_id=address-book} 		   

*}

<div class='row'>
{ezscript_require(array( 'ezjsc::jquery','ezjsc::jqueryUI',
                         'plugins/datatables/jquery.dataTables.js',
                         'plugins/datatables/jquery.dataTables.columnFilter.js',
                         'plugins/datatables/jquery.dataTables.buttonFilter.js',
                         'plugins/datatables/dataTables.overrides.js') )}
{ezcss_require( array( 'plugins/datatables/bootstrap-datatable.css' ) )}

<div class='col-sm-12'>
<div class='box bordered-box orange-border' style='margin-bottom:0;'>
  <div class='box-content box-no-padding'>
    <div class='table-responsive'>
        <table id="table-{$table_id}" class='dt-column-filter table table-striped' style='margin-bottom:0;'>
          <thead>
            <tr>
              {foreach $keys as $key}
              <th>{$key}</th>
              {/foreach}
            </tr>
          </thead>          
          <tfoot>
            <tr>              
              {foreach $keys as $key}
              <th>{$key}</th>
              {/foreach}
            </tr>
          </tfoot>
        </table>
    </div>
  </div>
</div>
</div>

{literal}
<script type="text/javascript">
$(document).ready(function() {    
    var elem = '#table-{/literal}{$table_id}{literal}';
    var dataSource = {/literal}{concat('datatable/view/', $subtree|implode(','), '/', $classes|implode(','), '/', $fields|implode(','), , '/', $filters|implode(','))|ezurl()}{literal};
    if ($(elem).data("pagination-top-bottom") === true) {
        sdom = "<'row datatables-top'<'col-sm-6'l><'col-sm-6 text-right'pf>r>t<'row datatables-bottom'<'col-sm-6'i><'col-sm-6 text-right'p>>";
    } else if ($(elem).data("pagination-top") === true) {
        sdom = "<'row datatables-top'<'col-sm-6'l><'col-sm-6 text-right'pf>r>t<'row datatables-bottom'<'col-sm-6'i><'col-sm-6 text-right'>>";
    } else {
        sdom = "<'row datatables-top'<'col-sm-6'l><'col-sm-6 text-right'f>r>t<'row datatables-bottom'<'col-sm-6'i><'col-sm-6 text-right'p>>";
    }
    var dt = $(elem).dataTable({
        sDom: sdom,
        "iDisplayLength": 10,
        "bProcessing": true,
        "bServerSide": true,
        "sAjaxSource": dataSource,
        "sServerMethod": "GET",
        "oLanguage": {            
            "sProcessing": "",
            "sLengthMenu": "_MENU_ elementi per pagina",
            "sZeroRecords": "Oooops! Nessun risultato...",
            "sInfo": "Da _START_ a _END_ di _TOTAL_ elementi",
            "sInfoEmpty": "",
            "sSearch": "Cerca",
            "oPaginate": {
                "sFirst":    "Primo",
                "sPrevious": "Precedente",
                "sNext":     "Successivo",
                "sLast":     "Ultimo"
            }
        },
        "aoColumnDefs": [            
            {                
                "mRender": function ( data, type, row ) {
                    return '<a href="/'+ row[{/literal}{count($fields)|sub(1)}{literal}] + '">' + data + '</a>';
                },                
                "aTargets": [ 0 ]
            }
            {/literal}{foreach $fields as $key => $field}{if $field|begins_with('subattr')}
            ,{ldelim} "bSortable": false, "aTargets": [ {$key} ] {rdelim}
            {/if}{/foreach}{literal}
        ]
    });
    //dt.columnFilter({
    //    "sRangeFormat": "Da {from} a {to}",
    //    "aoColumns":[
    //        {/literal}{foreach $fields as $field}{if $field|eq('meta_class_name_ms')}{literal}
    //        {"type": "select", "values": [{/literal}{foreach $class_names as $class}"{$class}"{delimiter},{/delimiter}{/foreach}{literal}]},
    //        {/literal}{*{elseif $field|eq('published')}{literal}
    //        {"type": "date-range"},
    //        {/literal}*}{else}{literal}
    //        {"type": "text"},
    //        {/literal}{/if}{/foreach}{literal}            
    //    ]
    //});
    //dt.buttonFilter({
    //    targetsWrapper: ".dataTableFiltersWrapper",
    //    targetContainer: ".dataTableFilter",
    //    target: ".dataTableFilterTerm",
    //    field: ".dataTableFilterField",
    //    targetContainerSelectedClass: "contrast-background",
    //    columns: {/literal}[
    //    {def $done=false()}
    //    {foreach $fields as $key => $field}
    //        {ldelim}field:"{$field}", key:{$key}{rdelim}
    //        {delimiter},{/delimiter}
    //    {/foreach}        
    //    ]{literal}
    //});    
    $(elem).closest('.dataTables_wrapper').find('div[id$=_filter] input').css("width", "200px");
    $(elem).closest('.dataTables_wrapper').find('input, select').addClass("form-control input-sm").attr('placeholder', 'Cerca');    
});
</script>
{/literal}        

</div>