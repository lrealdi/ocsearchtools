<?php
/** @var eZModule $module */
$Module = $Params['Module'];
$ParentNodes = $Params['ParentNodes'];
$Classes = $Params['Classes'];
$Fields = $Params['Fields'];
$DefaultFilters = $Params['DefaultFilters'] !== null ? $Params['DefaultFilters'] : array();
$http = eZHTTPTool::instance();

$fields = explode( ',', $Fields );
$classes = explode( ',', $Classes );
$subtreeArray = explode( ',', $ParentNodes );
$defaultFilters = $DefaultFilters !== null ? explode( ',', $DefaultFilters ) : array();
$fieldsToReturn = $fields;
$iDisplayStart = $http->hasGetVariable( 'iDisplayStart' ) ? $http->getVariable( 'iDisplayStart' ) : 10;
$iDisplayLength = $http->hasGetVariable( 'iDisplayLength' ) ? $http->getVariable( 'iDisplayLength' ) : 100;

/*
 * Ordering
 */
$sortBy = array( 'name' => 'asc' );
if ( $http->hasGetVariable( 'iSortCol_0' ) )
{    
    for ( $i=0 ; $i<intval( $http->getVariable( 'iSortingCols' ) ); $i++ )
    {
        $sortBy = array();
        if ( $http->getVariable( 'bSortable_'.intval( $http->getVariable( 'iSortCol_'.$i) ) ) == "true" )
        {
            $sortBy[] = array( $fields[ intval( $http->getVariable( 'iSortCol_' . $i ) ) ], $http->getVariable( 'sSortDir_' . $i ) );             
        }
    }
}

$query = '';
if ( $http->getVariable( 'sSearch' ) != "" )
{
    $query = str_replace( ' ', ' AND ', $http->getVariable( 'sSearch' ) ) . '*';
}

/* 
 * Individual column filtering
 */
$filters = array();
if ( !empty( $defaultFilters ) )
{        
    $filters[] = $defaultFilters;
}
for ( $i=0 ; $i<count( $fields ) ; $i++ )
{
    $columnFilters = array();    
    if ( $fields[$i] == 'name' || $fields[$i] == 'meta_name_t' && $http->getVariable( 'sSearch_' . $i ) != '' )
    {
        $query = str_replace( ' ', ' AND ', $http->getVariable( 'sSearch_' . $i ) ) . '*';
    }
    elseif ( $http->hasGetVariable( 'bSearchable_' . $i ) && $http->getVariable( 'bSearchable_' . $i ) == "true" && $http->getVariable( 'sSearch_' . $i ) != '' )
    {
        if ( isSubAttributeField( $fields[$i] ) )
        {
            $columnFilters[] = $fields[$i] . ':"' . $http->getVariable( 'sSearch_' . $i ) . '"';
        }
        elseif ( isUserNameField( $fields[$i] ) )
        {
            $columnFilters[] = $fields[$i] . ':' . $http->getVariable( 'sSearch_' . $i );
        }
        elseif ( isClassNameField( $fields[$i] ) )
        {
            $columnFilters[] = $fields[$i] . ':"' . $http->getVariable( 'sSearch_' . $i ) . '"';
        }
        elseif ( isTextField( $fields[$i] ) )
        {
            $columnFilters[] = $fields[$i] . ':' . str_replace( ' ', ' AND ', $http->getVariable( 'sSearch_' . $i ) );
        }        
    }    
    if ( !empty( $columnFilters ) )
    {        
        if ( count( $columnFilters ) > 1 )
            $filters[] = array_merge( array( 'or' ), $columnFilters );
        else
            $filters[] = $columnFilters;
    }
}
if ( empty( $filters ) )
{
    $filters = null;
}

$solrSearch = new eZSolr();

$params = array( 'SearchOffset' => $iDisplayStart,
                 'SearchLimit' => $iDisplayLength,                 
                 'SortBy' => $sortBy,
                 'Filter' => $filters,
                 'SearchContentClassID' => $classes,                 
                 'SearchSubTreeArray' => $subtreeArray,
                 'AsObjects' => false,                 
                 'FieldsToReturn' => $fieldsToReturn );

$solrSearch = new eZSolr();
$search = $solrSearch->search( $query, $params );
$search['SearchParameters'] = $params;
$iFilteredTotal = count( $search['SearchResult'] );
$iTotal = $search['SearchCount'];
echo '<pre>';print_r($search['SearchExtras']);die();
$output = array(
    "sEcho" => intval( $http->getVariable( 'sEcho' ) ),
    "iTotalRecords" => $iTotal,
    "iTotalDisplayRecords" => $iTotal,
    "aaData" => array()
);

if ( eZINI::instance()->variable( 'DebugSettings', 'DebugOutput' ) === 'enabled' )
{
    $output['searchParams'] = $search;
    $output['searchQuery'] = $query;
    $output['request'] = $_GET;
    $output['fields'] = $fields;    
    $output['results'] = $search['SearchResult'];
}

foreach( $search['SearchResult'] as $item )
{
    $row = array();    
    for ( $i=0 ; $i<count( $fields ); $i++ )
    {
        if ( $fields[$i] == 'published' )
        {
            $timestamp = strtotime( $item[$fields[$i]] );
            $row[] = date( 'd/m/Y H:i', $timestamp );
        }
        elseif ( isset( $item[$fields[$i]] ) )
            $row[] = $item[$fields[$i]];
        elseif ( isset( $item['fields'][$fields[$i]] ) )
            $row[] = $item['fields'][$fields[$i]];        
        elseif ( strpos( $fields[$i], 'meta' ) === 0 ) //@todo
        {
            $fieldPart = explode( '_', $fields[$i] );
            $meta = array_shift( $fieldPart );
            $type = array_pop( $fieldPart );
            $row[] = $item[implode( '_', $fieldPart )];
        }
        else
            $row[] = '';
    }    
    $output['aaData'][] = $row;
}

header('Content-Type: application/json');
echo json_encode( $output );
//eZDisplayDebug();
eZExecution::cleanExit();

//@todo

function isTextField( $field )
{
    return substr( $field, -1 ) == 't' || $field == 'name';
}

function isUserNameField( $field )
{
    return $field == 'meta_owner_name_ms';
}

function isClassNameField( $field )
{
    return substr( $field, -2 ) == 'ms';
}

function isSubAttributeField( $field )
{
    return strpos( $field, 'subattr' ) !== false;
}
