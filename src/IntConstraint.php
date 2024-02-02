<?php

namespace PLAYGROUND;
interface IntConstraint {
    /**
     * @param mixed $value
     * @return bool
     */
	function check( mixed $value ): bool;
}