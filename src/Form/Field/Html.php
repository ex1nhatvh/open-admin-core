<?php

namespace OpenAdminCore\Admin\Form\Field;

use Illuminate\Support\Arr;
use OpenAdminCore\Admin\Form\Field;

class Html extends Field
{
    /**
     * Htmlable.
     *
     * @var string|\Closure
     */
    protected $html = '';

    /**
     * @var string
     */
    protected $label = '';

    /**
     * @var bool
     */
    protected $plain = false;

    /**
     * Create a new Html instance.
     *
     * @param mixed $html
     * @param array<mixed> $arguments
     */
    public function __construct($html, $arguments)
    {
        $this->html = $html;

        $this->label = Arr::get($arguments, 0);
    }

    /**
     * @return $this
     */
    public function plain()
    {
        $this->plain = true;

        return $this;
    }

    /**
     * Render html field.
     *
     * @return string
     */
    public function render()
    {
        if ($this->html instanceof \Closure) {
            $this->html = $this->html->call($this->form->model(), $this->form);
        }

        if ($this->plain) {
            return $this->html;
        }

        $viewClass = $this->getViewElementClasses();

        return <<<EOT
<div class="form-group row">
    <label  class="{$viewClass['label']} control-label text-end mt-2">{$this->label}</label>
    <div class="{$viewClass['field']} ps-3">
        {$this->html}
    </div>
</div>
EOT;
    }
}
