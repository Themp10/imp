<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<style>
.cb{
    width: 40px;
    height: 40px;
    border-radius: 50%;
    vertical-align: middle;
    border: 1px solid #ddd;
    appearance: none;
    -webkit-appearance: none;
    outline: none;
    cursor: pointer;
}
.Noir{
    background-color: black;
  }
  .Yellow{
    background-color: #eaee07;
  }
  .Magenta{
    background-color: #f11cdf;
  }
  .Cyan{
    background-color: #1cf1df;
  }
.cb:checked {
    border: 5px solid red;
}
</style>
<body>
<div class="color-selector">
    <input type="checkbox" name="cb-black" id="cb-black" class="cb Noir">
    <input type="checkbox" name="cb-yellow" id="cb-yellow" class="cb Yellow">
    <input type="checkbox" name="cb-magenta" id="cb-magenta" class="cb Magenta">
    <input type="checkbox" name="cb-cyan" id="cb-cyan" class="cb Cyan">
</div>

<script>

</script>
</body>
</html>