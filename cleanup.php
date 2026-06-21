<?php
$dir = new RecursiveDirectoryIterator('C:/xampp/htdocs/fisync-erp/resources/views');
$iter = new RecursiveIteratorIterator($dir);
foreach ($iter as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) == 'php') {
        $content = file_get_contents($file);
        $content = str_replace(' border-0', '', $content);
        $content = str_replace(' shadow-sm', '', $content);
        $content = str_replace(' style="border-radius: 14px;"', '', $content);
        file_put_contents($file, $content);
    }
}
echo "Cleanup done.\n";
