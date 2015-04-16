<?php

interface OCClassSearchFormFieldBoundsInterface
{
    public function attributes();

    public function attribute( $key );

    public function hasAttribute( $key );

    public static function fromString( $string );

    public function setStart( $timestamp );

    public function setEnd( $timestamp );

    public function humanString();

    public function __toString();
}