<?php

namespace PLAYGROUND;

class DataServiceProvider {
	public function __construct( private readonly MyDataModel $data ) {

	}

    public function add(MyDataModel $data) {}

    private function check(MyDataModel $data) {
        $reflectionObject = new \ReflectionObject($data);
        foreach ( $reflectionObject->getProperties() as $property ) {
            $attributes = $property->getAttributes(
                Constraint::class,
                \ReflectionAttribute::IS_INSTANCEOF
            );
            foreach ( $attributes as $attribute ) {
                $constraint = $attribute->newInstance();
                if ( !$constraint->check( $property->getValue($data) ) ) {
                    trigger_error('Check failed!', E_USER_ERROR);
                } else {
                    print 'Check success!';
                }
            }
        }
    }
}