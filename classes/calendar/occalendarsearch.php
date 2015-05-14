<?php

class OCCalendarSearch
{
    
    protected $query;
    
    public static function instance( $query )
    {
        return new OCCalendarSearch( $query );
    }
    
    protected function __construct( OCCalendarSearchQuery $query )
    {
        $this->query = $query;        
    }
    
    public function solrData()
    {
        return array(
            $this->query->solrParams,
            $this->query->solrResult
        );
    }
    
    public function facets()
    {
        $facets = array();
        $facets['what'] = array(
            array( 'name' => 'test', 'id' => 1, 'children' => array( array( 'name' => 'subtest', 'id' => 1 ), array( 'name' => 'subtestA', 'id' => 1 ) ) ),
            array( 'name' => 'test1', 'id' => 2, 'children' => array( array( 'name' => 'subtest1', 'id' => 11 ), array( 'name' => 'subtestA1', 'id' => 111 ) ) ),
            array( 'name' => 'test2', 'id' => 3, 'children' => array( array( 'name' => 'subtest2', 'id' => 12 ), array( 'name' => 'subtestA2', 'id' => 112 ) ) ),
            array( 'name' => 'test3', 'id' => 4, 'children' => array( array( 'name' => 'subtest3', 'id' => 13 ), array( 'name' => 'subtestA3', 'id' => 113 ) ) ),
            array( 'name' => 'test4', 'id' => 5, 'children' => array( array( 'name' => 'subtest4', 'id' => 14 ), array( 'name' => 'subtestA4', 'id' => 114 ) ) ),
            array( 'name' => 'test5', 'id' => 6, 'children' => array( array( 'name' => 'subtest5', 'id' => 15 ), array( 'name' => 'subtestA5', 'id' => 115 ) ) )
        );
        $facets['where'] = array(
            array( 'name' => 'test', 'id' => 1, 'children' => array( array( 'name' => 'subtest', 'id' => 1 ), array( 'name' => 'subtestA', 'id' => 1 ) ) ),
            array( 'name' => 'test1', 'id' => 2, 'children' => array( array( 'name' => 'subtest1', 'id' => 11 ), array( 'name' => 'subtestA1', 'id' => 111 ) ) ),
            array( 'name' => 'test2', 'id' => 3, 'children' => array( array( 'name' => 'subtest2', 'id' => 12 ), array( 'name' => 'subtestA2', 'id' => 112 ) ) ),
            array( 'name' => 'test3', 'id' => 4, 'children' => array( array( 'name' => 'subtest3', 'id' => 13 ), array( 'name' => 'subtestA3', 'id' => 114 ) ) ),
            array( 'name' => 'test4', 'id' => 5, 'children' => array( array( 'name' => 'subtest4', 'id' => 14 ), array( 'name' => 'subtestA4', 'id' => 113 ) ) ),
            array( 'name' => 'test5', 'id' => 6, 'children' => array( array( 'name' => 'subtest5', 'id' => 15 ), array( 'name' => 'subtestA5', 'id' => 115 ) ) )
        );
        $facets['category'] = array(
            array( 'name' => 'test', 'id' => 1 ),
            array( 'name' => 'test1', 'id' => 2 ),
            array( 'name' => 'test2', 'id' => 3 ),
            array( 'name' => 'test3', 'id' => 4 ),
            array( 'name' => 'test4', 'id' => 5 ),
            array( 'name' => 'test5', 'id' => 6 )
        );
        $facets['target'] = array(
            array( 'name' => 'test', 'id' => 1 ),
            array( 'name' => 'test1', 'id' => 2 ),
            array( 'name' => 'test2', 'id' => 3 ),
            array( 'name' => 'test3', 'id' => 4 ),
            array( 'name' => 'test4', 'id' => 5 ),
            array( 'name' => 'test5', 'id' => 6 )
        );
        return $facets;
    }
    
    public function result()
    {
        $result = array(
            'events' => $this->query->solrResult['SearchResult'],
            'count' => $this->query->solrResult['SearchCount']
        );
        return $result;
    }
    
}
