<?php

namespace OpenAdminCore\Admin\Form\Field;

/**
 * Class ListBox.
 *
 * @see https://github.com/istvan-ujjmeszaros/bootstrap-duallistbox
 */
class Listbox extends MultipleSelect
{
    /**
     * @var array<mixed>
     */
    protected $settings = [];

    /**
     * @var array<string>
     */
    protected static $css = [
        '/vendor/open-admin/bootstrap-duallistbox/dist/bootstrap-duallistbox.min.css?v=4.0.2',
    ];

    /**
     * @var array<string>
     */
    protected static $js = [
        '/vendor/open-admin/bootstrap-duallistbox/dist/jquery.bootstrap-duallistbox.min.js?v=4.0.2',
    ];

    /**
     * @param array<mixed> $settings
     * @return $this
     */
    public function settings(array $settings)
    {
        $this->settings = array_merge($this->settings, $settings);

        return $this;
    }

    /**
     * Set listbox height.
     *
     * @param int $height
     *
     * @return Listbox
     */
    public function height($height = 200)
    {
        $this->settings(['minHeight' => $height]);

        return $this;
    }
    /**
     * {@inheritdoc}
     * @param string $url
     * @param array<mixed> $parameters
     * @param array<mixed> $options
     * @return $this
     */
    protected function loadRemoteOptions($url, $parameters = [], $options = [])
    {
        $ajaxOptions = json_encode(array_merge([
            'url' => $url.'?'.http_build_query($parameters),
        ], $options));

        $this->script = <<<EOT
        
$.ajax($ajaxOptions).done(function(data) {

  var listbox = $("{$this->getElementClassSelector()}");

    var value = listbox.data('value') + '';
    
    if (value) {
      value = value.split(',');
    }
    
    for (var key in data) {
        var selected =  ($.inArray(key, value) >= 0) ? 'selected' : '';
        listbox.append('<option value="'+key+'" '+selected+'>'+data[key]+'</option>');
    }
    
    listbox.bootstrapDualListbox('refresh', true);
});
EOT;

        return $this;
    }

    /**
     * @return string
     */
    public function render()
    {
        $this->style('width', '100%');

        $settings = array_merge([
            'availableTitle' => trans('admin.listbox.title_available'),
            'selectedTitle' => trans('admin.listbox.title_selected'),
            'minHeight' => 200,
            // Sometimes, click not working, so double click is false
            'moveOnDoubleClick' => false,
        ], $this->settings);

        $settings = json_encode($settings);

        $this->script .= <<<SCRIPT

        var dualListBox = $("{$this->getElementClassSelector()}");
        dualListBox.bootstrapDualListbox($settings);
        var isRequiredField = dualListBox.attr('required');

        function initDualListBox() {
            var instance = dualListBox.data('plugin_bootstrapDualListbox');
            var nonSelectedList = instance.elements.select1;
            var isDualListBoxValidated = !(instance.selectedElements > 0);
    
            nonSelectedList.prop('required', isDualListBoxValidated);
            instance.elements.originalSelect.prop('required', false);
        }
    
        dualListBox.change(function () {
            if (isRequiredField)
                initDualListBox();
        });
    
        if (isRequiredField)
            initDualListBox();

SCRIPT;

        //$this->attribute('data-value', implode(',', (array) $this->value()));

        return parent::render();
    }
}
