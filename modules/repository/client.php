<?php

$module = $Params['Module'];
$repositoryID = isset( $Params['RepositoryID'] ) ? $Params['RepositoryID'] : false;
$tpl = eZTemplate::factory();

try
{
    
    if ( !$repositoryID )
    {
    
        $list = OCCrossSearch::listAvailableRepositories();    
        $tpl->setVariable( 'repository_list', $list );
        $Result = array();
        $Result['content'] = $tpl->fetch( 'design:repository/list.tpl' );
        $Result['path'] = array( array( 'text' => 'Repository', 'url' => false ) );
    }
    elseif ( OCCrossSearch::isAvailableRepository( $repositoryID ) )
    {
        $repository = OCCrossSearch::instanceRepository( $repositoryID );
        $definition = $repository->attribute( 'definition' );
        $tpl->setVariable( 'repository', $repository );
        $Result = array();
        $Result['content'] = $tpl->fetch( $repository->templateName() );
        $Result['path'] = array( array( 'text' => 'Repository', 'url' => 'repository/client' ),
                                 array( 'text' => isset( $definition['Name'] ) ? $definition['Name'] : $repositoryID, 'url' => false ) );
    }
    else
    {
        return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
    }
    
}
catch ( Exception $e )
{
    $Result = array();
    $tpl->setVariable( 'error', $e->getMessage() );
    $Result['content'] = $tpl->fetch( 'design:repository/error.tpl' );
    $Result['path'] = array( array( 'text' => 'Repository', 'url' => false ) );
}