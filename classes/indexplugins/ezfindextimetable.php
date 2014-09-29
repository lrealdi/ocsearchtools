<?php

if ( interface_exists( 'ezfIndexPlugin' ) )
{
    class ezfIndexTimeTable implements ezfIndexPlugin
    {
        public function modify( eZContentObject $contentObject, &$docList )
        {            
            $isTimeTable = false;
            $dataMap = $contentObject->attribute( 'data_map' );
            if ( isset( $dataMap['timetable'] ) && $dataMap['timetable'] instanceof eZContentObjectAttribute )
            {
                $isTimeTable = $dataMap['timetable']->attribute( 'has_content' );
            }            
            if ( $isTimeTable )
            {                            
                $timeTableContent = $dataMap['timetable']->attribute( 'content' )->attribute( 'matrix' );
                $timeTable = $timeTableContent['columns']['sequential'];
                echo '<pre>';print_r($timeTable);
                $version = $contentObject->currentVersion();
                if( $version === false )
                {
                    return;
                }
                $availableLanguages = $version->translationList( false, false );
                foreach ( $availableLanguages as $languageCode )
                {
                    $docList[$languageCode]->addField( 'extra_timetable_s', 0 );
                }
            }
    
        }
    }
}

?>
