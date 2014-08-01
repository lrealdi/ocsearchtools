<?php

$module = $Params['Module'];
$repositoryID = isset( $Params['RepositoryID'] ) ? $Params['RepositoryID'] : false;

if ( !$repository )
{
    $list = OCCrossSearch::listAvailableRepositories();
    
}

if ( $repositoryID && OCCrossSearch::isAvailableRepository( $repositoryID ) )
{
    $repository = OCCrossSearch::instanceRepository( $repositoryID );
}
else
{
    // error
}




?>