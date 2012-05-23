The wyf framework
=================
The wyf framework is a framework that has been put together over the past
couple of years. It's really old and "cranky" .... but you'll definately find a 
time where it would be soooo useful to you. Its not a one size fits all kind of
framework. No! its main aim is to help build those database driven apps where you
have views,lots of complex forms and reports. In actual sence its a partially
built application and all you do is to code your modules in. 
  
Basic Architecture
------------------
The WYF Application Framework is somehow Object Oriented and it exhibits some 
model-view-controller (MVC) characteristics. 
The framework provides API's which aid in:
 -  Database interfacing and abstraction. (Although it only supports pgsql for 
    now :(. So why call it abstraction?)
 -  Object Relational Mapping of Database Tables
 -  Form generation and validation.
 -  Views or lists generation and manipulation.
 -  Report Generation
 -  Testing through the PHPUnit test automation framework.
 -  User Access control and authentication.
 -  Logging and audit trails

Some limitations
----------------
The following things have been the pain of many developers who have worked with 
this framework:
 -  It only works with postgresql (for now)
 -  It hurts to theme your application (you can however mess with the css that 
    ships with the framework)
 -  You may have to write classes with long names like SystemSetupUsersRolesController ... smh
 -  Hasn't been fully tested on Microsoft Windows.

Third Party Software
--------------------
The WYF framework utilizes other third party libraries to help it work. These software include:
 -  The smarty Template Engine which is used for rendering the templates.
 -  The FPDF PDF generation library which is used for generating the PDF 
    documents. (soon to be replaced by TCPDF)
 -  The Pear Spreadsheet Package which is used for generating the Microsoft 
    Excel documents.
 -  The PHPUnit framework which is used for unit testing the applications built 
    with the framework. (this is in fact a work in progress)

Getting Started
===============
 To setup the wyf framework you have to do the following:
 
 1. Checkout the code from here into a directory called `lib`. This directory 
    should be in the document root of your application.
 2. From your document root execute `php lib/setup/setup.php`
 3. Follow the steps and you should have a working dummy application before you 
    know it. Remember you must have an empty postgres database read

So to sum these up in commands (assuming your document root is /var/www ):
    
    $ cd /var/www
    $ mkdir wyftest
    $ cd wyftest
    $ git clone git://github.com/ekowabaka/wyf.git lib
    $ php lib/setup/setup.php

After that you can point your browser to `http://localhost/wyftest` and if your
system is behaving "normally" you should see a pretty login page.
