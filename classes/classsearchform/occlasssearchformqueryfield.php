<?php

class OCClassSearchFormQueryField extends OCClassSearchFormField
{
    protected $query;

    public function buildFetch( OCClassSearchFormFetcher $fetcher, $requestValue )
    {
        $this->query = $requestValue;
        $fetcher->addFetchField( array(
            'name' => 'Ricerca libera',
            'value' => $this->query,
            'remove_view_parameters' => $fetcher->getViewParametersString( array( 'query' ) )
        ));
    }

    public function queryText()
    {
        return $this->query;
    }
}

?>