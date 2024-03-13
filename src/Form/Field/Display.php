<?php

namespace OpenAdminCore\Admin\Form\Field;

use OpenAdminCore\Admin\Form\Field;

class Display extends Field
{
    public function prepare($value)
    {
        return $this->original();
    }
}
