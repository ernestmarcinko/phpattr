<?php

namespace PLAYGROUND;

class DataServiceProviderWithCheck {
    /**
     * @var MyDataModel[]
     */
    private array $dataStore = [];
    public function push(MyDataModel $data): void {
        if ( $this->check($data) ) {
            $this->dataStore[] = $data;
        }
    }

    public function pop(): ?MyDataModel {
        return array_pop($this->dataStore);
    }

    private function check(MyDataModel $data): bool {
        $reflectionObject = new \ReflectionObject($data);
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
                if ( !$constraint->check( $property->getValue($data) ) ) {
                    print "Check failed on {$attribute->getName()}, value given: {$property->getValue($data)} checked against: $args \r\n";
                    return false;
                } else {
                    print "Check success on {$attribute->getName()}, value given: {$property->getValue($data)} checked against: $args \r\n";
                }
            }
        }

        return true;
    }
}