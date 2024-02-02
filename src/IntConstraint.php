<?php

namespace PLAYGROUND;
interface IntConstraint {
    /**
     * Integer to check
     *
     * @param int $value
     * @return bool
     */
	function check( int $value ): bool;
}