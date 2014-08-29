{def $repository_list = repository_list()}
{if count( $repository_list )|gt(0)}
<h2>Repository disponibili</h2>
<ul>
{foreach $repository_list as $repository}
  <li>
    <a href={concat( 'repository/client/', $repository.Identifier )|ezurl}>{$repository.Name} ({$repository.Url})</a>
  </li>
{/foreach}
</ul>
{/if}