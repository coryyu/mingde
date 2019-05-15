<?php

namespace AlibabaCloud\Client\Resolver;

use RuntimeException;

/**
 * Trait CallTrait
 *
 * @internal
 * @codeCoverageIgnore
 * @package AlibabaCloud\Client\Resolver
 */
trait CallTrait
{
    /**
     * Magic method for set or get request parameters.
     *
     * @param string $name
     * @param mixed  $arguments
     *
     * @return $this
     */
    public function __call($name, $arguments)
    {
        if (strncmp($name, 'get', 3) === 0) {
            $parameter = $this->propertyNameByMethodName($name);

            return $this->__get($parameter);
        }

        if (strncmp($name, 'with', 4) === 0) {
            $parameter = $this->propertyNameByMethodName($name, 4);

            $value                                    = $arguments[0];
            $this->data[$parameter]                   = $value;
            $this->parameterPosition()[$parameter] = $value;

            return $this;
        }

        if (strncmp($name, 'set', 3) === 0) {
            $parameter   = $this->propertyNameByMethodName($name);
            $with_method = "with$parameter";

            return $this->$with_method($arguments[0]);
        }

        throw new RuntimeException('Call to undefined method ' . __CLASS__ . '::' . $name . '()');
    }
}
