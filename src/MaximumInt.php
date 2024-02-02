<?php

namespace PLAYGROUND;
#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class MaximumInt implements IntConstraint {
    /**
     * The maximum value allowed via #[Maximum(value)]
     * @param int $value
     */
    public function __construct( private int $value ) {}

    /**
     *
     * @param mixed $value
     * @return bool
     */
    public function check( mixed $value ): bool {
		return $this->value >= $value;
	}
}