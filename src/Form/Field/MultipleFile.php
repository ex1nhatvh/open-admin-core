<?php

namespace OpenAdminCore\Admin\Form\Field;

use Illuminate\Support\Arr;
use OpenAdminCore\Admin\Form;
use OpenAdminCore\Admin\Form\Field;
use OpenAdminCore\Admin\Form\Field\Traits\HasMediaPicker;
use OpenAdminCore\Admin\Form\Field\Traits\UploadField;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MultipleFile extends Field
{
    use UploadField;
    use HasMediaPicker;

    /**
     * Css.
     *
     * @var array<string>
     */
    protected static $css = [
        '/vendor/open-admin/fields/file-upload/file-upload.css',
    ];

    /**
     * Js.
     *
     * @var array<string>
     */
    protected static $js = [
        '/vendor/open-admin/bootstrap-fileinput/js/plugins/canvas-to-blob.min.js',
        // '/vendor/open-admin/bootstrap-fileinput/js/fileinput.min.js?v=4.5.2',
        '/vendor/open-admin/bootstrap-fileinput/js/plugins/sortable.min.js?v=4.5.2',
    ];

    /**
     * Caption.
     *
     * @var \Closure|null
     */
    protected $caption = null;

    /**
     * file Index.
     *
     * @var \Closure|null
     */
    protected $fileIndex = null;

    public $must_prepare = true;
    public $type = 'file';
    public $readonly = false;
    public $multiple = true;
    /**
     * Create a new File instance.
     *
     * @param string $column
     * @param array<mixed>  $arguments
     */
    public function __construct($column, $arguments = [])
    {
        $this->initStorage();
        $this->must_prepare = true;

        parent::__construct($column, $arguments);
    }

    /**
     * Default directory for file to upload.
     *
     * @return mixed
     */
    public function defaultDirectory()
    {
        return config('admin.upload.directory.file');
    }

    /**
     * {@inheritdoc}
     * @param array<mixed> $input
     *
     * @return bool|\Illuminate\Contracts\Validation\Validator|\Illuminate\Contracts\Validation\Factory
     */
    public function getValidator(array $input)
    {
        if (request()->has(static::FILE_DELETE_FLAG)) {
            return false;
        }

        if ($this->validator) {
            return $this->validator->call($this, $input);
        }

        $attributes = [];

        if (!$fieldRules = $this->getRules()) {
            return false;
        }

        $attributes[$this->column] = $this->label;
        $fileNames = Arr::get($input, $this->column);
        list($rules, $input) = $this->hydrateFiles($fileNames ? (is_array($fileNames) ? $fileNames : $fileNames->toArray()) : []);

        return \validator($input, $rules, $this->getValidationMessages(), $attributes);
    }

    /**
     * Hydrate the files array.
     *
     * @param array<mixed> $value
     *
     * @return array<mixed>
     */
    protected function hydrateFiles(array $value)
    {
        if (empty($value)) {
            return [[$this->column => $this->getRules()], []];
        }

        $rules = $input = [];

        foreach ($value as $key => $file) {
            $rules[$this->column . $key] = $this->getRules();
            $input[$this->column . $key] = $file;
        }

        return [$rules, $input];
    }

    /**
     * Sort files.
     *
     * @param string $order
     *
     * @return array<mixed>
     */
    protected function sortFiles($order, $updated_files)
    {
        $order = explode(',', trim($order, ','));
        if ($updated_files === false) {
            $updated_files = $this->original();
        }

        usort($updated_files, function ($a, $b) use ($order) {
            $pos_a = array_search($a, $order);
            $pos_b = array_search($b, $order);

            if ($pos_a === false || $pos_b === false) {
                return 0;
            }

            return ($pos_a < $pos_b) ? -1 : 1;
        });

        return $updated_files;
    }

    /**
     * Add files.
     *
     * @param string $files
     *
     * @return array
     */
    protected function addFiles($add, $updated_files)
    {
        $add = explode(',', trim($add, ','));
        if ($updated_files === false) {
            $updated_files = $this->original();
        }

        $updated_files = array_merge($updated_files, $add);

        return $updated_files;
    }

    /**
     * Prepare for saving.
     *
     * @param UploadedFile|array<mixed> $files
     *
     * @return mixed|string
     */
    public function prepare($files)
    {
        // If has $files is array, items is all string and has TMP_FILE_PREFIX, get $file
        if (is_array($files) && $this->getTmp) {
            if (
                !collect($files)->contains(function ($file) {
                    // If has $file is string, and has TMP_FILE_PREFIX, get $file
                    return !is_string($file) || strpos($file, File::TMP_FILE_PREFIX) !== 0;
                })
            ) {
                $files = call_user_func($this->getTmp, $files);
            }
        }
        $delete_key = $this->column . Field::FILE_DELETE_FLAG;
        $updated_files = false;
        if (request()->has($delete_key)) {
            if ($this->pathColumn) {
                $updated_files = $this->destroyFromHasMany(request($delete_key));
            } else {
                $updated_files = $this->destroy(request($delete_key));
            }
        }

        if (!empty($this->picker) && request()->has($this->column . Field::FILE_ADD_FLAG)) {
            $updated_files = $this->addFiles(request($this->column . Field::FILE_ADD_FLAG), $updated_files);
        }

        if (is_string($files)) {
            $files = [$files];
        }
        $sort_key = $this->column . static::FILE_SORT_FLAG;
        if (request()->has($sort_key)) {
            if ($this->sortColumn) {
                $updated_files = $this->sortFilesFromHasMany(request($sort_key), $updated_files);
            } else {
                $updated_files = $this->sortFiles(request($sort_key), $updated_files);
            }
        }

        if (!empty($files)) {
            $targets = array_map([$this, 'prepareForeach'], $files);
            // get original
            $original = $this->original();
            if (is_string($original)) {
                $original = [$original];
            }

            // for create or update
            if ($this->pathColumn) {
                $targets = array_map(function ($target) {
                    return [$this->pathColumn => $target];
                }, $targets);
            }

            if ($updated_files === false) {
                $updated_files = $this->original();
            }
            if ($this->sortColumn) {
                foreach ($targets as $key => $value) {
                    $targets[$key][$this->sortColumn] = $key + count($updated_files);
                }
            }

            $updated_files = array_merge($updated_files, $targets);
        }

        return $updated_files;
    }

    /**
     * @return array<mixed>|mixed
     */
    public function original()
    {
        if (empty($this->original)) {
            return [];
        }
        $this->original = $this->fixIfJsonString($this->original);

        return $this->original;
    }

    /**
     * Prepare for each file.
     *
     * @param UploadedFile $file
     *
     * @return mixed|string
     */
    protected function prepareForeach(UploadedFile $file = null)
    {
        $this->name = $this->getStoreName($file);

        return tap($this->upload($file), function () {
            $this->name = null;
        });
    }

    /**
     * Preview html for file-upload plugin.
     *
     * @return array<mixed>
     */
    protected function preview()
    {
        $files = $this->value ?: [];
        if (is_string($files)) {
            $files = [$files];
        }
        $files = $this->fixIfJsonString($files);

        if (!empty($files[0]) && is_array($files[0]) && $this->pathColumn) {
            if ($this->sortColumn) {
                array_multisort(array_column($files, $this->sortColumn), SORT_ASC, $files);
            }
            $files_preview = [];
            foreach ($files as $index => $file) {
                $files_preview[] = Arr::get($file, $this->pathColumn);
            }
            $files = $files_preview;
        }

        return implode(',', array_values($files));
    }

    /**
     * set fileIndex.
     *
     * @param \Closure $fileIndex
     *
     * @return $this
     */
    public function fileIndex($fileIndex)
    {
        $this->fileIndex = $fileIndex;

        return $this;
    }

    /**
     * set caption.
     *
     * @param \Closure $caption
     *
     * @return $this
     */
    public function caption($caption)
    {
        $this->caption = $caption;

        return $this;
    }

    /**
     * Initialize the index.
     *
     * @param mixed $index
     * @param mixed $file
     * @return mixed
     */
    protected function initialFileIndex($index, $file)
    {
        if ($this->fileIndex instanceof \Closure) {
            return $this->fileIndex->call($this, $index, $file);
        }
        return $index;
    }


    public function fixIfJsonString($arr)
    {
        if (!empty($arr) && !is_array($arr)) {
            $arr = json_decode($arr);
        }

        return $arr;
    }

    /**
     * Initialize the caption.
     *
     * @param string $caption
     * @param string $key
     *
     * @return string
     */
    protected function initialCaption($caption, $key)
    {
        if ($this->caption instanceof \Closure) {
            return $this->caption->call($this, $caption, $key);
        }
        return basename($caption);
    }

    /**
     * @return array<mixed>
     */
    protected function initialPreviewConfig()
    {
        $files = $this->value ?: [];

        if (is_string($files)) {
            $files = [$files];
        }

        $config = [];

        foreach ($files as $index => $file) {
            $key = $this->initialFileIndex($index, $file);
            $preview = array_merge([
                'caption' => $this->initialCaption($file, $key),
                'key' => $key,
            ], $this->guessPreviewType($file));

            $config[] = $preview;
        }

        return $config;
    }

    /**
     * Get related model key name.
     *
     * @return string
     */
    protected function getRelatedKeyName()
    {
        if (is_null($this->form)) {
            return;
        }

        return $this->form->model()->{$this->column}()->getRelated()->getKeyName();
    }

    /**
     * Allow to sort files.
     *
     * @return $this
     */
    public function sortable()
    {
        $this->fileActionSettings['showDrag'] = true;

        return $this;
    }

    protected function setType($type = 'file')
    {
        $this->options['type'] = $type;
    }

    /**
     * Destroy original files.
     *
     * @param string $key
     *
     * @return void
     */
    public function destroy($remove_me)
    {
        $remove_me = explode(',', trim($remove_me, ','));

        $files = $this->original() ?: [];

        foreach ($remove_me as $file) {
            $this->destroyFile($file);

            $files = array_diff($files, [$file]);
        }

        return array_values($files);
    }

    public function destroyFile($file)
    {
        if (!$this->retainable && $this->storage->exists($file)) {
            /* If this field class is using ImageField trait i.e MultipleImage field,
            we loop through the thumbnails to delete them as well. */
            if (isset($this->thumbnails) && method_exists($this, 'destroyThumbnailFile')) {
                foreach ($this->thumbnails as $name => $_) {
                    $this->destroyThumbnailFile($file, $name);
                }
            }
            $this->storage->delete($file);
        }
    }

    /**
     * Destroy original files from hasmany related model.
     *
     * @param int $key
     *
     * @return array
     */
    public function destroyFromHasMany($remove_me)
    {
        $remove_me = explode(',', trim($remove_me, ','));

        $files = collect($this->original ?: [])->keyBy($this->getRelatedKeyName())->toArray();

        foreach ($files as $key => $file_obj) {
            $file = $file_obj[$this->pathColumn];
            if (in_array($file, $remove_me)) {
                $this->destroyFile($file);
                $files[$key][Form::REMOVE_FLAG_NAME] = 1;
            }
        }

        return $files;
    }

    /**
     * Sort files.
     *
     * @param string $order
     * @param array  $files
     *
     * @return array
     */
    protected function sortFilesFromHasmany($order, $files)
    {
        $order = explode(',', trim($order, ','));
        if ($files === false) {
            $files = collect($this->original ?: [])->keyBy($this->getRelatedKeyName())->toArray();
        }

        foreach ($files as $key => $file_obj) {
            $file = $file_obj[$this->pathColumn];
            $files[$key][$this->sortColumn] = array_search($file, $order);
        }

        return $files;
    }

    protected function getFieldId()
    {
        if (!empty($this->elementName)) {
            $id = $this->elementName;
        } else {
            $id = $this->id;
        }
        $id = str_replace(']', '_', $id);
        $id = str_replace('[', '_', $id);

        return $id;
    }

    /**
     * Setupscript.
     *
     * @return nothing
     */
    protected function setupScripts($options)
    {
        $this->script = <<<EOT
$("{$this->getElementClassSelector()}").fileinput({$options});
EOT;

        if ($this->fileActionSettings['showRemove']) {
            $text = [
                'title' => trans('admin.delete_confirm'),
                'confirm' => trans('admin.confirm'),
                'cancel' => trans('admin.cancel'),
            ];

            $this->script .= <<<EOT
$("{$this->getElementClassSelector()}").on('filebeforedelete', function() {
    
    return new Promise(function(resolve, reject) {
    
        var remove = resolve;
    
        swal({
            title: "{$text['title']}",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "{$text['confirm']}",
            showLoaderOnConfirm: true,
            cancelButtonText: "{$text['cancel']}",
            preConfirm: function() {
                return new Promise(function(resolve) {
                    resolve(remove());
                });
            }
        });
    });
});
EOT;
            if(isset($this->options['deletedEvent'])){
                $deletedEvent = $this->options['deletedEvent'];
                $this->script .= <<<EOT
                $("{$this->getElementClassSelector()}").on('filedeleted', function(event, key, jqXHR, data) {
                    {$deletedEvent};
                });
EOT;
            }
        }

        if ($this->fileActionSettings['showDrag']) {
            $this->addVariables([
                'sortable'  => true,
                'sort_flag' => static::FILE_SORT_FLAG,
            ]);

            $this->script .= <<<EOT
$("{$this->getElementClassSelector()}").on('filesorted', function(event, params) {
    
    var order = [];
    
    params.stack.forEach(function (item) {
        order.push(item.key);
    });
    
    $("{$this->getElementClassSelector()}_sort").val(order);
});
EOT;
        }
    }

    /**
     * Render file upload field.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    {
        $this->attribute('multiple', true);
        $this->setupDefaultOptions();

        if (empty($this->value)) {
            $this->value = [];
        }

        if ($this->picker) {
            $this->renderMediaPicker();
        }

        if (!is_array($this->value)) {
            //try decoding json
            $this->value = json_decode($this->value);
            if (!is_array($this->value)) {
                throw new \Exception('Column: ' . $this->column . ' with Label: ' . $this->label . '; value is not empty and not a valid Array');
            }
        }

        if (!empty($this->value)) {
            $this->attribute('data-files', $this->preview());
            $this->setupPreviewOptions();
            /*
             * If has original value, means the form is in edit mode,
             * then remove required rule from rules.
             */
            unset($this->attributes['required']);
        }

        $options = json_encode($this->options);

        $this->setupScripts($options);

        return parent::render();
    }
}
