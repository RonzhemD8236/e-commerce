<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        .container{
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(2, 1fr);

        }

        .item1{
            grid-columns: span 2;
        }

    </style>
</head>
<body>

<div class = "container">
<div class ="item0">
  <form action="">
        <input type="text">
    </form>
</div>
<div class="item1">
  <form action="">
<input type="text" value="tito boy">
    </form>
</div>

<div class="item2">
  <form action="">
    <input type="text">
    </form>
</div>

</div>


  
</body>
</html>