<?php
$content = file_get_contents('includes/db.php');
if (strpos($content, 'function_exists(\'getallheaders\')') === false) {
    $polyfill = "
if (!function_exists('getallheaders')) {
    function getallheaders() {
        \$headers = [];
        foreach (\$_SERVER as \$name => \$value) {
            if (substr(\$name, 0, 5) == 'HTTP_') {
                \$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr(\$name, 5)))))] = \$value;
            }
        }
        return \$headers;
    }
}
";
    $content = preg_replace('/<\?php\s*/', "<?php\n" . $polyfill, $content, 1);
    file_put_contents('includes/db.php', $content);
}
