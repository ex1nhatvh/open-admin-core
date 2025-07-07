<?php

namespace OpenAdminCore\Admin\Form\Field;

class Time extends Date
{
    /**
     * @var string
     */
    protected $format = 'HH:mm:ss';

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|string
     */
    public function render()
    {
        $this->prepend('<i class="icon-clock"></i>');
        $this->style('max-width', '160px');
        $this->options['noCalendar'] = true;

        return parent::render();
    }
}
