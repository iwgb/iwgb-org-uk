<?php

header('Cache-Control: no-cache');

echo <<<'HTML'

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Loading... &middot; IWGB</title>
    <meta http-equiv="refresh" content="5">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@1,700&display=swap" rel="stylesheet">
    <style>
        body {
            height: 100vh;
            font-family: "Fira Sans", Helvetica Neue, Verdana, sans-serif;
            margin: 0;
        }

        .container {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            font-style: italic;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <p><i class="fa fa-4x fa-spin fa-spinner"></i></p>
        <h2 id="message">Loading...</h2>
        <div>Hang in there, we won't be a moment</div>
    </div>

    <script>
        (() => {
            document.getElementById('message').textContent = [
                'Finishing off the placards...',
                'Unpacking the vuvuzelas...',
                'Putting the workers first...',
                'Mailing out the ballot papers...',
                'Filing groundbreaking employment law cases...'
            ][Math.floor(Math.random() * 5)];
        })();
    </script>
</body>
</html>

HTML;