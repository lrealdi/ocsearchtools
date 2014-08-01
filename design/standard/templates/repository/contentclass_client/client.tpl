<div class="row">
  <div class="col-md-3">
	{$repository.form}
  </div>
  <div class="col-md-9">
	{if is_set( $repository.results.count )}
	  {if $repository.results.count|gt(0)}
		
		{if $repository.results.count|eq(1)}
		  <h2>Trovato {$repository.results.count} risultato</h2>
		{else}
		  <h2>Trovati {$repository.results.count} risultati</h2>
		{/if}
		
		<p>
		  {foreach $repository.results.fields as $field}
			<span class="label label-primary">{$field.name}: {$field.value}</span>
		  {/foreach}
		</p>
		
		<table class="table">
		  <tr>
			<th>Titolo</th>		  
			<th>Link al sito originale</th>
			<th>Link</th>
		  </tr>
		{foreach $repository.results.contents as $content}
		  <tr>
			<td>{$content.name|wash()}</td>
			<td><a target="_blank" href="{$content.installation_url}{$content.main_url_alias}">{$content.main_url_alias}</a></td>
			<td>
			  {def $imported = fetch( 'content', 'object', hash( 'remote_id', $content.remote_id ))}
			  {if $imported}
				{def $importedNode = $imported.main_node}
				<a href={$importedNode.url_alias|ezurl()}>{$importedNode.url_alias}</a>
				{undef $importedNode}
			  {else}				
				<a href={concat( 'repository/import/', $repository.definition.Identifier, '/', $content.main_node_id )|ezurl()} class="btn btn-sm btn-danger">
				  Importa
				</a>
			  {/if}
			  {undef $imported}
			</td>
		  </tr>
		{/foreach}
		</table>
		
		{if $repository.results.prev}
		  <a class="btn btn-primary btn-sm pull-left" href="{$repository.results.prev}">Precedente</a>
		{/if}
		
		{if $repository.results.next}
		  <a class="btn btn-primary btn-sm pull-right" href="{$repository.results.next}">Successiva</a>
		{/if}
	  {else}
		<h2>Nessun risultato</h2>
		<p>
		  {foreach $repository.results.fields as $field}
			<span class="label label-primary">{$field.name}: {$field.value}</span>
		  {/foreach}
		</p>
	  {/if}
	{/if}
  </div>
</div>