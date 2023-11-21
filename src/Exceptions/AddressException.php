<?php

namespace Booni3\DhlExpressRest\Exceptions;

class AddressException extends \Exception
{
    public static function validationException(string $field)
    {
        throw new static("Validation Exception For Address: $field");
    }
}
