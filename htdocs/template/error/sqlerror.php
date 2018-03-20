<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>500 Internal Server Error</title>
</head>
<body>
<h1>500 Internal Server Error</h1>
<h2>SQL Error</h2>
<ul>
<li><b>Message:</b> <?php if ($code) echo '('.$code.') '; echo $message; ?></li>
<?php if ($query): ?>
<li><b>Query:</b> <?php echo htmlspecialchars($query); ?></li>
<?php endif; ?>
<?php if ($param): ?>
<li>
    <b>Param:</b>
    <ol>
<?php foreach ($param as $p): ?>
        <li><?php echo htmlspecialchars($p); ?></li>
<?php endforeach; ?>
    </ol>
</li>
<?php endif; ?>
<li><b>File:</b> <?php echo $file; ?></li>
<li><b>Line:</b> <?php echo $line; ?></li>
</ul>
</body>
</html>
