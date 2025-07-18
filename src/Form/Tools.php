<?php

namespace Encore\Admin\Form;

use Encore\Admin\Facades\Admin;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Collection;

class Tools implements Renderable
{
    /**
     * @var Builder
     */
    protected $form;

    /**
     * Collection of tools.
     *
     * @var array<string>
     */
    protected $tools = ['delete', 'view', 'list'];

    /**
     * Tools should be appends to default tools.
     *
     * @var Collection<int|string, mixed>
     */
    protected $appends;

    /**
     * Tools should be prepends to default tools.
     *
     * @var Collection<int|string, mixed>
     */
    protected $prepends;

    /**
     * list path Expressly
     *
     * @var string|null
     */
    protected $listPath;

    /**
     * Create a new Tools instance.
     *
     * @param Builder $builder
     */
    public function __construct(Builder $builder)
    {
        $this->form = $builder;
        $this->appends = new Collection();
        $this->prepends = new Collection();
    }

    /**
     * Append a tools.
     *
     * @param mixed $tool
     *
     * @return $this
     */
    public function append($tool)
    {
        $this->appends->push($tool);

        return $this;
    }

    /**
     * Prepend a tool.
     *
     * @param mixed $tool
     *
     * @return $this
     */
    public function prepend($tool)
    {
        $this->prepends->push($tool);

        return $this;
    }

    /**
     * Disable `list` tool.
     * @param bool $disable
     *
     * @return $this
     */
    public function disableList(bool $disable = true)
    {
        if ($disable) {
            array_delete($this->tools, 'list');
        } elseif (!in_array('list', $this->tools)) {
            array_push($this->tools, 'list');
        }

        return $this;
    }

    /**
     * Disable `delete` tool.
     * @param bool $disable
     *
     * @return $this
     */
    public function disableDelete(bool $disable = true)
    {
        if ($disable) {
            array_delete($this->tools, 'delete');
        } elseif (!in_array('delete', $this->tools)) {
            array_push($this->tools, 'delete');
        }

        return $this;
    }

    /**
     * Disable `edit` tool.
     * @param bool $disable
     *
     * @return $this
     */
    public function disableView(bool $disable = true)
    {
        if ($disable) {
            array_delete($this->tools, 'view');
        } elseif (!in_array('view', $this->tools)) {
            array_push($this->tools, 'view');
        }

        return $this;
    }

    /**
     * Set request path for resource list Expressly.
     * @param string $listPath
     *
     * @return $this
     */
    public function setListPath($listPath)
    {
        $this->listPath = $listPath;

        return $this;
    }

    /**
     * Get request path for resource list.
     * @param bool $directList
     *
     * @return string
     */
    protected function getListPath(bool $directList = false)
    {
        if($directList && !is_null($this->listPath)){
            return $this->listPath;
        }

        return $this->form->getResource();
    }

    /**
     * Get request path for edit.
     *
     * @return string
     */
    protected function getDeletePath()
    {
        return $this->getViewPath();
    }

    /**
     * Get request path for delete.
     *
     * @return string
     */
    protected function getViewPath()
    {
        $key = $this->form->getResourceId();

        if ($key) {
            return $this->getListPath().'/'.$key;
        } else {
            return $this->getListPath();
        }
    }

    /**
     * Get parent form of tool.
     *
     * @return Builder
     */
    public function form()
    {
        return $this->form;
    }

    /**
     * Render list button.
     *
     * @return string
     */
    protected function renderList()
    {
        $text = trans('admin.list');
        $url = url($this->getListPath(true));

        return <<<EOT
<div class="btn-group pull-right" style="margin-right: 5px">
    <a href="{$url}" class="btn btn-sm btn-default d-flex align-items-center p-2" title="$text"><i class="fa fa-list p-1"></i><span class="d-none d-md-inline">&nbsp;$text</span></a>
</div>
EOT;
    }

    /**
     * Render list button.
     *
     * @return string
     */
    protected function renderView()
    {
        $view = trans('admin.view');
        $url = url($this->getViewPath());

        return <<<HTML
<div class="btn-group pull-right" style="margin-right: 5px">
    <a href="{$url}" class="btn btn-sm btn-primary d-flex align-items-center p-2" title="{$view}">
        <i class="fa fa-eye p-1"></i><span class="d-none d-md-inline"> {$view}</span>
    </a>
</div>
HTML;
    }

    /**
     * Render `delete` tool.
     *
     * @return string
     */
    protected function renderDelete()
    {
        $url = url($this->getDeletePath());
        $listUrl = url($this->getListPath(true));
        $trans = [
            'delete_confirm' => trans('admin.delete_confirm'),
            'confirm'        => trans('admin.confirm'),
            'cancel'         => trans('admin.cancel'),
            'delete'         => trans('admin.delete'),
        ];

        $class = uniqid();

        $script = <<<SCRIPT

$('.{$class}-delete').unbind('click').click(function() {

    swal({
        title: "{$trans['delete_confirm']}",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "{$trans['confirm']}",
        showLoaderOnConfirm: true,
        allowOutsideClick: false,
        cancelButtonText: "{$trans['cancel']}",
        preConfirm: function() {
            $('.swal2-cancel').hide();
            return new Promise(function(resolve) {
                $.ajax({
                    method: 'post',
                    url: '{$url}',
                    data: {
                        _method:'delete',
                        _token:LA.token,
                    },
                    success: function (data) {
                        $.pjax({container:'#pjax-container', url: '{$listUrl}' });

                        resolve(data);
                    }
                });
            });
        }
    }).then(function(result) {
        var data = result.value;
        if (typeof data === 'object') {
            if (data.status) {
                swal(data.message, '', 'success');
            } else {
                swal(data.message, '', 'error');
            }
        }
    });
});

SCRIPT;

        Admin::script($script);

        return <<<HTML
<div class="btn-group pull-right" style="margin-right: 5px">
    <a href="javascript:void(0);" class="btn p-1 btn-sm btn-danger d-flex align-items-center {$class}-delete" title="{$trans['delete']}">
        <i class="fa fa-trash p-1"></i><span class="d-none d-md-inline p-1">  {$trans['delete']}</span>
    </a>
</div>
HTML;
    }

    /**
     * Add a tool.
     *
     * @param string $tool
     *
     * @return $this
     *
     * @deprecated use append instead.
     */
    public function add($tool)
    {
        return $this->append($tool);
    }

    /**
     * Disable back button.
     *
     * @return void
     * @deprecated
     */
    public function disableBackButton()
    {
    }

    /**
     * Disable list button.
     *
     * @return $this
     *
     * @deprecated Use disableList instead.
     */
    public function disableListButton()
    {
        return $this->disableList();
    }

    /**
     * Render custom tools.
     *
     * @param Collection<int|string, mixed>|null $tools
     *
     * @return mixed
     */
    protected function renderCustomTools($tools)
    {
        if ($this->form->isCreating()) {
            $this->disableView();
            $this->disableDelete();
        }

        if (empty($tools)) {
            return '';
        }

        return $tools->map(function ($tool) {
            if ($tool instanceof Renderable) {
                return $tool->render();
            }

            if ($tool instanceof Htmlable) {
                return $tool->toHtml();
            }

            return (string) $tool;
        })->implode(' ');
    }

    /**
     * Render tools.
     *
     * @return string
     */
    public function render()
    {
        $output = $this->renderCustomTools($this->prepends);

        foreach ($this->tools as $tool) {
            $renderMethod = 'render'.ucfirst($tool);
            $output .= $this->$renderMethod();
        }

        return $output.$this->renderCustomTools($this->appends);
    }
}
