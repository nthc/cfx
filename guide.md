
General Overview of the Framework
=================================
This chapter aims to give a brief description of the core components of the
entire framework. After reading this chapter we're pretty sure you would have a 
basic understanding of the operations of the entire framework.

\section modules Modules
\subsection modules_overview Overview
Applications built on top of the WYF Application Framework are organized into modules. Each module may contain a controller, a model and a collection of views. The controller is used for handling URL routing within the module. The model is used to store the information that the module operates on. The views are used in certain cases for presentation. Modules can be grouped into packages. Packages are normally used to group logically related modules. Physically module packages and their contained modules are just a collection of directories on the disk. The module package directories contain only sub-directories, while the module directories contain codes.
Let us take the WYF brokerage software as an example. The whole application is built in a single package called brokerage. Inside this brokerage package there are four other packages; accounting, reports, setup and transactions. The setup package contains modules which are used to setup the brokerage software. An example of such a setup module is the clients module (which is used to set up clients' information).
\subsection modules_core_modules Core Modules
The framework requires certain modules to operate. Some of these modules are stored in the system package. These are:
- configurations
- home
- login
- logout
- logs
- permissions
- roles
- side_menu
- users

The core modules found in the system package can be categorized as either configuration modules, user interface modules or access control modules. There also exists an API module which could be used for the purpose interacting with the application. This module stands alone (without a package) as the api module.
\subsection modules_configurations Configuration Module
The configurations module is used for storing and retrieving configuration information within the software. Any module could use the configurations module to get access to configuration information that it may need for its operations. Configurations are simple key-value data pairs. A constant key is used to identify the configuration parameter and a value associated with the key represents the value of that particular configuration parameter.
\subsection modules_user_interface User Interface Modules
The home and side_menu modules represent the user interface modules. These modules handle parts of the presentation operations of the framework. The home module is the default module that is executed when the application receives a default request (i.e. a request without any parameters). The side_menu module is used to render the menu on the side of the application.
\subsection Access Control Modules
Access controls modules are used to configure and manage who can access what parts of the system. They also help keep track of all the actions of all the users in the system. Access controls work by having authorised users who are assigned to roles. Each role has a set of permissions which determine what that particular user can and cannot do with each of the modules installed in the system. 
The login and logout packages are used to log users in and out of the system respectively. The logs module is used to keep logs of the all the actions in the system. The roles module is used to configure user roles. The permissions module is used to configure which permissions are assigned to which roles. The users module is used to configure users and their associated roles.

\section go_mvc Models, Views and Controllers
As already stated, the WYF Application Framework is built around a Model-View-Controller pattern. Simply, models are used to store the data, controllers are used to route data and views are used to present the data.

\subsection go_models Models
In the WYF Application Framework, there can be one model per module. Models can either be specified as XML files (through the XMLDefinedSQLDatabaseModel), they can also be specified through PHP classes which extend the Model class or they could take advantage of the ORMSQLDatabaseModel class which automatically generates a model definition once it is given the name of the database table which is going to be used to store the model data. 
Models which are specified as XML files are named model.xml and they are stored in the module directories. The XML file details all the fields of the model as well as the relationship it has with other models.
Models which are specified through PHP classes have class names which are chosen by capitalizing the module package path. For example the model class for the system.users module would be SystemUsersModel and the model class for the ima.liabilities.setup.clients would be ImaLiabilitiesSetupcClientsModel. The classes would be saved in files which have the same name as the classes so the model for the system.users module would be stored in the SystemUsersModel.php file in the module directory.

\subsection go_controllers Controllers
Just as there is one model per module, there can also be one controller per module. Controllers route the data that are stored in models and they work very closely with the views. In several parts of the WYF Software, views are directly embedded within the controller and the separation doesn't really exist.
The framework contains certain core controllers which are used for performing special tasks. These core controllers are the ModelController, ReportController and ErrorController. The ModelController is used for manipulating model content, the ReportController is used for generating reports and the ErrorController is used for displaying error messages.
Just like model classes, controller classes are also named by capitalizing the module package name. A controller class for the system.users module would be named SystemUsersController and the controller class for the ima.liabilities.setup.clients controller would be named ImaLiabilitiesSetupClientsController. In conformity to the standards set by the Model classes the controller class files are saved with the same name as the class. This means that the controller for the system.users module would be stored in the SystemUsersController.php file.

\subsection go_views Views
Unlike models and controllers, there can be as many views per module as needed. The WYF Application framework relies primarily on the Smarty library for laying out the HTML templates. For PDF it uses the FPDF library and for Microsoft Excel files, it uses the Pear Spreadsheet package.

\section go_ui User Interfaces
The WYF Application framework allows the user to interact with the applications based on the framework through two different interfaces. The user can interact through the web interface or the command line interface. The web interface is the preferred interface while the command line is intended to be used by system administrators to perform mission critical tasks which normally involve data transfer and migration. Mission critical tasks are tasks which are very necessary yet resource intensive. These tasks that are likely to time-out if worked through the web interface.

\section go_web_ui Web User Interface
\subsection go_routing Routing
The WYF Application Framework uses a very simple URL routing scheme which is based entirely on its module package architecture. In actual sense the location of a module with respect to the root package is used to determine how the module is routed.
Let us take a simple example where our users configuration module is stored in the system package. To access this module through the web browser, the user would just type http://softwarehost/system/users (assuming the software is installed on a machine called softwarehost). This would load the users module from the system package and execute its controller.
When a request hits the web server the following series of events take place:

 -# The request is received by the index.php script. This script sets up all the include paths (for the required classes), the time-zones and auto-loading functions (which automatically load any other classes which are needed during the runtime of the request). The index.php script also loads the Application class which is the class from the framework which handles the rest of the request.
 -# The Application class checks which module was requested by analysing the URL. If no module was requested, the system.home module is set as the requested module.
 -# The Application class runs the code present in the bootstrap.php script. This script is the first entry point of the application and it is not provided in the framework. This script is normally used to modify the request (mostly for authentication and access control purposes). It is also used to setup specific libraries that the application may need for its purposes. The bootstrapping script is the only part of the application which  is ran every time the framework receives a request.
 -# After the Application class has executed the bootstrapping script, it loads the requested module's controller and executes it.
 -# The Application class outputs the result of the controller execution to the browser after it has applied the web layout to it. This step could be skipped in certain cases where the controller handles its own output and terminates the request.
 
\subsection go_controller Controller Actions
Just as requests are passed through the web browsers URLs, the actions the controllers are expected to execute are also passed through these same URLs. Let's say we want to delete a user from our users model (through the users controller which is stored in the system.users module), we would place the following URL request [http://softwarehost/system/users/delete/123]. The first part of the request [http://softwarehost/system/users] details the framework to load the controller found in system.users. The second part [delete/123] tells the framework to execute the delete action in the controller with a parameter 123. Placing this request should delete a user with a user_id of 123.

\section go_clui Command Line User Interface
The command line user interface is reserved for use by users who belong to the System Administrators role. Command line execution is only possible for users who are logged on to the server machine's operating system (either physically or remotely through SSH). The command interface is accessible through the egati command. Remember that for this command to work the SOFTWARE_HOME parameter in the software configuration settings must be properly set and also the egati script must be executable and accessible through the path.
When executed, the output of the controller accessed through the command line is written to the standard output of the terminal. In most cases, the output would be the same as what is written to the browser. This means that if a particular controller outputs HTML text, the command line execution of that controller would output HTML text. Although this sounds weird it seems to be a good idea because for reporting purposes, the PDF output could be redirected from standard output into a file. Some controllers on the other hand (like the ModelController) can operate in a special API mode which allows them to output data in the JSON format.

\subsection go_clui_args Arguments of the egati Script
The egati script takes five command line arguments. The following table describes each of these arguments.
<table>
<tr>
<td><b>Argument</b></td>
<td><b>Description</b></td>
</tr>
<tr>
<td><tt>--username</tt></td>
<td>This argument is the username to use for the purposes of authentication.</td>
</tr>
<tr>
<td><tt>--password</tt></td>
<td>This argument is the password for the user specified through the username parameter. Note that when a username is specified and an associated password is ignored, the system interactively requires the entry of the password through an invisible input prompt.</td>
</tr>
<tr>
<td><tt>--path</tt></td>
<td>This argument specifies the url path of the controller to load. The path argument can be specified in a manner similar to that of a URL without the protocol and host specification. For example to access the system.users controller which would normally be accessed through the http://software.host/system/users, the user would pass system/users as the value for the –path parameter.</td>
</tr>
<tr>
<td><tt>--request</tt></td>
<td>Some controllers require data which are normally sent through the HTTP parameters (either as GET or POST headers). In cases where these controllers (which require these parameters) are to be used through the command line interface, the --request parameter could be used to pass the POST and GET values to these controllers. The --request parameter is normally encoded using the URL Encoding scheme.</td>
</tr>
<tr>
<td><tt>--apimode</tt></td>
<td>The –-apimode parameter explicitly makes controllers operate in the api mode. This argument takes a value of either yes or no.</td>
</tr>
</table>

\subsection go_clui_using Using the egati Script
Lets take a couple of examples involving the usage of the egati script.
 - To get a list of all the users in the system returned in JSON format, the following command could be executed:
\code
egati --username james --path system/users --api-mode yes
\endcode
This command would output a JSON formatted list of users unto standard output. In the above example, the user for authentication is james, and the path requested is system/users which is the path of the system.users controller.

- To add a bank branch through the command line interface the following command could be used:
\code
egati --username james --path system/banks/add --request bank_name=Agric+Devt.+Bank 
\endcode

- This command would request for the entry of the password and it     would output the status of the addition operation. The box below    gives a description of what the output might look like.
\code
Please enter your password : 
{"success":true,"data":"169"}
\endcode

- To generate the brokerage list of payments report the following command could be used:
\code
egati --username hawa --path brokerage/reports/list_of_payments/generate --request report_format=pdf\&page_orientation=L --password mypassword > reports.pdf
\endcode
It can be seen that from the command shown above that, the output of the command was redirected into a file and the password was passed as a command line parameter.

 * 
 * \section directory_structure Directory Structure
 * \subsection main_directory_structure Main Directory Structure
 * The following table represents the root directory structure of the application.
<table>
<tr>
<td><b>Name</b></td>
<td><b>Type</b></td>
<td><b>Description</b></td>
</tr>
<tr>
<td><tt>app</tt></td>
<td><tt>Directory</tt></td>
<td>A directory in which the main application code and source files reside.
All code which have to do with the WYF Software and its operation reside in 
this directory.</td>
</tr>
<tr>
<td><tt>css</tt></td>
<td><tt>Directory</tt></td>
<td>A directory to hold the style-sheets used in styling HTML content.</td>
</tr>
<tr>
<td><tt>images</tt></td>
<td><tt>Directory</tt></td>
<td>A directory which holds any images that the application requires.</td>
</tr>
<tr>
<td><tt>js</tt></td>
<td><tt>Directory</tt></td>
<td>A directory which holds the JavaScripts</td>
</tr><tr>
<td><tt>lib</tt></td>
<td><tt>Directory</tt></td>
<td>A directory which holds the core code for the application framework</td>
</tr><tr>
<td><tt>tools</tt></td>
<td><tt>Directory</tt></td>
<td>A directory which holds utility scripts written for the purposes of the application.</td>
</tr><tr>
<td><tt>connection.php</tt></td>
<td><tt>Script</tt></td>
<td>A script which performs the database connections</td>
</tr>
<tr>
<td><tt>coreutils.php</tt></td>
<td><tt>Script</tt></td>
<td>
A script which contains utilities required by the entire application. Currently the coreutils.php file contains:
 - Utility functions to aid in autoloading the PHP classes.
 - Utility functions for performing special array operations.
 </td></tr>

<tr>
<td><tt>index.php</tt>
<td><tt>Script</tt></td>
<td>Main entry point for the application</td></tr>
<tr>
<td><tt>setup.php</tt></td>
<td><tt>Script</tt></td>
<td>
A utility script which should be run once and removed for the purposes of 
setting up an installation of the application. This script only writes out the 
configuration files and creates all the required directories needed for the 
application to exist.</td>
</tr>
</table>

\subsection app_directory_structure App Directory Structure
The following table explains the directory structure of the app directory which is found in the root directory. This directory holds all the code which implements the business logic of the application.
<table>
<tr>
<td><b>Name</b></td>
<td><b>Type</b></td>
<td><b>Description</b></td>
</tr>
<tr>
<td><tt>bootstrap.php</tt></td>
<td><tt>Script</tt></td>
<td>A script which is called before the framework performs the actual request. 
This script makes it possible for the application developer to filter the 
requests.</td>
</tr>
<tr>
<td><tt>cache</tt></td>
<td><tt>Directory</tt></td>
<td>A directory which holds all the cached data in the application.</td>
</tr>
<tr>
<td><tt>config.php</tt></td>
<td><tt>Script</tt></td>
<td>A script which contains the database configurations of the application.</td></tr>

<tr>
<td><tt>css</tt></td>
<td><tt>Directory</tt></td>
<td>A directory which holds the style-sheets specific to the application.</td>
</tr>

<tr>
<td><tt>js</tt></td>
<td><tt>Directory</tt></td>
<td>A directory which holds the JavaScripts specific to the application.</td>
</tr>
<tr>
<td><tt>lib</tt></td>
<td><tt>Directory</tt></td>
<td>A directory which holds libraries needed by the application to run. These libraries are not used by the framework.</td>
</tr>
<tr>
<td><tt>modules</tt></td>
<td><tt>Directory</tt></td>
<td>A directory which holds all the modules used by the application. Modules are special collection of code which perform a particular operation in the application. For example there is a specific module for logging users into the application.</td>
</tr>
<tr>
<td><tt>temp</tt></td>
<td><tt>Directory</tt></td>
<td>A directory for holding temporarily generated files.</td>
</tr>
<tr>
<td><tt>templates</tt></td>
<td><tt>Directory</tt></td>
<td>A directory for holding the smarty templates which are used for rendering the application into HTML.</td>
</tr>
<tr><td><tt>uploads</tt></td>
<td><tt>Directory</tt></td>
<td>A directory for holding files which have uploaded unto the server.</td>
</tr>
</table>

\subsection library_dir_struct Framework Library Directory Structure
The framework library directory contains the core code which implements the framework operations.
<table>
<tr>
<td><b>Name</b></td>
<td><b>Type</b></td>
<td><b>Description</b></td>
</tr>
<tr>
<td><tt>Application.php</tt></td>
<td><tt>Script</tt></td>
<td>A script which represents the main application. This script provides basic services which are shared across the application to all the users of the application.</td>
</tr>
<tr>
<td><tt>cache</tt></td>
<td><tt>Directory</tt></td>
<td>A directory which holds all the code which provides the framework's caching code.</td>
</tr>
<tr>
<td><tt>controllers</tt></td>
<td><tt>Directory</tt></td>
<td>A directory which holds all the core code which provides the framework's controller code.</td>
</tr>
<tr>
<td><tt>fapi</tt></td>
<td><tt>Directory</tt></td>
<td>A directory which holds the code for the helper API which generates and validates forms.</td>
</tr>
<tr>
<td><tt>ImageCache.php</tt></td>
<td><tt>Script</tt></td>
<td>A script which contains code used for caching images.</td>
</tr>
<tr>
<td><tt>models</tt></td>
<td><tt>Directory</tt></td>
<td>A directory which contains all the code which provides the framework's model code.</td></tr>
<tr>
<td><tt>rapi</tt></td>
<td><tt>Directory</tt></td>
<td>A directory which contains all the code which generates reports in the application.</td>
</tr>
</table>

\subsection config_file_orgarnisation Configuration File Organisation
The configuration file is stored as app/config.php. This file is expected to define  four variables which are to be used for the purpose of establishing a database connection. These expected variables are:
<table>
<tr>
<td><b>Variable</b></td>
<td><b>Purpose</b></td>
</tr>
<tr>
<td><tt>$db_driver</tt></td>
<td>Identifies which driver should be used for the database connection. Currently the only driver available in the framework is the oracle driver.</td>
</tr>
<tr>
<td><tt>$db_user</tt></td>
<td><tt>This variable holds the username to be used in establishing the database connection.</tt></td>
</tr>
<tr>
<td><tt>$db_host</tt></td>
<td>This variable holds the host name through which the database connection could be establised.</td>
</tr>
<tr>
<td><tt>$db_password</tt></td>
<td>This variable holds the password of the database user.</td>
</tr>
<tr>
<td><tt>$db_name</tt></td>
<td>This variable holds the name of the database which is desired on the server.</td>
</tr>
<tr>
<td><tt>$cache_method</tt></td>
<td>This variable holds the name of the technology used for caching within the entire application.</td>
</tr>
<tr>
<td><tt>$cache_models</tt></td>
<td>This boolean variable is set to determine whether model codes would be cached or not.</td>
</tr>
</table>

The organisation of the config.php file could be as follows.
@code
<?php
$db_driver      = 'oracle';
$db_user        = 'WYF';
$db_host        = 'localhost'
$db_password    = 'root';
$db_name        = 'XE';

$cache_method = 'file';
$cache_models = true;
@endcode

Although the organisation presented above works in production cases, during development the need may exist to switch databases. The following organisation which allows for much easy database switching could be used during development.

@code
<?php
$selected = "exp";

$database["pglive"] = array(
    "driver" => "postgresql",
    "user" => "WYF",
    "host" => "192.168.0.200",
    "password" => "P@w0RdWYF",
    "name" => "WYF",
    "port" => "5432",
    "cache_method" => 'file',
    "cache_models" => true,     
);

$database["exp"] = array(
    "driver" => "postgresql",
    "user" => "postgres",
    "host" => "127.0.0.1",
    "password" => "hello",
    "name" => "WYF",
    "port" => "5432",
    "cache_method" => 'file',
    "cache_models" => 'false',     
);

$database["dev"] = array(
    "driver" => "oracle",
    "user" => "WYF",
    "host" => "localhost",
    "password" => "root",
    "name" => "XE",
    "cache_method" => 'file',
    "cache_models" => false,     
);

$database["test"] = array(
    "driver" => "oracle",
    "user" => "test",
    "host" => "localhost",
    "password" => "test",
    "name" => "XE",
    "cache_method" => 'file',
    "cache_models" => false,     
);

$database["prod"] = array(
    "driver" => "oracle",
    "user" => "WYF",
    "host" => "192.168.0.46",
    "password" => "p@w0RDWYF",
    "name" => "XE"
);

$database["new"] = array(
    "driver" => "oracle",
    "user" => "WYF_new",
    "host" => "localhost",
    "password" => "root",
    "name" => "XE",
);

$db_driver      = $database[$selected]["driver"];
$db_user        = $database[$selected]["user"];
$db_host        = $database[$selected]["host"];
$db_password    = $database[$selected]["password"];
$db_name        = $database[$selected]["name"];
$db_port        = $database[$selected]["port"];

$cache_method   = $database[$selected]["cache_method"];
$cache_models   = $database[$selected]["cache_models"];
@endcode

With a file organisation as shown above databases could be switched by editing 
the value assigned to the $selected variable. The value which is assigned should 
have been used as a key of the $database array.

********************************************************************************

\page working_with_models Working with Models

\section models_datastores Data Stores
Data stores represent the storage back-end for models. Every model stores and 
accesses its data through a data store. A data store could be an RDBMS, an 
XML file, an RSS feed, a flat-file, a web service or some other means of data 
storage or retrieval. Currently the oracle data store is the only existing data 
store in the WYF Application Framework.

All data stores are sub classes of the abstract DataStore class. This class 
requires that all its sub classes implement the get(), save(), update() and 
delete() methods. For data stores which may be read only, empty implementations 
of the save() and update() methods which throw exceptions could be created. For 
write only data stores, the reverse of the read only approach could be taken.

\section models_creating Creating Models for your Application
\subsection models_datatypes Model Data Types
Models come with support for certain data types. These data types could be 
mapped with data types of the target data-store. 

Note that the transformation of the the model data-types to the target data 
types of the underlying data store is handled entirely by the WYF Application 
Framework. The application developer is however expected to handle the 
transformation of the data-types between PHP and the model 
(this is really not too difficult).

\subsection models_class The Model class
The Model class is the base class for all the models created for use in 
applications written with the framework. Historically, the WYF framework's 
Models did not support automatic Object Relational Mapping (ORM) hence all 
fields and their properties had to be explicitly specified in the model class. 
Since proved to be very difficult to manage (especially when the number of 
models and the fields contained in them were very large), a special wrapper 
class XMLDefinedSQLDatabaseModel was used to wrap XML definitions of these 
models. As its name implied, the XMLDefinedSQLDatabaseModel class was used to 
access only models whose data-stores were SQL based. In order to extend these 
XML defined models with extra methods, special hook classes (written in PHP) 
had to be defined to store these methods. The schema for the XML files used to 
specify these models is stored in the models directory of the framework directory 
as model.xsd.

Later in the lifetime of the WYF framework, ORM methods were introduced through 
the ORMSQLDatabaseModel class. This meant that it was going to be easy to introduce 
these features into the WYF Application framework. The ORMSQLDatabaseModel class, 
queries the database for information about the tables it is supposed to wrap. 
With the information derived from the query it can automatically detects the fields 
and the associated data types of the fields as well as certain field constraints 
like uniqueness and required statuses. All that the software developer has to do 
is to define a class which extends the ORMSQLDatabaseModel class and make a few 
definitions and the framework automatically handles the rest. ORMSQLDatabaseModels 
tend to be faster to load because there is no XML parsing and the results of the 
queries made by the ORM component of the framework are cached so the queries do 
not have to be executed anytime the models are loaded.

\subsection models_xml Using XML Defined Models

\warning
XML Defined Models are deprecated and should not be used in new code. New code 
written should always use the ORM model definition approach. This section of the 
documentation is only presented for historical purposes. 
Some examples in this documentation would refer to models created with the 
XMLDefinedSQLDatabaseModel class. These examples are only kept because the XML 
definitions give a true picture of what is contained in the model's 
database tables.

XML Defined models must be placed in the model's module directory and named as 
<tt>model.xml.</tt> The framework automatically detects this XML file and then 
loads the XMLDefinedSQLDatabaseModel as a Model class wrapper for that XML file. 
The model.xml file contains definitions of all the fields in the model and their 
associated validation rules. It also contains information about all the 
relationships the model has with other models. Below is a listing of the 
model.xml file which is used to define the system.users model (the schema 
definitions and some other required definitions have been removed to make it 
easy to read).

@code
<?xml version="1.0" encoding="UTF-8"?>

<model:model name="users" database="users" label="Users">
  
  <model:description>
    All the users in the system. 
  </model:description>
  
  <model:fields>
    <model:field name="user_id" type="integer" key="primary" label="User ID" />
    <model:field name="user_name" type="string" label="Username" >
    <model:validator type="unique" />
    <model:validator type="required" /> 
    </model:field>
    <model:field name="password" type="string" label="Password" />
    <model:field name="role_id" type="reference" 
        reference="system.roles.role_id" referenceValue="role_name" label="Role" >
        <model:validator type="required"></model:validator>
    </model:field>
    <model:field name="first_name" type="string" label="Firstname" >
        <model:validator type="required" />
    </model:field>
    <model:field name="last_name" type="string"  label="Lastname" >
        <model:validator type="required" />
    </model:field>
    <model:field name="other_names" type="string" label="Other Names" />
    <model:field name="user_status" type="enum" label="Status">
        <model:options>
        <model:option value="0">Inactive</model:option>
        <model:option value="1">Active</model:option>
        <model:option value="2">New Account</model:option>
        </model:options>
    </model:field>
    <model:field name="email" type="string" label="E-Mail" >
        <model:validator type="required" />
        <model:validator type="regexp">
               /^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3}$/
           </model:validator>
    </model:field>
    <model:field name="phone" type="string" label="Phone" />
    <model:field name="department_id" type="reference" 
          reference="system.departments.department_id" referenceValue="department_name" 
          label="Department" />
  </model:fields>
