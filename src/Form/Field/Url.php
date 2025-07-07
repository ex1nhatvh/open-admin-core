<?php

namespace OpenAdminCore\Admin\Form\Field;

use OpenAdminCore\Admin\Form;

class Url extends Text
{
    /**
     * @var string
     */
    protected $rules = 'nullable|url';

    /**
     * {@inheritdoc}
     * @return string
     */

    public function setForm($form = null)
    {
        $this->form = $form;
        // field type url has a default browser validation
        $this->form->enableValidate();

        return $this;
    }

    public function render()
    {
        $this->prepend('<i class="fa fa-internet-explorer fa-fw"></i>')
            ->defaultAttribute('type', 'url');

        return parent::render();
    }
}
