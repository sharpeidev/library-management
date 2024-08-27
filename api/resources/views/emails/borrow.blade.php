<!DOCTYPE html>
<html lang="en">
<head>
    <title>Book borrowing confirmation email</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
        }
        div#main {
            background-color: #f4f4f4;
            padding: 20px;
            text-align: center;
        }
        .title {
            color: #333;
        }
    </style>
</head>
<body>
    <div id="main">
        <h1 class="title">You borrowed the book {{ $book ?? '' }} on {{ $date ?? '' }}.</h1>
        <h2 class="title">Have a good day!</h2>
    </div>
</body>
</html>
