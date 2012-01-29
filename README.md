# Couchdb datasource plugin for CakePHP

DataSources are the link between models and the source of data that models represent. 

CouchDB is an open source document-oriented database written mostly in the Erlang programming language. 

### Version

Written for CakePHP 2.0+


### Installation

You can clone the plugin into your project (or if you want you can use as a [submodule](http://help.github.com/submodules)):

```
cd path/to/app/Plugin or /plugins
git clone git@github.com:maurymmarques/couchdb-datasource.git CouchDB
```

Bootstrap the plugin in app/Config/bootstrap.php:

```php
<?php
CakePlugin::load('CouchDB');
```

### Configuration

Connection in app/Config/database.php:

```php
<?php
class DATABASE_CONFIG {

	public $default = array(
		'datasource'	=> 'CouchDB.CouchDBSource',
		'persistent'	=> false,
		'host'			=> 'localhost',
		'port'			=> '5984',
		'login'			=> 'root',
		'password'		=> 'root',
		'database'		=> null,
		'prefix'		=> ''
	);

}
```