</model:model>
@endcode

\subsection models_orm Using Object Relational Mapping For Model Definition
Object Relational Mapped models present a better way of writing model classes. 
The base class for ORM models is the ORMSQLDatabaseModel class which is a subclass 
of the Model class. This means that all the convenience methods and hooks 
available in the Model class are easily accessible to the ORMSQLDatabaseModel 
class.

To create a Model class throught the ORMSQLDatabaseModel class, the database 
table which would store the model's data must exist. For example to create a 
model class for the system.users module, an SQL query which defines the the users 
table must be executed to create the table.

\code
CREATE TABLE  "USERS" 
   (    "USER_ID" NUMBER, 
        "USER_NAME" VARCHAR2(64) NOT NULL ENABLE, 
        "PASSWORD" VARCHAR2(64) NOT NULL ENABLE, 
        "ROLE_ID" NUMBER, 
        "FIRST_NAME" VARCHAR2(64) NOT NULL ENABLE, 
        "LAST_NAME" VARCHAR2(64) NOT NULL ENABLE, 
        "OTHER_NAMES" VARCHAR2(64), 
        "USER_STATUS" NUMBER(1,0), 
        "EMAIL" VARCHAR2(64) NOT NULL ENABLE, 
        "PHONE" VARCHAR2(64), 
        "DEPARTMENT_ID" NUMBER, 
         CONSTRAINT "USER_ID_PK" PRIMARY KEY ("USER_ID") ENABLE, 
         CONSTRAINT "USER_NAME_UK" UNIQUE ("USER_NAME") ENABLE, 
         CONSTRAINT "USERS_ROLE_ID_FK" FOREIGN KEY ("ROLE_ID")
          REFERENCES  "ROLES" ("ROLE_ID") ON DELETE SET NULL ENABLE, 
         CONSTRAINT "USERS_DEPT_ID_FK" FOREIGN KEY ("DEPARTMENT_ID")
         REFERENCES  "DEPARTMENTS" ("DEPARTMENT_ID") ON DELETE SET NULL ENABLE
   )
