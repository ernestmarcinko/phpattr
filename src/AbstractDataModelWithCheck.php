<?php

namespace PLAYGROUND;

readonly abstract class AbstractDataModelWithCheck {
    function __construct() {
        $reflectionObject = new \ReflectionObject($this);
        foreach ( $reflectionObject->getProperties() as $property ) {
            /**
             * Get any attribute that implements the IntConstraint interface
             */
            $attributes = $property->getAttributes(
                IntConstraint::class,
                \ReflectionAttribute::IS_INSTANCEOF
            );
            foreach ( $attributes as $attribute ) {
                $constraint = $attribute->newInstance();
                $args = implode($attribute->getArguments());
                /**
                 * We can safely call the check() method because of the contract with
                 * the IntConstraint interface.
                 */
                if ( !$constraint->check( $property->getValue($this) ) ) {
                    print "Check failed on {$attribute->getName()}, value given: {$property->getValue($this)} checked against: $args \r\n";
                    return false;
                } else {
                    print "Check success on {$attribute->getName()}, value given: {$property->getValue($this)} checked against: $args \r\n";
                }
            }
        }

        return true;
    }
}