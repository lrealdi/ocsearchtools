<?php
class OCClassSearchFormAttributeNumeric extends OCClassSearchFormAttributeBoundable
{
    /**
     * @return OCClassSearchFormFieldBoundsInterface
     */
    function getBoundsClassName()
    {
        return 'OCClassSearchFormNumericFieldBounds';
    }
}