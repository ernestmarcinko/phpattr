<?php

namespace PLAYGROUND;

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