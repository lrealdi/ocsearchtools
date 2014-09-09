{def $repository_list = repository_list()}
{if count( $repository_list )|gt(0)}
<h2>Importa contenuti da repository</h2>
<ul>
{foreach $repository_list as $repository}
  <li>
    <a href={concat( 'repository/client/', $repository.Identifier )|ezurl}>{$repository.Name} <small>({$repository.Url})</small></a>
  </li>
{/foreach}
</ul>
{/if}