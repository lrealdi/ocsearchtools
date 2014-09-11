<?php

class OCClassSearchTemplate
{
        
    protected $attributes = array();

    protected $functionAttributes = array();
    
    public function attribute( $name )
    {        
        if ( isset( $this->attributes[$name] ) )
        {
            return $this->attributes[$name];
        }
        elseif ( isset( $this->functionAttributes[$name] ) )
        {
            return call_user_func( array( $this, $this->functionAttributes[$name] ) );
        }
        eZDebug::writeError( "Attribute $name not found", __METHOD__ );
        return false;
    }    

    public function attributes()
    {
        return array_keys( $this->attributes );
    }

    public function hasAttribute( $name )
    {
        return isset( $this->attributes[$name] ) || isset( $this->functionAttributes[$name] );
    }

    public function setAttributes( $attributes )
    {
        $this->attributes = $attributes;
    }
}