\endcode

Once the table is available, the ORM model class to wrap this table could be defined as follows.

\code
class SystemUsers extends ORMSQLDatabaseModel
{
    public $database = "users";
}
\endcode

This class could be extended to show all the relationships and validation rules 
that are necessary for the model to correctly function. Instances of the 
system.users model as defined above could still be created and used to insert 
and access data without any problems.

\section model_instantiating Instantiating Models
Model classes are instantiated through the static load method of the Model class 
(Model::load). The new operator cannot be used because the models may not always 
be defined as PHP classes files (in some cases they were previously defined in 
XML format). Leaving the method of definition open makes it possible to easily 
introduce wrapper classes which allow models to be defined in other formats 
into the application framework.

@code
$usersModel = Model::load("system.users");
@endcode

\section model_crud CRUD Operations with Models
For the rest of this section a description is going to be given on how to 
perform basic CRUD (Create, Read, Update and Delete) operations on models 
created in the framework. We are going to use an instance of the 
brokerage.setup.securities module which could be defined by the following XML 
listing (to be used through the XMLDefinedSQLDatabaseModel class) or it could 
also be defined as an SQL table (to be used through the ORMSQLDatabaseModel 
class).

@code
<?xml version="1.0" encoding="UTF-8"?>
<model:model label="Securities" name="securities" database="securities">
  <model:fields>
    <model:field name="security_id" type="integer" key="primary" label="Security ID" />
    <model:field name="security_type" type="enum" label="Security Type" >
        <model:options>
            <model:option value="0">Ordinary</model:option>
            <model:option value="1">Preference</model:option>
        </model:options>
        <model:validator type="required"/>
    </model:field>
    <model:field name="security_name" type="string" label="Security Name">
        <model:validator type="required" />
        <model:validator type="unique" />
    </model:field>
    <model:field name="code" type="string" label="Code" key="secondary">
        <model:validator type="required" />
        <model:validator type="unique" />
    </model:field>
    <model:field name="unit_price" type="double" label="Unit Price">
        <model:validator type="required" />
    </model:field>
    <model:field name="over_counter" type="boolean" label="Over the counter"/>
    <model:field name="last_payment_date" type="date" label="Last Payment Date"/>
    <model:field name="interest_rate" type="double" label="Interest Rate"/>
    <model:field name="tax_rate" type="double" label="Tax Rate"/>
  </model:fields>
