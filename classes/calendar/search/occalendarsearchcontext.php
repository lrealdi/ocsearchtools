<?php

abstract class OCCalendarSearchContext implements OCCalendarSearchContextInterface
{
    /**
     * @var string
     */
    protected $contextIdentifier;

    /**
     * @var OCCachedSearchQuery
     */
    protected $queryHandler;

    /**
     * @var OCCalendarSearchRequest
     */
    protected $requestHandler;

    /**
     * @var DateTime
     */
    protected $startDateTime;

    /**
     * @var DateTime
     */
    protected $endDateTime;

    /**
     * @var string
     */
    public $dateTimeFormat = 'd/m/Y';

    /**
     * @param $contextIdentifier
     * @param array $contextParameters
     * @return OCCalendarSearchContextInterface
     * @throws Exception
     */
    final public static function instance( $contextIdentifier, $contextParameters = array() )
    {
        $ini = eZINI::instance( 'ocsearchtools.ini' );
        if ( $ini->hasVariable( 'CalendarSearchContext_' . $contextIdentifier, 'SearchContext' ) )
        {
            $className = $ini->variable( 'CalendarSearchContext_' . $contextIdentifier, 'SearchContext' );
            /** @var OCCalendarSearchContextInterface $instance */
            $instance = new $className( $contextIdentifier, $contextParameters );
            $queryHandler =  new OCCachedSearchQuery( $instance->getCacheKey() );
            $instance->setQueryHandler( $queryHandler );
        }
        throw new Exception( "SearchContext class for $contextIdentifier not found" );
    }

    protected function __construct( $contextIdentifier, $contextParameters = array() )
    {
        $this->contextIdentifier = $contextIdentifier;
    }
    
    public function getIdentifier()
    {
        return $this->contextIdentifier;
    }

    public function getCacheKey()
    {
        return $this->contextIdentifier;
    }
    
    public function getTaxonomiesCacheKey()
    {
        return 'calendar_taxonomy';
    }

    public function setRequest( OCCalendarSearchRequest $request )
    {
        $this->requestHandler = $request;
    }

    public function setQueryHandler( OCSearchQuery $queryHandler )
    {
        $this->queryHandler = $queryHandler;
    }

    final public function getData()
    {
        $data = array();
        $this->parseRequest();
        $resultData = $this->queryHandler->fetch();
        $result = array(
            'current_dates' => $this->getDateHash(),
            'events' => $this->parseResults( $resultData['SearchResult'] ),
            'count' => $resultData['SearchCount']
        );
        $data['result'] = $result;
        $data['facets'] = $this->parseFacets( $resultData['FacetFields'] );

        return $data;
    }

    public function getStartDateTime()
    {
        return $this->startDateTime;
    }

    public function getEndDateTime()
    {
        return $this->endDateTime;
    }

    public function getDateHash()
    {
        $date = array();
        if ( $this->startDateTime instanceof DateTime )
        {
            $date[] = $this->startDateTime->format( $this->dateTimeFormat );

            if ( $this->endDateTime instanceof DateTime )
            {
                if ( $this->endDateTime->format( $this->dateTimeFormat ) != $this->startDateTime->format( $this->dateTimeFormat ) )
                {
                    $date[] = $this->endDateTime->format( $this->dateTimeFormat );
                }
            }
        }
        return $date;
    }

    protected function parseRequest()
    {
        if ( $this->requestHandler->has( 'text' ) )
        {
            $this->queryHandler->queryText = $this->requestHandler->get( 'text' );
        }

        if ( $this->requestHandler->has( 'when' ) )
        {
            $this->queryHandler->addQueryFilter( $this->getDateFilter( $this->requestHandler->get( 'when' ) ) );
        }

        if ( $this->requestHandler->has( 'what' ) )
        {
            $this->queryHandler->addQueryFilter( $this->getTaxonomyFilter( $this->requestHandler->get( 'what' ), 'what' ) );
        }

        if ( $this->requestHandler->has( 'where' ) )
        {
            $this->queryHandler->addQueryFilter( $this->getTaxonomyFilter( $this->requestHandler->get( 'where' ), 'where' ) );
        }

        if ( $this->requestHandler->has( 'target' ) )
        {
            $this->queryHandler->addQueryFilter( $this->getTaxonomyFilter( $this->requestHandler->get( 'target' ), 'target' ) );
        }

        if ( $this->requestHandler->has( 'category' ) )
        {
            $this->queryHandler->addQueryFilter( $this->getTaxonomyFilter( $this->requestHandler->get( 'category' ), 'category' ) );
        }
    }

