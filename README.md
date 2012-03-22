# CouchDB datasource plugin for CakePHP

DataSources are the link between models and the source of data that models represent. 

CouchDB is an open source document-oriented database written mostly in the Erlang programming language. 

### Version

Written for CakePHP 2.1+


### Installation

You can clone the plugin into your project (or if you want you can use as a [submodule](http://help.github.com/submodules)):

```
cd path/to/app/Plugin or /plugins
git clone https://github.com/maurymmarques/couchdb-datasource.git CouchDB
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

## Usage

The datasource works basically like CakePHP

### Saving a document

```php
<?php
$data = array('title' => 'My new title');
$this->Post->save($data);

// Id
$this->Post->id;

// Revision
$this->Post->rev;
```

### Search for a document

```php
<?php
$conditions = array('Post.id' => $this->Post->id);
$result = $this->Post->find('first', compact('conditions'));
```

### Change a document (changing the last revision)

```php
<?php
$data = array('title' => 'My new title');
$this->Post->id = '8e64f1eadab2b3b32c94ef2scf3094420';
$this->Post->save($data);
```

### Change a document to a particular revision

```php
<?php
$data = array('title' => 'My new title');
$this->Post->id = '8e64f1eadab2b3b32c94ef2scf3094420';
$this->Post->rev = '26-5cd5713759905feeee9b384edc4cfb61';
$this->Post->save($data);
```

### Deleting a document

```php
<?php
$this->Post->id = '8e64f1eadab2b3b32c94ef2scf3094420';
$this->Post->delete($data);
```

### REST

You can use the methods: curlGet, curlPost, curlPut, curlDelete

```php
<?php
$post = array(
	'source' => 'post',
	'target' => 'post-replicate',
	'countinuos' => true
);

$return = $this->Post->curlPost('_replicate', $post, true, false);
```
