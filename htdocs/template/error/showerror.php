<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>500 Internal Server Error</title>
</head>
<body>
<h1>500 Internal Server Error</h1>
<h2><?php echo $type.' ('.$code.')'; ?></h2>
<ul>
<li><b>Message:</b> <?php echo $message; ?></li>
<li><b>File:</b> <?php echo $file; ?></li>
<li><b>Line:</b> <?php echo $line; ?></li>
</ul>
</body>
</html>
