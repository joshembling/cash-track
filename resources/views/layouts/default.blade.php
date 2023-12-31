<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
      .chat {
        list-style: none;
        margin: 0;
        padding: 0;
      }

      .chat li {
        margin-bottom: 10px;
        padding-bottom: 5px;
        border-bottom: 1px dotted #B3A9A9;
      }

      .chat li .chat-body p {
        margin: 0;
        color: #777777;
      }

        p {
        margin: .1rem 0 1rem;
      }

      .panel-body {
        overflow-y: scroll;
        height: 350px;
      }

      ::-webkit-scrollbar-track {
        -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
        background-color: #F5F5F5;
      }

      ::-webkit-scrollbar {
        width: 12px;
        background-color: #F5F5F5;
      }

      ::-webkit-scrollbar-thumb {
        -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,.3);
        background-color: #555;
      }
    </style>
</head>
<body>
    <div style="padding: 2rem">
        @yield('content')
    </div>
</body>
</html>
