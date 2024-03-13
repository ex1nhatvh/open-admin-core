<?php

namespace OpenAdminCore\Admin\Form\Field;

use OpenAdminCore\Admin\Form\Field\Traits\BelongsToRelation;

class BelongsTo extends Select
{
    use BelongsToRelation;

    protected $relation_prefix = 'belongsto-';
    protected $relation_type = 'one';

    protected function getOptions()
    {
        $options = [];

        if ($value = $this->value()) {
            $options = [$value => $value];
        }

        return $options;
    }
}
