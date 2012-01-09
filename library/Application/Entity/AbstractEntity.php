<?php

namespace Application\Entity;

abstract class AbstractEntity implements \ArrayAccess {

    public function __get($key) {
        return $this->$key;
    }

    public function __set($key, $value) {
        $this->$key = $value;
    }

    public function offsetExists($offset) {
        return isset($this->$offset);
    }

    public function offsetGet($offset) {
        return $this->__Get($offset);
    }

    public function offsetSet($offset, $value) {
        return $this->__set($offset, $value);
    }

    public function offsetUnset($offset) {
        $this->$offset = null;
    }

}