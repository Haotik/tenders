<?php
$cfg['PmaAbsoluteUri_DisableWarning'] = TRUE;
$cfg['blowfish_secret'] = 'wertyukjndwy3cn3';
$cfg['Servers'][1]['auth_type'] = 'cookie';
$cfg['Servers'][1]['host'] = 'localhost';
$cfg['Servers'][1]['connect_type'] = 'tcp';
$cfg['Servers'][1]['compress'] = false;
/* Select mysql if your server does not have mysqli */
$cfg['Servers'][1]['extension'] = 'mysqli';
?>
