<?php
$remaining_time = strtotime('2015-10-18 00:00:00')-time();
$nb_days = floor($remaining_time/86400);
$remaining_time = $remaining_time % 86400;
$nb_hours = floor($remaining_time/3600);
$remaining_time = $remaining_time % 3600;
$nb_minutes = floor($remaining_time/60);
$remaining_time = $remaining_time % 60;
$remaining_time = '';
if ($nb_days > 0)
{
    if ($nb_days == 1)
    {
        $remaining_time .= $nb_days.' jour ';
    }
    else
    {
        $remaining_time .= $nb_days.' jours ';
    }
}
if ($nb_hours > 0)
{
    if ($nb_hours == 1)
    {
        $remaining_time .= $nb_hours.' heure ';
    }
    else
    {
        $remaining_time .= $nb_hours.' heures ';
    }
}
if ($nb_minutes > 0)
{
    if ($nb_minutes == 1)
    {
        $remaining_time .= $nb_minutes.' minute ';
    }
    else
    {
        $remaining_time .= $nb_minutes.' minutes ';
    }
}
$remaining_time = (empty($remaining_time) == FALSE) ? (trim($remaining_time)) : ('Inconnu');
?>

<!DOCTYPE html>	
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr-FR" lang="fr-FR">
	<head>
		<title>Club-Manga - Maintenance</title>
		<meta name='description' content='Club-Manga en maintenance' />
		<meta name='author' content='Fiyery' />
		<meta http-equiv='content-type' content='text/html; charset=utf-8' />
		<link rel='icon' type='image/x-icon' href='ressources/img/design/favicon.ico'/>
		<meta name="viewport" content="width=613" />
		<style>
            body 
            {
                text-align: center;
            }
            
            #bloc
            {
                width: 613px;
                height: 290px;
                display: block;
                position: absolute;
                margin-left: -307px;
                margin-top: -145px;
                left: 50%;
                top: 50%;
                overflow:hidden; 
            }
		</style>
	</head>
	<body>
	   <span>
	       Site est en maintenance. La reprise du service est prévue pour dans : 
	       <?php echo $remaining_time; ?>
	   </span>
	</body>
</html>