
Apskel 1.0 (alpha)
Copyright (C) 2012 By Matthew C. Tedder
Email: matthewct@gmail.com
Apskel is provided under the terms and conditions specified in the
accompanying LICENSE file (The GNU LGPL from the Free Software Foundation)


ABOUT APSKEL

The name "Apskel" is short for "Application Skeleton".  Apskel is a framework
for Rapid Application Development (RAD), using PHP in a Linux or UNIX
environment.  The primary aim of Apskel is to minimize work and
complexity in the development and maintenance of web applications.  

Currently, Apskel is developed for the LAMP (Linux Apache2 MySQL PHP)
stack.  JQuery is also adopted as a core technology.  The follow aspirations
exist:

  * Build Panels Module
  * Build Reputational Identity Module
    - Local Authentication
    - Phone Texting Authentication
    - Email Authentication
    - Amazon Authentication
    - Google Authentication 
  * Build User Directory Module
  * Build Agent Module
  * Build Workflow Module


INSTALLATION INSTRUCTIONS

  (0) Ensure all prerequisites are met:

    a. You have command line access to a Linux or UNIX server
    b. Apache 2.4 is installed with mod_rewrite enabled
    c. You have php7 and php7-cli installed

  (1) Unpack the Apskel files in the directory, underwhich you plan to have it
on a webserver.  Most advisably (and the presumption to be made hereafter), this
would be /var.

  cd /var
  tar -xzpf apskel-1.0.tgz

  (2) Execute the setup utility:

  php /var/apskel/tools/configure

  (3) Follow the instructions, thereafter.


