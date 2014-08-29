{if $repository_list|count()|gt(0)}
<ul>
{foreach $repository_list as $repository}
  <li>
    <a href={concat( 'repository/client/', $repository.Identifier )|ezurl}>{$repository.Name} ({$repository.Url})</a>
  </li>
{/foreach}
</ul>
{else}
  <div class="alert alert-danger" role="alert">
    <p>Non ci sono repository configurati</p>
  </div>
{/if}