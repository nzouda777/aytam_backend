<?php
$cfg['Servers'][1]['auth_type'] = 'cookie';
$cfg['Servers'][1]['host'] = 'db';
$cfg['Servers'][1]['compress'] = false;
$cfg['Servers'][1]['AllowNoPassword'] = false;
$cfg['UploadDir'] = '';
$cfg['SaveDir'] = '';
$cfg['PmaNoRelation_DisableWarning'] = true;
$cfg['blowfish_secret'] = 'your-secret-key-here';
$cfg['ForceSSL'] = false;
$cfg['PmaAbsoluteUri'] = '/';
$cfg['LoginCookieValidity'] = 14400;
ini_set('session.cookie_httponly', '0');
