<?php

namespace OpenAdminCore\Admin\Widgets\Navbar;

use OpenAdminCore\Admin\Admin;
use Illuminate\Contracts\Support\Renderable;

class RefreshButton implements Renderable
{
    public function render()
{
    $message = json_encode(__('admin.refresh_succeeded'));
    $script = <<<SCRIPT
    const message = {$message};
    /**
     * Show a success message using Toastr.
     */
    function showToastrSuccess() {
        toastr.success(message, '', {
            closeButton: true,
            progressBar: true,
            positionClass: "toast-top-center",
            timeOut: 4000,
            showMethod: 'slideDown'
        });
    }

    $(function() {
        $('.refresh-button').off('click').on('click', function() {
            admin.ajax.reload();
            showToastrSuccess();
        });
    });
SCRIPT;

    Admin::script($script);

    return <<<'EOT'
<li>
    <a href="javascript:void(0);" class="refresh-button d-none d-md-block" style="padding:15.5px 11.5px;">
      <i class="fa fa-refresh"></i>
    </a>
</li>
EOT;
}

}
