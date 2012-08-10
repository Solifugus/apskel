<?php

// General Settings
$settings = array();
$settings['landing_page']       = 'work/todo';       // default landing page
$settings['post_login_page']    = 'user/profile';    // default page to go to after a successful login
$settings['salt']               = 'not pepper';      // salt for password encryption/decryption
$settings['log_file']           = '../../environments/1.0/development/application.log'; // TODO: default this to /var/log/{application}-{environment}.log
$settings['email_from']         = 'system@localhost';
$settings['admin_emails']       = 'root@localhost';
$settings['debug']              = 'log';             // 'show' (on screen), 'hide' (as HTML comments), 'log', or 'none'

// Database Settings
$databases = array();
$databases[0] = array();
$databases[0]['address']  = 'localhost';   // address to database server
$databases[0]['port']     = null;          // port number or null for default
$databases[0]['name']     = 'workmosaic';  // database name
$databases[0]['prefix']   = '';            // optional table prefix (for shared databases)
$databases[0]['user']     = 'root';        // database user name
$databases[0]['password'] = 'secret';   // database user password
$databases[0]['usage']    = 'read_write';  // 'read_only', 'read_write', or 'failover' 

// URL Shorteners 
$rewrites = array();
$rewrites['tasks']   = 'workflow/tasks';
$rewrites['login']   = 'user/login';
$rewrites['profile'] = 'user/profile';
$rewrites['account'] = 'user/profile';
$rewrites['logoff']  = 'user/logoff';