    protected function getDateFilter( $dateArray )
    {
        $this->startDateTime = array_shift( $dateArray );
        $this->endDateTime = array_shift( $dateArray );;
        if ( $this->endDateTime == null )
        {
            $this->endDateTime = clone $this->startDateTime;
        }

        $this->startDateTime->setTime( 0, 0 );
        $this->endDateTime->setTime( 23, 59 );

        //ezfSolrDocumentFieldBase::preProcessValue( $start->format( 'U' ), 'date' );
        $startSolr = strftime(
            '%Y-%m-%dT%H:%M:%SZ',
            $this->startDateTime->format( 'U' )
        );

        //ezfSolrDocumentFieldBase::preProcessValue( $end->format( 'U' ) - 1 , 'date' );
        $endSolr = strftime(
            '%Y-%m-%dT%H:%M:%SZ',
            $this->endDateTime->format( 'U' )
        );

        return array(
            'or',
            'attr_from_time_dt:[' . $startSolr . ' TO ' . $endSolr . ']',
            'attr_to_time_dt:[' . $startSolr . ' TO ' . $endSolr . ']',
            array(
                'and',
                'attr_from_time_dt:[* TO ' . $startSolr . ']',
                'attr_to_time_dt:[' . $endSolr . ' TO *]'
            )
        );
    }

    protected function getTaxonomyFilter( $data, $taxonomyIdentifier )
    {
        $filter = array();
        $taxonomy = OCCalendarSearchTaxonomy::instance( $taxonomyIdentifier, $this );
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
     * @param $taxonomyIdentifier
     *
     * @return array
     */
    abstract protected function getTaxonomyFetchParameters( $taxonomyIdentifier );

    /**
     * @param $taxonomyIdentifier
     *
     * @return int
     */
    abstract protected function getTaxonomyFetchRootNodeId( $taxonomyIdentifier );

    /**
     * @param $taxonomyIdentifier
     *
     * @return array
     */
    abstract protected function getTaxonomySolrBaseFields( $taxonomyIdentifier );

    /**
     * @return array
     */
    abstract protected function getAlwaysDisplayTaxonomyIdentifiers();

    public function getTaxonomyTree( $taxonomyIdentifier )
    {        
        switch( $taxonomyIdentifier )
        {
            case 'what':
            case 'where':
            case 'target':
            case 'category':
                $data = array();
                $nodes = eZContentObjectTreeNode::subTreeByNodeID(
                    $this->getTaxonomyFetchParameters( $taxonomyIdentifier ),
                    $this->getTaxonomyFetchRootNodeId( $taxonomyIdentifier )
                );
                foreach( $nodes as $node )
                {
                    $data[] = $this->walkTaxonomyItem( $node, $taxonomyIdentifier );
                }
                return $data;
            break;
        }
        return false;
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
        $baseFields = $this->getTaxonomySolrBaseFields( $taxonomyIdentifier );
        if ( $baseFields[$node->attribute( 'class_identifier' )] )
        {
            foreach( $baseFields[$node->attribute( 'class_identifier' )] as $baseField )
            {
                $solrIdFields[] = "submeta_{$baseField}___id____si";
            }
        }

        /** @var eZContentObjectTreeNode[] $children */
        $children = eZContentObjectTreeNode::subTreeByNodeID(
            $this->getTaxonomyFetchParameters( $taxonomyIdentifier ),
            $node->attribute( 'node_id' )
        );
        $parentFields = array();
        foreach( $children as $child )
        {
            $item['children'][] = $this->walkTaxonomyItem( $child, $taxonomyIdentifier );
            foreach( $baseFields[$child->attribute( 'class_identifier' )] as $baseField )
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

    public function parseFacets( $rawFacetsFields )
    {        
        $currentFacetsResult = array();
        foreach( $rawFacetsFields as $resultFacetGroup )
        {
            $currentFacetsResult = $currentFacetsResult + $resultFacetGroup['countList'];
        }
        $facets = array();
        $taxonomyIdentifiers = array( 'what', 'where', 'target', 'category' );
        $forceTaxonomyIdentifiers = $this->getAlwaysDisplayTaxonomyIdentifiers();
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
