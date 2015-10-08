{def $tag_tematiche = fetch( tags, tags_by_keyword, hash( 'keyword', 'Tematiche' ) )}

<div class="col-md-offset-3 col-md-6">
    
    {if is_set($tag_tematiche)}
        {def $tags = fetch(tags, tree, hash('parent_tag_id', $tag_tematiche.0.id))}

        {if is_set($tags)}
            <h2>
                Seleziona tematiche
            </h2>
            <p>
                Seleziona una o più tematiche per la catalogazione dell'oggetto importato.
            </p>

            <form method="POST" action="{concat($fromPage, '/', $localParentNodeID)|ezurl('no')}">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Tematiche
                    </div>
                    {def $i=1}
                    
                    {foreach $tags as $tag}
                        <div class="checkbox" style="margin-left:4px;">
                            <label>
                                <input name="tematica{$i}" 
                                       type="checkbox" 
                                       value="{$tag.id};{$tag.keyword};{$tag.parent_id}">
                                {$tag.keyword}
                            </label>
                        </div>

                        {set $i=$i|sum(1)}
                    {/foreach}
                    {undef $i}
                </div>
                <button class="pull-left btn btn-primary" type="submit" name="SelectTags">Seleziona</button>
                <button class="pull-right btn btn-large btn-default" type="submit" name="BrowseCancelButton">Annulla</button>
            </form>
        {else}
            <div class="alert alert-danger" role="alert">
                <strong>Errore!</strong>
                Nessun Tag Tematiche disponibile! Contattare l'amministratore.
            </div>
        {/if}
    {else}
        <div class="alert alert-danger" role="alert">
            <strong>Errore!</strong>
            Non esistono le Tematiche per questo sito! Contattare l'amministratore.
        </div>
    {/if}

</div>