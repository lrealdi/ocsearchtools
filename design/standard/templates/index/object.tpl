<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Index object</title>
<style>
{literal}
body { font-size: 100%; font-family: 'Lucida Grande', Verdana, Arial, Sans-Serif; }ul#tabs { list-style-type: none; margin: 30px 0 0 0; padding: 0 0 0.3em 0; }ul#tabs li { display: inline; }ul#tabs li a { font-size: 1.3em;color: #42454a; background-color: #dedbde; border: 1px solid #c9c3ba; border-bottom: none; padding: 0.3em 0.5em; text-decoration: none; }ul#tabs li a:hover { background-color: #f1f0ee; }ul#tabs li a.selected { color: #000; background-color: #f1f0ee; font-weight: bold; padding-top: .5em }div.tabContent { overflow: auto;border: 1px solid #c9c3ba; padding: 0.5em; background-color: #f1f0ee; }div.tabContent.hide { display: none; } pre { white-space: pre-wrap;background: #fff;padding: 10px} tbody tr:nth-child(odd) { background-color: #fff;}
{/literal}
</style>
</head>

{if $error}

  <body>
    <h1>Error</h1>
    {$error}
  </body>

{else}

  <body onload="init()">
    
  <h1>Index object #{$info.object.id} {if $info.result}<strong style="background: green; color: white; padding: 5px;"> OK{else}<strong style="background: red; color: white; padding: 5px;"> FAIL {/if}</strong></h1>
    
  <ul id="tabs">
    <li><a href="#info">Info</a></li>
    <li><a href="#detail">Detail</a></li>
    <li><a href="#xml">XML</a></li>
    <li><a href="#solr">Solr</a></li>
  </ul>
  
  <div class="tabContent" id="info">
    <div>
        <p><strong>Object ID</strong> {$info.object.id}</p>
        <p><strong>Main Node ID</strong> {$info.object.main_node_id}</p>
        <p><strong>Name</strong> <a href="{$info.object.main_node.url_alias|ezurl(no)}">{$info.object.name}</a></p>
    </div>
  </div>
  
  <div class="tabContent" id="detail">
    <div>
      <table style="width:100%" cellpadding="10" cellspacing="0">
        <thead>
        <tr>
          <th>Attribute</th>
          <th>Standard Metadata</th>
          <th>eZFind Metadata</th>
        </tr>
        </thead>
        <tbody>
        {foreach $detail as $item}
          <tr>
            <td style="vertical-align: top">
              <strong>{$item.name|wash()}</strong><br />
              {$item.identifier}<br />              
            </td>
            <td style="vertical-align: top">                            
              {if $item.is_searchable}
                <strong>{$item.data_type_string}</strong><br />
                <pre>{$item.ez_metadata|wash()}</pre>
              {else}
                <small>(not searchable)</small>
              {/if}
            </td>
            <td style="vertical-align: top">              
              {if $item.is_searchable}
                <strong>{$item.solr_metadata_class|wash()}</strong><br />
                <pre>{$item.solr_metadata|wash()}</pre>
              {else}
                <small>(not searchable)</small>
              {/if}
            </td>
          </tr>
        {/foreach}
        </tbody>
      </table> 
    </div>
  </div>
  
  <div class="tabContent" id="xml">
    <div>      
      {foreach $xml as $i => $x}
        <div id="xml-{$i}">
          <p>
            {foreach $xml as $index => $icsemmelle}
              {if $i|eq($index)>}
                <strong>Document {$index|inc()}</strong>
              {else}
                <a href="#xml-{$index}">Document {$index|inc()}</a>
              {/if}
            {delimiter} - {/delimiter}
            {/foreach}
          </p>
          <pre>{$x|wash()}</pre>
        </div>        
      {/foreach}
    </div>
  </div>
  
  <div class="tabContent" id="solr">
    <div>
      <strong>Ping</strong>
        <pre>{$solr.ping|wash()}</pre>
      <strong>Version</strong>
        <pre>{$solr.version|wash()}</pre>
    </div>
  </div>
  
  <script>
  {literal}
  var tabLinks = new Array();
  var contentDivs = new Array();
  function init() {var tabListItems = document.getElementById('tabs').childNodes;for ( var i = 0; i < tabListItems.length; i++ ) {if ( tabListItems[i].nodeName == "LI" ) {var tabLink = getFirstChildWithTagName( tabListItems[i], 'A' );var id = getHash( tabLink.getAttribute('href') );tabLinks[id] = tabLink;contentDivs[id] = document.getElementById( id );}}var i = 0;for ( var id in tabLinks ) {tabLinks[id].onclick = showTab;tabLinks[id].onfocus = function() { this.blur() };if ( i == 0 ) tabLinks[id].className = 'selected';i++;}var i = 0;for ( var id in contentDivs ) {if ( i != 0 ) contentDivs[id].className = 'tabContent hide';i++;}}
  function showTab(){var selectedId = getHash( this.getAttribute('href') );for ( var id in contentDivs ) {if ( id == selectedId ) {tabLinks[id].className = 'selected';contentDivs[id].className = 'tabContent';} else {tabLinks[id].className = '';contentDivs[id].className = 'tabContent hide';}}return false;}
  function getFirstChildWithTagName( element, tagName ) {for ( var i = 0; i < element.childNodes.length; i++ ) {if ( element.childNodes[i].nodeName == tagName ) return element.childNodes[i];}}
  function getHash( url ) {var hashPos = url.lastIndexOf ( '#' );return url.substring( hashPos + 1 );}
  {/literal}
  </script>
  </body>
  
{/if}

</html>