</model:model>
@endcode

\subsection model_crud_insert Inserting Data
To insert data into the model you would use the setData() and save() methods.

\code
$securitiesModel = Model::load("brokerage.setup.securities");
$securitiesModel->setData(
    array(
        "security_type" => 0,
        "security_name" => "Ghana Commercial Bank Limited",
        "code" => "GCB",
        "unit_price" => 0.91,
        "over_counter" => false
    )
);

$securitiesModel->save();
\endcode

This listing loads an instance of the brokerage.setup.roles model and sets 
the data that is to be stored. (Note that not all the fields were used). 
After it sets the data, it executes the save method to push the data into the 
database.

\subsection model_crud_retrieving Retrieving Data
In retrieving data from the model, the get() method is used. This method takes 
four arguments. The first argument is a structured array which contains most of 
the necessary parameters which are needed for the retrieval of the right type of 
data. The following table shows all the options that can be passed through the 
parameters structured array.
<table>
<tr>
<td><b>Parameter</b></td>
<td><b>Description</b></td>
</tr>
<tr>
<td><tt>fields</tt></td>
<td>An array which contains a list of all the fields whose data are required in the query.</td>
</tr>
<tr>
<td><tt>conditions</tt></td>
<td>A string in the format of an SQL conditional statement which is used to  
limit what type of data is outputted.</td>
</tr>
<tr>
<td><tt>sort_field</tt></td>
<td>If this parameter is a string, the results are sorted by the field represented in the string. If this parameter is an array, the results are sorted by the fields specified in the array.</td>
</tr>
<tr>
<td><tt>sort_type</tt></td>
<td>This is only applicable when the value of the sort field is a string. 
It describes how the sorting should be done. Format is ASC for ascending order 
and DESC for descending order.</td>
</tr>
<tr>
<td><tt>enumerate</tt></td>
<td>If this parameter is set to true, the result of the query is simply the 
count of all the results the query would have given.</td>
</tr>
<tr>
<td><tt>distinct</tt></td>
<td>If this parameter is set to true, the value of the results are distinct 
(i.e. nothing is repeated).</td>
</tr>
</table>

