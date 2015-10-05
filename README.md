OpenContent Search Tools
========================

Estensione per eZPublish Legacy che utilizza eZFind per esporre nuove
funzionalità e visualizzazioni

 

Moduli
------

 

### calendar

-   **view** proxy per la visualizzazione di calendario eventi in template di
    nodo

-   **search** json endpoint per la visualizzazione di calendario eventi in
    angular.js

 

### classtools

-   **compare **modulo per la comparazione/sincronizzazione/installazione di una
    classe con un repository remoti di classi

-   **list **visualizzazione user friendly della lista delle classi

-   **definition **esposizione in json della definizione della classe

-   **classes **visualizzazione user friendly della singola classe ez

-   **relations **visualizzazione dello schema delle relazioni

 

### datatable

-   **view** endpoint per la visualizzaione di contenuti via jQuery Datatable
    Plugin

 

### facet

-   **proxy** proxy per la visualizzazione di ricerca a faccette

 

### index

-   **object **modulo per indicizzare un eZContentObject in solr e esplorarne i
    dettagli di indicizzazione

-   **remote\_id\_search** modulo per il reindirizzamento al content/view/full
    di un nodo a partire dal remote\_id dell'oggetto

-   **subtree** modulo per l’inserimento in pending list della reindicizzazioen
    di un sottoalbero

 

### ocsearch

-   **action **modulo per il reindirizzamento per la visualizzaione di ricerca
    per classe

 

### repository

-   **client **client del sistema di cross site search

-   **server **server del sistema di cross site search

-   **import **modulo di importazione per il sistema di cross site search

 

Operatori di template
---------------------

 

### Operatori di utilità per la ricerca

-   setFilterParameter( string \$name, mixed \$value )

-   removeFilterParameter( string \$name )

-   getFilterParameter( string \$name )

-   getFilterParameters( [bool \$as\_array], [string \$cond] )

-   getFilterUrlSuffix()

-   getFilterHiddenInput()

-   addQuoteOnFilter()

 

### Operatori di utilità generale

-   in\_array\_r( string \$needle, array \$haystack )

-   sort()

-   asort()

-   parsedate()

-   strtotime()

 

### Operatori per le visualizzazioni

-   facet\_navigation( array \$base\_query, array \$override, string \$base\_url
    )

-   class\_search\_form( array \$parameters, array \$fields )

-   attribute\_search\_form( OCClassSearchFormHelper \$helper,
    OCClassSearchFormAttributeField \$input\_field )

-   class\_search\_result( array \$parameters, array \$fields )

-   calendar( eZContentObjectTreeNode \$node, array \$parameters )

 

### Operatori per la funzionalità di cross search

-   repository\_list()
