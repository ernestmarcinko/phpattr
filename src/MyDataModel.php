<?php

namespace PLAYGROUND;

#[\Attribute]
class Maximum {
	function __construct( private readonly int $value ) {}
}

class MyDataModel {
	#[Maximum(30)]
    public int $number = 0;
}