The second argument passed determines the format in which the fields of the data 
is returned. If the value of this parameter is Model::MODE_ASSOC then the fields 
are returned as an associative array with the names of the fields being the keys. 
If the value of this parameter is Model::MODE_ARRAY then the fields are returned 
as a regular numeric array. 

The third argument is a boolean value which determines whether the explicitly 
related data should be retrieved. (See Explicit Relations)
The fourth argument is also a boolean value which determines whether the data 
should be formatted before being output. When this parameter is set to true: 
 -# Dates are formatted into the DD/MM/YYYY H:M:S format
 -# Numbers are comma separated into thousands and they are reported to four decimal places if they are supposed to contain fractions.
 -# Fields from referenced tables are reported instead of the foreign key value.
 
The following scenarios describe some of the use cases of the Model class's get() method.
To retrieve everything from the model as an array the following code would be used:

@code
$securitiesModel = Model::load("brokerage.setup.securities");
$securities = $securitiesModel->get();

To retrieve a particular item with a particular primary key value the following code would used:
$securitiesModel = Model::load("brokerage.setup.securities");

// Retrieve security data with primary key 5
$securities = $securitiesModel[5];
@endcode

Note that this operation treats the model as though it were an array indexed 
by the primary key of the model.

To retrieve a only a set containing specific fields:

