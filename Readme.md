# HAPHAZARD 

  Haphazard is a smorgasbord of Zend Framework 1 development all compiled into a single Twitter Bootstrap project. It's a mixture of old and new development that I've decided to open source and place on GitHub as a backup and reference. A big chunk of the development is a few years old and will need to be implemented on a case by case other projects permitting basis.
      
## Features

  * Blog built using the adjacency list model aka modified database tree traversal algorithm.
  * A RESTful interface and Zend_Paginated administrator customer/users grid.
  * Underscore.js templating used on miscellaneous forms.
  * Hashed passwords using bcrypt.
  
## Installation
  
  Run the installation script to install the tables in the install module. Alternatively just run the schema manually.
    
  Manually insert the admin user. Please note passwords are hashed using bcrypt so don't attempt to change the password below.
  
    INSERT INTO `users` (`id`, `username`, `email`, `password`, `name`, `identity`, `newsletter`, `comment`, `created`, `modified`, `code`) VALUES
    (1, 'admin', 'admin@mysite.com', '$2a$10$hLuIdVvofgZvlRa.NuBsVeQLtC/NWQbCXn74Okv/A39aPdxfw0C02', 'Administrator', 3, 1, 'The best never rest.', '2012-05-12 21:59:20', '2012-05-12 21:59:20', '5ef8c9f26e55f844fd14194193ad061a');
    
  Don't forget to rename application/configs/application.sample.ini to application.ini and fill in your MySQL connection information.
    
## Logging In

  Login with the following:
  
    Email: admin@mysite.com
    Password: coolpassword 
    
   Once you have successfully logged in, head to the administration section and edit your email and password.

## License 

(The MIT License)

Copyright (c) 2011 Tom Shaw &lt;tom@visfx.me&gt;

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
'Software'), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
