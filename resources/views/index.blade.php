<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ Admin::title() }} @if($header) | {{ $header }}@endif</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    @if(!is_null($favicon = Admin::favicon()))
        <link rel="shortcut icon" href="{{$favicon}}">
    @endif

    {!! Admin::css() !!}

    <script src="{{ Admin::jQuery() }}"></script>
    {!! Admin::headerJs() !!}

</head>

<body class="hold-transition {{config('admin.skin')}} {{join(' ', config('admin.layout'))}}">
    <div class="wrapper">

        @include('admin::partials.header')

        @include('admin::partials.sidebar')

        <div class="content-wrapper" id="main">
            <div id="pjax-container">
                {!! Admin::style() !!}
                <div id="app">
                    @yield('content')
                </div>
                {!! Admin::script() !!}
            </div>

        </div>
        @include('admin::partials.footer')


    </div>

    {!! Admin::html() !!}

    <button id="totop" title="Go to top" style="display: none;"><i class="fa fa-chevron-up"></i></button>

    <script>
        function LA() { }
        LA.token = "{{ csrf_token() }}";
    </script>

    <!-- REQUIRED JS SCRIPTS -->
    {!! Admin::js() !!}

</body>

</html>