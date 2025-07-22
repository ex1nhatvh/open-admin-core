<?php

namespace OpenAdminCore\Admin\Form\Field;

class Decimal extends Text
{
    /**
     * @var array<string>
     */
    protected static $js = [
        '/vendor/open-admin/inputmask/inputmask.min.js',
    ];

    /**
     * @see https://github.com/RobinHerbots/Inputmask#options
     *
     * @var array<string, string|bool>
     */
    protected $options = [
        'alias'      => 'decimal',
        'rightAlign' => true,
    ];

    /*
     * @return string
     */
    public function render()
    {
        $this->inputmask($this->options);

        $this->prepend('<i class="'.$this->icon.'"></i>');
        $this->style('max-width', '160px');

        return parent::render();
    }
}
