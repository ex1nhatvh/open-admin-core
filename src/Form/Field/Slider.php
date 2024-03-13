<?php

namespace OpenAdminCore\Admin\Form\Field;

use OpenAdminCore\Admin\Form\Field;
use OpenAdminCore\Admin\Form\Field\Traits\HasNumberModifiers;

class Slider extends Field
{
    use HasNumberModifiers;

    public function render()
    {
        $this->attribute('value', old($this->elementName ?: $this->column, $this->value()));

        return parent::render();
    }
}
