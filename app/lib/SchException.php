<?php

namespace Builder;

class SchException extends \Klein\Exception
{
    public function __toString()
    {
        return get_called_class() . ' with message \'' . $this->original_message . '\'';
    }
}
