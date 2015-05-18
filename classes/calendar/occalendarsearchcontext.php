<?php

class OCCalendarSearchContext implements OCCalendarSearchContextInterface
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
    
    protected function __construct( $contextIdentifier, $contextParameters = array() )
    {
        $this->contextIdentifier = $contextIdentifier;
        
        $this->taxonomyFetchRootNodeId = array(
            'what' => 200420,
            'where' => 6392,
            'target' => 478657,
            'category' => 22155
        );
        
        $this->taxonomyFetchParams = array(
            'what' => array(
                'ClassFilterType' => 'include',
                'ClassFilterArray' => array( 'tipo_evento' ),
                'SortBy' => array( 'priority', true ),
                'Limitation' => array(),
                'Depth' => 1,
                'DepthOperator' => 'eq',
            ),
            'where' => array(
                'ClassFilterType' => 'include',
                'ClassFilterArray' => array( 'comune', 'area_turistica' ),
                'SortBy' => array( 'priority', true ),
                'Limitation' => array(),
                'Depth' => 1,
                'DepthOperator' => 'eq',
            ),
            'target' => array(
                'ClassFilterType' => 'include',
                'ClassFilterArray' => array( 'utenza_target' ),
                'SortBy' => array( 'priority', true ),
                'Limitation' => array(),
                'Depth' => 1,
                'DepthOperator' => 'eq',
            ),
            'category' => array(
                'ClassFilterType' => 'include',
                'ClassFilterArray' => array( 'tematica' ),
                'SortBy' => array( 'priority', true ),
                'Limitation' => array(),
                'Depth' => 1,
                'DepthOperator' => 'eq',
            )
        );
        
        // class_identifier => attribute_identifier
        $this->solrBaseFields = array(
            'what' => array(
                'tipo_evento' => array( 'tipo_evento' )
            ),
            'where' => array(
                'comune' => array( 'comune' ),
                'area_turistica' => false,
            ),
            'target' => array(
                'utenza_target' => array( 'utenza_target' )
            ),
            'category' => array(
                'tematica' => array( 'tema' )
            ),
        );
        
        $this->solrFacetFields = array(
            'what' => array(
                'submeta_tipo_evento___id____si'
            ),
            'where' => array(
                'submeta_comune___id____si'
            ),
            'target' => array(
                'submeta_utenza_target___id____si'
            ),
            'category' => array(
                'submeta_tema___id____si'
            ),
        );
        
        $facets = array();
        foreach( $this->solrFacetFields as $identifier => $values )
        {
            foreach( $values as $name => $value )
            {
                $facets[] = array( 'field' => $value, 'name'  => $value, 'limit' => 100, 'sort' => 'alpha' );
            }
        }
        
        $this->solrFetchParams = array(
            'SearchSubTreeArray' => array( 298848 ),
            'FieldsToReturn' => array(
                'attr_from_time_dt',
                'attr_to_time_dt', 
                'meta_class_identifier_ms',
                'subattr_tipo_evento___name____s',
                'attr_indirizzo_t',
                'attr_luogo_svolgimento_t',
                'subattr_comune___name____s',
                'subattr_luogo_della_cultura___name____s',
                'subattr_luogo_della_cultura___indirizzo____s',
                'subattr_luogo_della_cultura___collocazione_geografica____t',
                'subattr_utenza_target___name____s',
                'subattr_tema___name____s',
                'attr_email_t',
                'attr_telefono_t',
                'attr_fax_t',
                'attr_url_t',
                'submeta_tipo_evento___id____si',
                'submeta_comune___id____si',
                'submeta_utenza_target___id____si',
                'subattr_utenza_target___name____s',
                'submeta_tema___id____si',
                'subattr_tema___name____s',
                'subattr_iniziativa___name____s',
                'submeta_iniziativa___main_node_id____si',
                'attr_orario_svolgimento_t',
                'subattr_geo___coordinates____gpt',
                'attr_costi_t'
            ),
            'Facet' => $facets
        );
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
    
    public function parseResults( array $rawResults, DateTime $startDateTime, DateTime $endDateTime = null )
    {        
        $events = array();
        //$allEvents = array();
        foreach( $rawResults as $rawResult )
        {
            $event = OCCalendarSearchResultItem::instance( $rawResult, $this );
            $events[] = $event;
            //$allEvents[] = $event->toHash();
        }
        
        $data = array();
        $taxonomy = OCCalendarSearchTaxonomy::instance( 'what', $this );
        if ( !$taxonomy instanceof OCCalendarSearchTaxonomy )
        {                            
            throw new Exception( "Taxonomy what not found" );
        }
        
        $byDayEvents = $this->byDayEvents( $events, $startDateTime, $endDateTime );
        foreach( $byDayEvents as $byDayEvent )
        {
            $byDayEventData = array(
                'day' => $byDayEvent,
                'tipo_evento' => array()
            );
            foreach( $taxonomy->getTree() as $taxonomyItem )
            {
                $byTaxonomyEvents = $this->byTaxonomyEvents( $byDayEvent->attribute( 'events' ), $taxonomyItem );
                if ( !empty( $byTaxonomyEvents ) )
                {                    
                    $byTaxonomyEventsHash = array();
                    foreach( $byTaxonomyEvents as $byTaxonomyEvent )
                    {
                        $byTaxonomyEventsHash[] = $byTaxonomyEvent->toHash();    
                    }                    
                    $byDayEventData['tipo_evento'][] = array(
                        'id' => $taxonomyItem['id'],
                        'name' => $taxonomyItem['name'],
                        'events' => $byTaxonomyEventsHash
                    );
                }
            }
            if ( count( $byDayEventData['tipo_evento'] ) > 0 )
            {
                $data[] = $byDayEventData;
            }
        }
        return $data;
    }

    /**
     * @param OCCalendarSearchResultItem $event
     * @param array $taxonomyItem
     *
     * @return bool
     */
    protected function eventIsA( $event, $taxonomyItem )
    {
        if ( isset( $event['tipo_evento'] ) )
        {
            foreach( $event['tipo_evento'] as $tipo )
            {
                if ( $tipo['id'] == $taxonomyItem['id'] )
                {
                    return true;
                }
                if ( count( $taxonomyItem['children'] ) > 0 )
                {
                    foreach( $taxonomyItem['children'] as $child )
                    {
                        if ( $tipo['id'] == $child['id'] )
                        {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * @param OCCalendarSearchResultItem[] $events
     * @param $taxonomyItem
     *
     * @return OCCalendarSearchResultItem[]
     */
    protected function byTaxonomyEvents( $events, $taxonomyItem )
    {
        $eventsByTaxonomy = array();
        foreach( $events as $event )
        {
            if ( $this->eventIsA( $event, $taxonomyItem ) )
            {
                $eventsByTaxonomy[] = $event;
            }
        }
        return $eventsByTaxonomy;
    }

    /**
     * @param OCCalendarSearchResultItem[] $events
     * @param DateTime $startDateTime
     * @param DateTime $endDateTime
     *
     * @return OCCalendarDay[]
     */
    protected function byDayEvents( $events, $startDateTime, $endDateTime = null )
    {
        $eventsByDay = array();        
        if ( $endDateTime instanceof DateTime )
        {
            $byDayInterval = new DateInterval( 'P1D' );
            $byDayPeriod = new DatePeriod( $startDateTime, $byDayInterval, $endDateTime );        
        }
        else
        {
            $byDayPeriod = array( $startDateTime );
        }
        /** @var DateTime[] $byDayPeriod */
        foreach( $byDayPeriod as $date )
        {
            $identifier = $date->format( OCCalendarData::FULLDAY_IDENTIFIER_FORMAT );            
            $calendarDay = new OCCalendarDay( $identifier );            
            $calendarDay->addEvents( $events );
            if ( count( $calendarDay->attribute( 'events' ) ) > 0 )
            {
                $eventsByDay[] = $calendarDay;            
            }
        }
        
        return $eventsByDay;
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
