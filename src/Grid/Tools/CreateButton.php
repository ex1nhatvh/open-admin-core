<?php

namespace Encore\Admin\Grid\Tools;

use Encore\Admin\Grid;

class CreateButton extends AbstractTool
{
    /**
     * @var Grid
     */
    protected $grid;

    /**
     * Create a new CreateButton instance.
     *
     * @param Grid $grid
     */
    public function __construct(Grid $grid)
    {
        $this->grid = $grid;
    }

    /**
     * Render CreateButton.
     *
     * @return string
     */
    public function render()
    {
        if (!$this->grid->showCreateBtn()) {
            return '';
        }

        $new = trans('admin.new');

        return <<<EOT

<div class="btn-group pull-right" style="margin-right: 10px">
    <a href="{$this->grid->getCreateUrl()}" class="btn btn-sm btn-success p-2" title="{$new}">
        <i class="fa fa-plus"></i><span class="d-none d-md-inline">&nbsp;&nbsp;{$new}</span>
    </a>
</div>

EOT;
    }
}
