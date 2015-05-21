<?php

abstract class OCCalendarSearchContext implements OCCalendarSearchContextInterface
{
    
    protected $contextIdentifier;
        
    protected $solrBaseFields = array();

    protected $taxonomyFetchParams = array();

    protected $taxonomyFetchRootNodeId = array();

    protected $solrFetchParams = array();
    
    public $forceTaxonomyIdentifiers = array();
    
    public $parsedRequest = array();
    
    final public static function instance( $contextIdentifier, $contextParameters = array(), $request = array() )
    {
        $ini = eZINI::instance( 'ocsearchtools.ini' );
        if ( $ini->hasVariable( 'CalendarSearchContext_' . $contextIdentifier, 'SearchContext' ) )
        {
            $className = $ini->variable( 'CalendarSearchContext_' . $contextIdentifier, 'SearchContext' );
            return new $className( $contextIdentifier, $contextParameters, $request );
        }
        throw new Exception( "SearchContext class for $contextIdentifier not found" );
    }
    
    public function identifier()
    {
        return $this->contextIdentifier;
    }

    public function cacheKey()
    {
        return $this->contextIdentifier;
    }
    
    public function taxonomiesCacheKey()
    {
        return 'calendar_taxonomy';
    } 
    
    public function solrFetchParams()
    {
        return $this->solrFetchParams;
    }
    
    public function taxonomyTree( $taxonomyIdentifier )
    {        
        switch( $taxonomyIdentifier )
        {
            case 'what':
            case 'where':
            case 'target':
            case 'category':
                $data = array();
                $nodes = eZContentObjectTreeNode::subTreeByNodeID( $this->taxonomyFetchParams[$taxonomyIdentifier], $this->taxonomyFetchRootNodeId[$taxonomyIdentifier] );
                foreach( $nodes as $node )
                {
                    $data[] = $this->walkTaxonomyItem( $node, $taxonomyIdentifier );
                }
                return $data;
            break;
        }
        return false;
    }
    
    public function getSolrFilters( array $data, OCCalendarSearchTaxonomy $taxonomy )
    {
        $filter = array();        
        foreach( $data as $taxonomyId )
        {
            $item = $taxonomy->getItem( $taxonomyId );
            if ( $item )
            {
                $filter[] = $item['solr_filter'];
            }        
        }
        return empty( $filter ) ? false : $filter;
    }

    /**
     * @param eZContentObjectTreeNode $node
     * @param string $taxonomyIdentifier
     *
     * @return array
     */
    protected function walkTaxonomyItem( $node, $taxonomyIdentifier )
    {
        /** @var eZContentObject $object */
        $object = $node->attribute( 'object' );
        $item = array(
            'name' => $node->attribute( 'name' ),
            'main_node_id' => intval( $object->attribute( 'main_node_id' ) ),
            'main_parent_node_id' => intval( $object->attribute( 'main_parent_node_id' ) ),
            'id' => intval( $node->attribute( 'contentobject_id' ) ),
            'class_identifier' => $node->attribute( 'class_identifier' ),
            'solr_filter' => array(),
            'children' => array()
        );
        $solrIdFields = array();
        if ( $this->solrBaseFields[$taxonomyIdentifier][$node->attribute( 'class_identifier' )] )
        {
            foreach( $this->solrBaseFields[$taxonomyIdentifier][$node->attribute( 'class_identifier' )] as $baseField )
            {
                $solrIdFields[] = "submeta_{$baseField}___id____si";
            }
        }

        /** @var eZContentObjectTreeNode[] $children */
        $children = eZContentObjectTreeNode::subTreeByNodeID( $this->taxonomyFetchParams[$taxonomyIdentifier], $node->attribute( 'node_id' ) );
        $parentFields = array();
        foreach( $children as $child )
        {
            $item['children'][] = $this->walkTaxonomyItem( $child, $taxonomyIdentifier );
            foreach( $this->solrBaseFields[$taxonomyIdentifier][$child->attribute( 'class_identifier' )] as $baseField )
            {
                $parentFields[] = "submeta_{$baseField}___path____si";
            }
        }
        $parentFields = array_unique( $parentFields );
        if ( count( $parentFields ) )
        {
            if ( count( $solrIdFields ) > 0 )
            {
                foreach( $solrIdFields as $solrIdField )
                {
                    $solrFilter = array(
                        'or',
                        $solrIdField . ':' . $item['id']
                    );
                    foreach( $parentFields as $parentField )
                    {
                        $solrFilter[] = $parentField . ':' . $item['main_node_id'];
                    }
                    $item['solr_filter'][] = $solrFilter;
                }
            }
            else
            {
                $solrFilter = array( 'or' );
                foreach( $item['children'] as $child )
                {
                    $solrFilter = array_merge( $solrFilter, $child['solr_filter'] );
                }
                $item['solr_filter'] = $solrFilter;
            }
        }
        else
        {
            foreach( $solrIdFields as $solrIdField )
            {
                if ( $solrIdField )
                {
                    $item['solr_filter'][] = $solrIdField . ':' . $item['id'];
                }
            }
        }
        return $item;
    }
    
