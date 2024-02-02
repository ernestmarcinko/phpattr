# PHP Attributes for data validation

While looking at the [tsed attribute decorator example](https://tsed.io/docs/model.html#example), I was wondering if there is any
way to implement similar feature in PHP via the [attribute features](https://www.php.net/manual/en/language.attributes.overview.php).

PHP attributes are not decorators though, they can be interpreted and implemented in many different ways.

This is a possible implementation (two actually), and it's a reminder to myself for future reference as I'm old
and I forget things.

_Disclaimer: This may have been done before, I don't know. I just wanted to explore
what attributes can do._

## Data Model
Consider a data model, where we want to describe certain properties with value based
restrictions.

```php
readonly class MyDataModel {
    public function __construct(
        #[MaximumInt(30)]
        public int $number1 = 0,

        #[MinimumInt(40), MaximumInt(50)]
        public int $number2 = 0
    ) {}
}
```

This looks clean as we can define the constraints **in the model itself**.
In the examples below I will explore two ways to validate this data model:
 - When used by a [Provider Service](#check-by-provider)
 - Encapsulated in the [Data Model](#check-by-data-model-constructor) itself (self-check)

## Attributes

The attribute classes could implement a check() method, which could be used for checks later on
in the Reflections.

For example the **MaximumInt** attribute class:

```php
#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class MaximumInt implements IntConstraint {
    /**
     * The maximum value allowed via #[Maximum(value)]
     * @param int $value
     */
    public function __construct( private int $value ) {}

    /**
     *
     * @param int $value
     * @return bool
     */
    public function check( int $value ): bool {
        return $this->value >= $value;
    }
}
```

Let's use a common interface for integer related checks for convenience.
The [ReflectionProperty::getAttributes](https://www.php.net/manual/en/reflectionproperty.getattributes.php) function can filter by common ancestors, so it becomes handy later on.

```php
interface IntConstraint {
    /**
     * Integer to check
     *
     * @param int $value
     * @return bool
     */
	function check( int $value ): bool;
}
```

Later when fetching for the attributes, this IntConstraint can be used to fetch only attributes
which **implement this interface only**.

## Check By Provider

Say we have some sort of data store, where we want to push/pop data in and out - but only if the data model has valid data.
For the sake of simplicity this provider will do the checks internally using the 
injected data (see `check( MyDataModel $data )` method).

### Pros
The data is only checked when it's actually used and not upon creation.

### Cons
Basically the same as the pros, the data exists invalid until used.

```php
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
```

For example:

```php
$myData1 = new MyDataModel(...[
    'number1' => 3, // Max 30
    'number2' => 30 // Min 40, Max 50 --> Should fail
]);
$dataServiceProviderWithCheck = new DataServiceProviderWithCheck();
// Validation only triggers here
$dataServiceProviderWithCheck->push($myData1);
```

## Check by Data Model constructor

Alternatively, we can do the data validity check **when the data model is created**.

Consider an abstract data model, which implements a self attribute check, and could be reused for
any future data models. The check method is basically the same here as in the previous
example, except it's done on self via **$this**.

### Pros
The data model is validated on creation.

### Cons
Extra overhead if execution stops before the data model is used.

```php
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
```

The data model implementing **AbstractDataModelWithCheck** will look almost the same:

```php
readonly class MyDataModelWithCheck extends AbstractDataModelWithCheck {
    public function __construct(
        #[MaximumInt(30)]
        public int $number1 = 0,

        #[MinimumInt(40), MaximumInt(50)]
        public int $number2 = 0
    ) {
        parent::__construct();
    }
}
```

Finally, the provider does not have to deal with validation anymore:

```php
class DataServiceProvider {
    /**
     * @var MyDataModelWithCheck[]
     */
    private array $dataStore = [];
    public function push(MyDataModelWithCheck $data): void {
		$this->dataStore[] = $data;
    }

    public function pop(): ?MyDataModelWithCheck {
        return array_pop($this->dataStore);
    }
}
```

For example:

```php
$myData2 = new MyDataModelWithCheck(...[
    'number1' => 31, // Max 30
    'number2' => 44 // Min 40, Max 50 --> Should fail
]);
// Validation already finished here

$dataServiceProvider = new DataServiceProvider();
$dataServiceProvider->push($myData2);
```