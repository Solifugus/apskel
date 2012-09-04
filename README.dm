
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

  - to add support for Virtuoso Universal Database 
    This could provide SPARQL and XML data access capabilities.
  - to add support for a SINful data store
    Semantic Information Notation (SIN) is a notation for true and non-rigid
    semantic storage and retrieval of data (as information).  The problem is, I
    have yet to complete implementation of a fully functional SIN Server.


INSTALLATION INSTRUCTIONS

  (0) Ensure all prerequisites are met:

    a. You have command line access to a Linux or UNIX server
    b. Apache 2.x is installed with mod_write enabled
    c. You have php5 and php5-cli installed

  (1) Unpack the Apskel files in the directory, underwhich you plan to have it
on a webserver.  Most advisably (and the presumption to be made hereafter), this
would be /var.

  cd /var
  tar -xzpf apskel-1.0.tgz

  (2) Execute the setup utility:

  php /var/apskel/tools/configure

  (3) Follow the instructions, thereafter.


