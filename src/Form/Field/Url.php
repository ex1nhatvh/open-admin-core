<?php

namespace OpenAdminCore\Admin\Form\Field;

class Url extends Text
{
    /**
     * @var string
     */
    protected $rules = 'nullable|url';

    public function setForm(Form $form = null)
    {
        $this->form = $form;
        // field type url has a default browser validation
        $this->form->enableValidate();

        return $this;
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function render()
    {
        $this->prepend('<i class="fa fa-internet-explorer fa-fw"></i>')
            ->defaultAttribute('type', 'url');

        return parent::render();
    }
}