@code
$securitiesModel = Model::load("brokerage.setup.securities");

// Retrieve only the security names and unit prices
$securities = $securitiesModel->get(
    array(
        "fields"=>array("security_name", "unit_price")
        )
    );
@endcode

To retrieve with conditions

@code
$securitiesModel = Model::load("brokerage.setup.securities");

// Retrieve only the security names and unit prices
$securities = $securitiesModel->get(
    array(
        "fields"=>array("security_name", "unit_price"),
        "conditions"=>"unit_price > 0.5 AND security_type = 0"
        )
    );
@endcode
    
Note that the conditions need to be expressed as though they were being used in 
a regular SQL statement. This means that you are free to write the conditions 
anyway you please.

\subsection model_crud_updating Updating Data
To update data, the setData() and update() methods of the Model class are used. 
The setData() method is used to set the data and the update() method is used to 
perform the actual update. The update() method takes two different parameters. 
The first one is the primary key field of the model (or any other field in the 
module which is guaranteed to have a unique value) and the second one is the 
value that is currently associated with the primary key field (or any other field 
that was passed through the first parameter). The following listing shows how 
data could be updated. It updates the security stored in the securities table 
with a primary key value of 5.

@code
$securitiesModel = Model::load("brokerage.setup.securities");

$securitiesModel->setData(
    array(
        "security_type" => 0,
        "security_name" => "Ghana Commercial Bank Limited",
        "code" => "GCB",
        "unit_price" => 0.91,
        "over_counter" => false
    )
);

$securitiesModel->update("security_id", 5);
@endcode

\subsection model_crud_deleting Deleting Data
To delete data from a model, the delete() method of the Model class is used. 
This method takes two parameters. The first parameter is the primary key field 
of the model (or any other field which is guaranteed to always hold a unique 
value) and the second one is the value of the primary key field (or any other 
field that was passed as the first parameter). The following listing shows how 
data could be deleted from a model.

@code
$securitiesModel = Model::load("brokerage.setup.securities");
$securitiesModel->delete("security_id", 5);
@endcode

\subsection model_crud_arrays Accessing Model Data as Arrays
An instance of the model class could be accessed as an array. The keys of this 
arrays are the primary key values of the data stored in the given model. The 
following example illustrates this capability.

@code
$securitiesModel = Model::load("brokerage.setup.securities");
// Retrieve security data with primary key 5
$securities = $securitiesModel[5];
@endcode

When retrieving data in this fashion: 
 -# The mode of the returned fields could be controlled through the queryMode parameter of the Model class either by setting it as Model::MODE_ASSOC and Model::MODE_ARRAY.
 -# Requests for explicitly related data could be included by setting the value of the queryExlicitRelations parameter of the Model class as true.
 -# Automatic formatting of the returned data could be achieved by setting the value of the queryResolve parameter of the Model class to true.

\section model_hooks Hooks for the Model Class
The Model class exposes certain hooks which makes it possible for the application 
developers to control which data goes through every stage of the class's CRUD 
operations. These hooks are special methods in the Model class which could overrided. 
The hook methods are called during the crud operations so that the applications 
could perform their own operations on the data. The hook methods defined in the 
Model class are;

<table>
<tr>
<td><b>Hook</b></td>
<td><b>Description</b></td>
</tr>
<tr>
<td><tt>preAddHook()</tt></td>
<td>Called before any data is added to the database. The programmer could use 
the $this->data property of the Model class to get access to the data and make 
manipulations before the data is saved. Whatever state the programmer puts the 
$this->data property into is what would be saved into the system.</td>
</tr>
<tr> 
<td><tt>postAddHook($primaryKeyValue)</tt></td>
<td>Called after data has been added to the database. The programmer could use 
the $this->data property of the Model class to get access to the data that was 
saved. This method takes one parameter. This parameter would contain the value 
of the primary key generated during the save operation.</td>
</tr>
<tr>
<td><tt>preUpdateHook()</tt></td>
<td>Called before any data is updated. Just as with the preAddHook(), this hook 
could also manipulate the data to be stored through the $this->data property of 
the Model class.</td>
<tr>
<td><tt>postUpdateHook()</tt></td>
<td>Called after data has been updated in the database table. The hook could get 
access to the data that was updated through the $this->data property of the Model 
class.</td>
</tr>
<tr>
<td><tt>preDeleteHook()</tt></td>
<td>This hook is called before data is deleted from the model.</td>
</tr>
<tr>
<td><tt>postDeleteHook()</tt></td>
<td>This hook is called after data has been deleted from the model.</td>
</tr>
<tr>
<td><tt>preValidateHook()</tt></td>
<td>This hook is called just before the system validation rules are run. It is 
advisable to call these rules when some dynamic data needs to be inserted before 
validation.</td>
</tr>
<tr>
<td><tt>postDeleteHook()</tt></td>
<td>This hook is called just after the system validation has been ran.</td>
</tr>
</table>

\subsection model_hooks_xml Hooks for XML Defined Models

\warning
XML Defined Models are deprecated and should not be used in new code. New code 
written should always use the ORM model definition approach. This section of the 
documentation is only presented for historical purposes. 
Some examples in this documentation would refer to models created with the 
XMLDefinedSQLDatabaseModel class. These examples are only kept because the XML 
definitions give a true picture of what is contained in the model's database tables.

Since XML defined models cannot contain executable PHP code, special hook classes 
which extend the XMLDefinedSQLDatabaseModelHooks class are used to implement the 
hooks for the Model class. These classes are stored in the same folder as the 
model.xml file and they are named with the name of the model postfixed with the 
word Hooks. For example if there is an XML defined model with the name clients 
then the hooks class would be clientsHooks and it would be stored in the 
clientsHooks.php file. 

Apart from providing the regular Model class hooks, the hooks class for XML defined 
models also give extra hooks which makes it possible to override Model class 
methods such as save(), update() and delete(). The table below describes all the 
possible hooks which could be written for XML defined models.

