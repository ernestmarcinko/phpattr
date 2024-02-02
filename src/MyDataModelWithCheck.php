<?php

namespace PLAYGROUND;

/**
 * Data model with type and value check
 */
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