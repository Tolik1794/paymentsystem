<?php

namespace payment\PaymentSystem\Portmone\entities;

class BaseEntity
{
    protected $data;

    public function __construct(\stdClass $data)
    {
        $this->data = $data;
    }

    /**
     * Get dynamical properties according to origin XML structure
     * @param $propertyName
     * @param \stdClass $data
     * @return string|float|int|bool
     */
    protected function getProperty($propertyName, $data = null)
    {
        if (null == $data) {
            $data = $this->data;
        }
        // parse property name
        $parts = explode('->', $propertyName);
        if (1 == count($parts)) {
            // 1-st level property
            if (isset($data->$propertyName) && !is_object($data->$propertyName)) {
                return $data->$propertyName;
            } else {
                return false;
            }
        } else {
            // has sublevels
            $first = array_shift($parts); // has 1-st level name
            if (isset($data->$first)) {
                return $this->getProperty(implode('->', $parts), $data->$first); // recursion!
            } else {
                return false;
            }
        }
    }
}