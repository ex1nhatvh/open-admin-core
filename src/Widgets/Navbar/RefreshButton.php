<?php

namespace OpenAdminCore\Admin\Widgets\Navbar;

use OpenAdminCore\Admin\Admin;
use Illuminate\Contracts\Support\Renderable;

class RefreshButton implements Renderable
{
   public function render()
{
    $message = json_encode(__('admin.refresh_succeeded'));
    
    // dd($message);

    $script = <<<SCRIPT
    $(function() {
        $('.refresh-button').off('click').on('click', function() {
            admin.ajax.reload();         
            admin.toastr.success({$message}, {positionClass:"toast-top-center"});                        
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
