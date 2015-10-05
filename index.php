<?php
// Maintenance du site.
if (file_exists(__DIR__.'/app/maintenance.php'))
{
    require(__DIR__.'/app/maintenance.php');
    exit();
}

require(__DIR__.'/app/core.php');
?>