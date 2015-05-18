<?php

abstract class OCCalendarSearchContext implements OCCalendarSearchContextInterface
{
    
    protected $contextIdentifier;
        
    protected $solrBaseFields = array();

    protected $taxonomyFetchParams = array();

    protected $taxonomyFetchRootNodeId = array();

    protected $solrFetchParams = array();
    
    final public static function instance( $contextIdentifier, $contextParameters = array() )
    {
        $ini = eZINI::instance( 'ocsearchtools.ini' );
        if ( $ini->hasVariable( 'CalendarSearchContext_' . $contextIdentifier, 'SearchContext' ) )
        {
            $className = $ini->variable( 'CalendarSearchContext_' . $contextIdentifier, 'SearchContext' );
            return new $className( $contextIdentifier, $contextParameters );
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
            'main_node_id' => $object->attribute( 'main_node_id' ),
            'main_parent_node_id' => $object->attribute( 'main_parent_node_id' ),
            'id' => $node->attribute( 'contentobject_id' ),
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
}