    protected function parseFacetItem( $currentItem, $currentFacetsResult )
    {
        $facetItem = false;
        if ( array_key_exists( $currentItem['id'], $currentFacetsResult ) )
        {
            $facetItem = $currentItem;
            $facetItem['count'] = $currentFacetsResult[$currentItem['id']];
            $facetItem['is_selectable'] = 1;
            $facetItem['children'] = array();
            if ( $currentItem['children'] > 0 )
            {
                foreach( $currentItem['children'] as $child )
                {
                    $childItem = $this->parseFacetItem( $child, $currentFacetsResult );
                    if ( $childItem )
                    {
                        $facetItem['children'][] = $childItem;
                    }
                }
            }
        }
        if ( $currentItem['children'] > 0 )
        {
            $foundChildren = array();
            foreach( $currentItem['children'] as $child )
            {
                $childItem = $this->parseFacetItem( $child, $currentFacetsResult );
                if ( $childItem )
                {
                    $foundChildren[] = $childItem;
                }
            }
            if ( count( $foundChildren ) > 0 )
            {
                $facetItem = $currentItem;
                $facetItem['children'] = $foundChildren;
                $facetItem['is_selectable'] = 1;
            }
        }
        return $facetItem;
    }

    public function parseFacets( array $rawFacetsFields, array $parsedRequest )
    {        
        $currentFacetsResult = array();
        foreach( $rawFacetsFields as $resultFacetGroup )
        {
            $currentFacetsResult = $currentFacetsResult + $resultFacetGroup['countList'];
        }
        $facets = array();
        $taxonomyIdentifiers = array( 'what', 'where', 'target', 'category' );
        $forceTaxonomyIdentifiers = $this->forceTaxonomyIdentifiers;
        foreach( $taxonomyIdentifiers as $taxonomyIdentifier )
        {
            $taxonomy = OCCalendarSearchTaxonomy::instance( $taxonomyIdentifier, $this );
            if ( $taxonomy instanceof OCCalendarSearchTaxonomy )
            {
                $facets[$taxonomyIdentifier] = array();
                foreach( $taxonomy->getTree() as $item )
                {
                    $facetItem = $this->parseFacetItem( $item, $currentFacetsResult );
                    if( in_array( $taxonomyIdentifier, $forceTaxonomyIdentifiers ) )
                    {
                        $item['is_selectable'] = $facetItem != false;
                        $facets[$taxonomyIdentifier][] = $item;
                    }
                    elseif ( $facetItem )
                    {
                        $item['is_selectable'] = 1;
                        $facets[$taxonomyIdentifier][] = $facetItem;
                    }
                    elseif ( isset( $parsedRequest[ $taxonomyIdentifier ] ) && in_array( $item['id'], $parsedRequest[ $taxonomyIdentifier ] ) )
                    {
                        $children = array();
                        if ( $item['children'] > 0 )
                        {
                            foreach( $item['children'] as $child )
                            {
                                $childItem = $this->parseFacetItem( $child, $currentFacetsResult );
                                if ( $childItem )
                                {
                                    $children[] = $childItem;
                                }
                            }
                            $item['children'] = $children;
                        }
                        $item['is_selectable'] = 1;
                        $facets[$taxonomyIdentifier][] = $item;
                    }                    
                }
            }
        }
        return $facets;
    }
}
