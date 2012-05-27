README
======

This directory should be used to place project specfic documentation including
but not limited to project notes, generated API/phpdoc documentation, or
manual files generated or hand written.  Ideally, this directory would remain
in your development environment only and should not be deployed with your
application to it's final production location.


Setting Up Your VHOST
=====================

The following is a sample VHOST you might want to consider for your project.

<VirtualHost *:80>
   DocumentRoot "C:/Server/htdocs/project/haphazard/public"
   ServerName .local

   # This should be omitted in the production environment
   SetEnv APPLICATION_ENV development

   <Directory "C:/Server/htdocs/project/haphazard/public">
       Options Indexes MultiViews FollowSymLinks
       AllowOverride All
       Order allow,deny
       Allow from all
   </Directory>

</VirtualHost>

Rest Controller Testing
A simple quick start application created to explorer and learn how to use controllers that extend Zend_Rest_Controller.

When running these examples comment out the following line in your application.ini.
resources.frontController.plugins.acl = Plugin_Access_Acl
 

Testing the indexAction(): Retrieves all users using Zend_Paginator. All users are returned for this request. The param -v is verbose mode.
curl http://haphazard.dev/admin/users
curl http://haphazard.dev/admin/users -v


Testing the getAction(): Retrieves a single user referenced by an identification number.
curl http://haphazard.dev/admin/users/get/id/1
curl http://haphazard.dev/admin/users/get/id/20 -v
curl http://haphazard.dev/admin/users/get/id/35634 -v


Testing postAction(): Used to insert a new records.
curl -d "name=Tom Shaw" http://haphazard.dev/admin/users/post
curl -d "name=Tom Shaw&username=tomshaw&email=tom@tomshaw.info" http://haphazard.dev/admin/users/post -v


Testing the putAction(): Used to update records.
curl -d "email=tom@tomshaw.info&username=Tom Shaw&newsletter=1" -X PUT http://haphazard.dev/admin/users/put/id/2
curl -d "email=tom@tomshaw.info&username=Tom Shaw&newsletter=1" -X PUT http://haphazard.dev/admin/users/put/id/2 -v


Testing the deleteAction(): Deletes a user from thr database.
curl -X DELETE http://haphazard.dev/admin/users/delete/id/2028
curl -X DELETE http://haphazard.dev/admin/users/delete/id/2028 -v


Testing the secrectAction() User to verify an api key sent in the request header.
curl -H "apikey: d4SdfXsAd6H9Z" http://haphazard.dev/admin/users/secret -v

Response codes.
200 OK - successfully returning the requested users
201 Created - user has been created
204 No Content - user has been deleted
404 Not Found - no resource found at the requested URI
503 Service Unavailable - the server is experiencing heavy load.