<!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8'>
<title>404 Not Found</title>
</head>
<body>
<h1>404 Not Found</h1>
<?php
echo "<div>".htmlspecialchars(urldecode($_SERVER['REQUEST_URI']))."</div>\n";
echo '<div>'.$message.'</div>';
?>
</body>
</html>