<table>
<tr>
<td><b>Hook</b></td>
<td><b>Description</b></td>
</tr>
<tr>
<td><tt>preAdd()</tt></td>
<td>Implements the preAddHook() method of the Model class.</td>
</tr>
<tr>
<td><tt>postAdd($primaryKeyValue)</tt></td>
<td>Implements the postAddHook() method of the Model class.</td>
</tr>
<tr>
<td><tt>preUpdate()</tt></td>
<td>Implements the preUpdateHook() method of the Model class.</td>
</tr>
<tr>
<td><tt>postUpdate()</tt></td>
<td>Implements the postUpdateHook() method of the Model class.</td>
</tr>
<tr>
<td><tt>preDelete()</tt></td>
<td>Implements the preDeleteHook() method of the Model class.</td>
</tr>
<tr>
<td><tt>postDelete()</tt></td>
<td>Implements the postDeleteHook() method of the Model class.</td>
</tr>
<tr>
<td><tt>validate()</tt></td>
<td>Allows overriding of the validate() method of the Model class.</td>
</tr>
<tr>
<td><tt>save()</tt></td>
<td><tt>Allows overriding of the save() method of the Model class.</tt></td>
</tr>
<tr>
<td><tt>update()</tt></td>
<td>Allows overriding of the update() method of the Model class.</td>
</tr>
<tr>
<td><tt>delete()</tt></td>
<td>Allows overriding of the delete() method of the Model class.</td>
</tr>
</table>

\section model_related Working with Related Models
\subsection model_related_simple Simple Relationships
The WYF framework allows for the application developers to work with a their 
database table relationships in their Models. Related models (based on their table structures) 
can be referenced in a model definition. The XML model definition uses a special 
data type reference to represent this.

@code
<model:field name="nationality_id" label="Nationality" type="reference" reference="system.countries.country_id" referenceValue="nationality" />
@endcode

Using the definition above, whenever a query is executed the nationality_id 
would be retrieved. However, whenever a query is executed with the resolve mode 
set to true, the referenceValue which is nationality in this case is retrieved.

\subsection model_related_explicit Explicit Relationships
Explicit relationships take relationships a step further.  They represent a means 
of informing a model about which other models reference it. You can consider it 
as a one to many relationship or a reverse of the simple relationships explained 
above. If a model is defined as being explicitly related to a particular model 
(which is being defined), then the explicitly related model is expected to contain 
a foreign key to the model being defined. Hence if model A is defined as being 
explicitly related to model B, then model A is expected to contain a foreign key 
to model B. You could consider this relationship as a one to many relationship.
 
In the Model class, explicit relationships are stored through the 
Model::$explicitRelations variable as an array. This definition could either be 
done by overriding the Model::explicitRelations variable with the array or by 
doing an assignment to this variable through the constructor of the class. The 
following definition shows how explicit relationships are defined by overriding 
the Model::$explicitRelations variable in an ORMSQLDatabaseModel.

@code
class BrokerageSetupClientsModel extends ORMSQLDatabaseModel
{
    public $explicitRelations = array(
        "brokerage.setup.client_joint_accounts",
        "brokerage.setup.next_of_kins",
    );
}
@endcode

In XML defined models, explicit relationships were defined with the 
model:explicitRelations tag. The following definition (which was taken from the 
brokerage.setup.clients module of the WYF brokerage software) shows how explicit 
relationships were defined in the XML defined models.
@code 
    <model:explicitRelations>
        <model:model>brokerage.setup.client_joint_accounts</model:model>
        <model:model>brokerage.setup.next_of_kins</model:model>
    </model:explicitRelations>
@endcode

In this definition, the brokerage.setup.client_joint_accounts and the 
brokerage.setup.next_of_kins models are expected to contain a reference 
definition to the brokerage.setup.clients.client_id field. When a query is made 
to the brokerage.setup.clients model and explicit relationships are requested, 
all the brokerage.setup.next_of_kins data and the 
brokerage.setup.client_joint_accounts data would be pulled for any 
brokerage.setup.clients data to which is related.
 
\section model_transactions Working In Transactions
In most business and mission critical applications, the ability to work on a set 
of database operations at once is very critical. For example a transfer of funds 
may involve crediting one account and debiting another. These two operations must 
take place at once and none must fail. If one operation fails then the whole 
transfer should fail. Transactions provide a means within the framework to work 
on a batch of operations as a unit. It must be said that support for transactions 
depends on the data store backing the model. To begin a transaction execute:

@code
$model->datastore->beginTransaction();
@endcode

Once this is executed all models are put into transaction mode. Any operations 
on  any models would not be committed into the database until

@code
$model->datastore->endTransaction();
@endcode
is executed.

**********************************************************************************

\page controllers Working with Controllers

Controllers in the Application framework are classes which contain a collection 
of public methods. Each of these public methods return strings or specially 
formatted arrays which are in turn used to generate the final output targeted at 
the end user (and mostly presented through the browser). All controllers in the 
application frame work are based on the Controller class.

\subsection controllers_core The Core Controller Classes
The Application Framework has five main core controller classes and a couple of 
helper class libraries. These classes are:

 -# Controller
 -# ModelController
 -# PackageController
 -# ReportController
 -# XmlDefinedReportController
 
The Controller class forms the base class for all controllers, the ModelController 
class is a special controller class which provides an interface for viewing and 
manipulating model data, the PackageController class automatically generates menus 
by displaying the modules contained in a package, the ReportController class 
provides an extension to the Controller class for the purpose of generating and 
rendering reports and the XmlDefinedReportController class provides a wrapper 
class for XML defined reports.

\section controllers_intantiating Instantiating Controllers
\subsection controllers_url_routing URL Routing Scheme
From Section 2.6.1 of the second chapter in this manual, we find out that 
controllers are always instantiated based on the request the server receives. 
This is necessary because the controller is the only component which can 
interpret the requests and act on them. Requests are normally sent through 
the URLs which hit the server. Every URL directed to the server must be 
formatted such that it specifies a controller class to be loaded, an optional 
controller action method to be invoked and a set of parameters to be passed to 
the controller action method (in case one was provided). In cases where a 
controller method is not specified the getContents() method of the controller 
class is invoked by default. This getContents() method is available in the base 
Controller class. Subclasses which extend the Controller class should override 
this method with the code for their default action method.

