<?php
/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();

$repositoryID = $Params['RepositoryID'];
$repositoryNodeID = $Params['NodeID'];
$localParentNodeID = $Params['ParentNodeID'];

try
{
    if ( OCCrossSearch::isAvailableRepository( $repositoryID ) )
    {
        $repository = OCCrossSearch::instanceRepository( $repositoryID );
       
        if ( $http->hasPostVariable( 'SelectedNodeIDArray' ) and
             $http->postVariable( 'BrowseActionName' ) == 'FindRepositoryImportParentNode' and
             !$http->hasPostVariable( 'BrowseCancelButton' ) )
        {
            $selectedNodeIDArray = $http->postVariable( 'SelectedNodeIDArray' );
            $localParentNodeID = $selectedNodeIDArray[0];
        }
        
        if ( !$localParentNodeID )
        {
            eZContentBrowse::browse( array( 'action_name' => 'FindRepositoryImportParentNode',
                                            'from_page' => '/repository/import/' . $repositoryID . '/' . $repositoryNodeID ),
                                             $module );
            return;
        }
        
        // ::: EDIT by SZ :::
        $ini = eZINI::instance( 'ocrepository.ini' );

        if ( $ini->hasVariable( 'Client_' . $repositoryID, 'AskTagTematica' )
             && $ini->variable(
                'Client_' . $repositoryID,
                'AskTagTematica'
            ) == 'true'
        )
        {

            $tagIDs = "";
            $tagKeywords = "";
            $tagParents = "";

            if ( !$http->hasPostVariable( 'SelectTags' ) )
            {
                $tpl->setVariable(
                    'fromPage',
                    '/repository/import/' . $repositoryID . '/' . $repositoryNodeID
                );
                $tpl->setVariable( 'localParentNodeID', $localParentNodeID );

                $Result['content'] = $tpl->fetch( 'design:repository/eztagschooser.tpl' );
                $Result['path'] = array(
                    array(
                        'url' => false,
                        'text' => 'Scegli Tag'
                    )
                );

                return;
            }
            else
            {
                foreach ( $_POST as $post_key => $post_val )
                {
                    if ( substr( $post_key, 0, 8 ) == 'tematica' )
                    {
                        $tematica = explode( ";", $post_val );

                        $tagIDs .= $tematica[0] . '|#';
                        $tagKeywords .= $tematica[1] . '|#';
                        $tagParents .= $tematica[2] . '|#';
                    }
                }
            }
            $newObject = $repository->import( $repositoryNodeID, $localParentNodeID );

            foreach ( $newObject->contentObjectAttributes() as $attribute )
            {
                if ( $attribute->contentClassAttributeIdentifier() == 'tematica' )
                {
                    $eZTags = new eZTags();

                    $eZTags->createFromStrings( $tagIDs, $tagKeywords, $tagParents );
                    $eZTags->store( $attribute );

                    break;
                }
            }
        }
        else
        {
            $newObject = $repository->import( $repositoryNodeID, $localParentNodeID );
        }
        // ::: :::
        
        $module->redirectTo( $newObject->attribute( 'main_node' )->attribute( 'url_alias' ) );        
    }
    else
    {
        $module->redirectTo( 'repository/client' );
    }
    
}
catch ( Exception $e )
{
    $Result = array();
    $tpl->setVariable( 'error', $e->getMessage() );
    eZDebug::writeNotice( $e->getTraceAsString(), $e->getMessage() );
    $Result['content'] = $tpl->fetch( 'design:repository/error.tpl' );
    $Result['path'] = array( array( 'text' => 'Repository', 'url' => false ) );
}
