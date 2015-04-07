<?php

class OCClassSearchFormQueryField extends OCClassSearchFormField
{

    public function __construct()
    {
        $this->attributes = array(
            'query' => '',
            'label' => 'Ricerca libera',
            'name' => 'query',
            'id' => 'query'
        );
    }

    public function buildFetch( OCClassSearchFormFetcher $fetcher, $requestValue )
    {
        $this->attributes['query'] = $requestValue;
        $fetcher->addFetchField( array(
            'name' => $this->attributes['label'],
            'value' => $this->attributes['query'],
            'remove_view_parameters' => $fetcher->getViewParametersString( array( 'query' ) )
        ));
    }

    public function queryText()
    {
        return $this->attributes['query'];
    }
}

?>