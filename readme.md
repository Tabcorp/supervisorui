Supervisor multi-server dashboard
=================================

Introduction
------------
This is a simple, quick and dirty dashboard that gives you an at-a-glance look at the state of all your supervisor using webservers. Also provides the ability to stop and start individual processes. It uses
 
  * [Silex](http://silex.sensiolabs.org/)
  * [Twitter Bootstrap](http://twitter.github.com/bootstrap/)
  * [jQuery](http://jquery.com/)
  * [Backbone.js](http://documentcloud.github.com/backbone/)
  * [The Incutio XML-RPC Library](http://scripts.incutio.com/xmlrpc/)

Requirements
------------
The only external dependency is the [Silex](http://silex.sensiolabs.org/) phar archive.
[Download silex.phar](http://silex.sensiolabs.org/get/silex.phar) and put it in the top dir of this application. All other dependencies are included.

Supervisor also needs to be configured to allow XML-RPC access on port 9001 on 127.0.0.1.

Configuration
-------------
Copy config.php.dist as config.php and edit as appropriate.

Download silex.phar and put it in the top level of this application.

Apache config changes:

```apache
Alias /supervisorui/ "/path/to/supervisorui/web/"

<Directory "/path/to/supervisorui/web">
	Order deny,allow
	Deny from all
	Allow from 127.0.0.1 <other private ip's here>
</Directory>
```

Either enable .htaccess overrides or put the contents of the web/.htaccess file into the above `<Directory>` block.

Supervisor (/etc/supervisord.conf) changes to enable XML-RPC access:

```ini
[inet_http_server]         ; inet (TCP) server disabled by default
port=127.0.0.1:9001
```

Restart apache and supervisord for these changes to take effect


Authors
-------
[Marcus Gatt](https://github.com/mrgatt)

License
-------
© 2012 Luxbet Pty Ltd.
Released under [The BSD 3 clause License](http://www.opensource.org/licenses/BSD-3-Clause)