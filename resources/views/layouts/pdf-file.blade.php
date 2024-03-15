<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

    <style type="text/css">
        {!! file_get_contents(resource_path('css/pdf.css')) !!}
    </style>
</head>
<body>
<div id="app">
    <div id="report-body">

        <!-- TODO: Добавить в заготовку хедер и футер -->

        @yield('content')

    </div>
</div>

@yield('scripts')
</body>
</html>