\subsection controllers_url_action Controller Action Method Parameters
As stated in the previous section, the URL used to invoke controllers could 
contain parameters which are to be passed to the the controller action method. 
These parameters are all put together into an array and this array is subsequently 
passed to the controller action method. This goes ahead to make us aware of the 
fact that controller action methods can take only one parameter which is the 
array containing the parameters which were passed to the controller 
(through the URL).

\subsection controllers_auto Automatically Loaded Core Controllers
In certain cases where a request is directed to a module which has no controller, 
the framework uses certain heuristics to load one of the core controllers for that 
module. The core controllers used in such cases are the ModelController, the 
PackageController, the XmlDefinedReportController and the ErrorController.

The ModelController is automatically loaded when the requested module contains a
model but not a controller. The ModelController presents the user with an 
inbuilt controller which allows the user to manipulate the contents of the model. 
Through this controller the user can perform operations like adding, editing, 
deleteing, exporting, and importing on the contents of the model.
 
The PackageController is automatically loaded when the requested module contains
a collection of sub packages or modules. This controller allows the user to 
navigate through the modules which are available through that package.

The XmlDefinedReportController is automatically loaded when a module contains 
only a report.xml file. These report.xml files contain descriptions of reports 
which are to be rendered by the XmlDefinedReportController.

The ErrorController is loaded whenever there is an error in loading the 
controller. Errors in loading controllers may occur when a controller doesn't 
exist or a method being requested in the controller doesn't exist.

\section controllers_output Generating Controller Output
Output for any controller request is specified by the object returned by the 
controller action method. Controller action methods can either return strings or 
specially formatted associative arrays (which are processed through templates).

\subsection controllers_output_strings Outputing through Strings
In cases where the controller returns a string, that string becomes the verbatim 
output of the controller. As an example consider the following listing which 
demonstrates a hypothetical controller for a module named examples.hello_world 
which has a greet() controller action method.
@code
class ExamplesHelloWorldController extends Controller 
{
    public function greet($params) {
        return "Hello World";
    }   
}
@endcode

This action method outputs the very famous “Hello World” greeting. One URL which 
could have possibly been used to execute this controller would be 
http://softwarehost/examples/hello_world/greet.

\subsection controllers_output_arrays Outputing through Arrays
In the other case where a controller returns a special array, the values in the
array are used in conjunction with the Smarty Template library to generate the
output of the controller. This special array must have two keys. The first key
points to the template and the second key points to another associative array 
(which acts as a key-value hash table for all the variables to be substituted in
the template). The format of this array is internal to the framework and it is 
likely to change as such the Controller class exposes the template() method which 
is responsible for generating this array based on the controller's internal 
specification. The template() method takes two parameters. The first parameter 
is the name of the template file and the second parameter is an array organized 
in a key-value format which represents the data to be substituted in the template. 
The template file being referred to must exist in the same directory as the module 
directory of the controller.

Outputting through arrays (and for that matter templates) is a much more cleaner
option than using strings because it properly separates the controller from the 
view and as such it fully implements the MVC principle. In using ths template() 
method to output controller data, the template file acts as the view and the 
Smarty Template Library acts as the class responsible for the view.

The hello world example presented above could be extended by making it output 
through arrays (or templates) instead of strings. To make this possible a template 
file which defines how the output would look must be added. Please note that to 
further demonstrate the substitution ability of the Smarty Template Engine, this
example would feature some  controller parameters. The first listing below shows 
the source for the modified controller class and the second listing shows the 
contents template file (greet.tpl).

@code
class ExamplesHelloWorldController extends Controller 
{
    public function greet($params) 
    {
        $data = array (
           "first_name" => $params[0],
           "last_name"  => $params[1]
        );
        return $this->template("greet", $data);
    }
}
@endcode

@code
Hello {$first_name} {$last_name}!
@endcode

The version of the greet action method shown above, outputs a modified form of 
the Hello greeting. This time the greeting is followed by the full name of the 
person to be greeted. The software can get to know of the person to be greeted 
through the URL. A URL which could have possibly been used to execute this 
controller would be http://softwarehost/examples/hello_world/greet/Hawa/Mohammed, 
and this URL would output Hello Hawa Mohammed.

\section controller_permissions Exposing Role Permissions for Controllers
The WYF Application Framework possesses a role based authentication component 
in its system.roles module. This module generates menus and enforces restrictions 
on the URL paths which can be accessed by a particular user who belongs to a 
particular role. Apart from enforcing these default restrictions, a special User 
class exists which allows the controller to check for certain controller specific 
permissions.
 
All controllers which want to expose their own permissions must override the 
getPermissions() method of the Controller class. The new method must return an 
array which describes all the permissions available exposed by the controller. 
The following listing shows an example of such a method which exposes two 
permissions.

@code
public function getPermissions()
{
    return array 
    ( 
        array(
            "label" => "First Permission",
            "name"  => "can_run_first_permission"
        ),
        array(
            "label" => "Second Permission",
            "name"  => "can_run_second_permission"
        )
    );
}
@endcode

From the listing above, the label key of the permission array stores a string 
which would be displayed during the configuration of roles and the name key must
store a unique value which would be used by the database to store the permission
values.

\section controller_extending Extending the ModelController
The ModelController class as we already know is used for manipulating data in 
the models. This controller provides a user interface which lists all the contents 
of the model and allows the user to perform operations such as searching, adding, 
editing, importing, exporting and deleting. Although the ModelController is 
instantiated automatically by the framework, it can also be used as the super 
class of a Controller.

Although the ModelController inherently has its own default forms, operations and 
permissions, these defaults could be overridden by the class which is extending 
the ModelController. The new class can add new operations, suppress existing 
operations and modify the default form with a custom form.

\section controller_report_controller Extending the ReportController and the XmlDefinedReportController
As their names imply, the ReportController and the XmlDefinedReportController are 
used for writing controllers which generate reports. The ReportController class 
is the base class for all reports. The XmlDefinedReportController class (which 
is a sub class of the ReportController class) provides an interface for 
generating reports which are defined through XML files.

The ReportController class is abstract and it requires that its extenders 
implement its getForm() and generate() methods. The getForm() method is called 
by the framework when it needs a form which contains the filters of the report. 
The generate() method is the controller action method which does the actual 
rendering of the method.

The XmlDefinedReportController on the other hand is much more of a complete 
report generating toolkit. It has an internal engine which reads the required 
report fields (and some other associated parameters) from an XML file and 
generates a query to be executed. The XmlDefinedReportController class provides 
extra reporting features like sorting, grouping and also filtration of reports.


*/