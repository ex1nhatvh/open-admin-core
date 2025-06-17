<div class="box card p-2 box-{{ $style }}">
    <div class="box-header with-border d-flex justify-content-between border-bottom p-1 pb-2">
        <h3 class="box-title">{{ $title }}</h3>

        <div class="box-tools d-flex flex-row-reverse">
            {!! $tools !!}
        </div>
    </div>
    <!-- /.box-header -->
    <!-- form start -->
    <div class="form-horizontal">

        <div class="box-body">

            <div class="fields-group">

                @foreach($fields as $field)
                    {!! $field->render() !!}
                @endforeach
            </div>

        </div>
        <!-- /.box-body -->
    </div>
</div>