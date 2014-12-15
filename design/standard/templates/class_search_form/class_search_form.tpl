{*
  helper OCClassSearchFormHelper
  class eZContentClass
  sort_form html tpl @todo
  published_form html tpl @todo
*}
<form name="{concat('class_search_form_',$helper.class.identifier)}" method="get" action="{'/ocsearch/action'|ezurl( 'no' )}">
    
  {include uri='design:class_search_form/query.tpl' helper=$helper input=$helper.query_field}
  
  {foreach $helper.attribute_fields as $input}	
	{attribute_search_form( $helper, $input )}
  {/foreach}
  
  {include uri='design:class_search_form/sort.tpl' helper=$helper input=$helper.sort_field}
  
  {include uri='design:class_search_form/publish_date.tpl' helper=$helper input=$helper.published_field}

  {foreach $parameters as $key => $value}
	<input type="hidden" name="{$key}" value="{$value}" />
  {/foreach}
  
  <input type="hidden" name="class_id" value="{$helper.class.id}" />  
  
  <button class="defaultbutton" type="submit">{'Search'|i18n('design/ocbootstrap/pagelayout')}</button>
</form>