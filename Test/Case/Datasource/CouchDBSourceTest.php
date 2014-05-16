<?php
/**
 * CouchDB DataSource Test file.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       CouchDB.Test.Case.Datasource
 * @since         CakePHP Datasources v 0.3
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('AppModel', 'Model');

/**
 * Post Model for the test.
 */
class Post extends AppModel {

/**
 * Name of the model.
 *
 * @var string
 */
	public $name = 'Post';

/**
 * Custom display field name.
 *
 * @var string
 */
	public $displayField = 'title';

/**
 * Number of associations to recurse through during find calls.
 *
 * @var integer
 */
	public $recursive = -1;

/**
 * List of validation rules.
 *
 * @var array
 */
	public $validate = array(
		'title' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

/**
 * Field-by-field table metadata.
 *
 * @var array
 */
	public $schema = array(
		'id' => array(
			'type' => 'string',
			'null' => true,
			'key' => 'primary',
			'length' => 32,
		),
		'rev' => array(
			'type' => 'string',
			'null' => true,
			'key' => 'primary',
			'length' => 34,
		),
		'title' => array(
			'type' => 'string',
			'null' => true,
			'length' => 255,
		),
		'description' => array(
			'type' => 'text',
			'null' => true,
		)
	);
}

/**
 * CouchDBTestCase.
 *
 * @package       CouchDB.Test.Case.Datasource
 */
class CouchDBTestCase extends CakeTestCase {

/**
 * CouchDB Datasource object.
 *
 * @var object
 */
	public $CouchDB = null;

/**
 * Configuration.
 *
 * @var array
 */
	protected $_config = array(
		'datasource' => 'CouchDB.CouchDBSource',
		'persistent' => false,
		'host' => 'localhost',
		'port' => '5984',
		'login' => 'root',
		'password' => '',
		'database' => null,
		'prefix' => '',
	);

/**
 * Start Test.
 *
 * @return void
 */
	public function setUp() {
		config('database');
		$config = new DATABASE_CONFIG();

		if (isset($config->couchdb_test)) {
			$this->_config = $config->couchdb_test;
		}

		ConnectionManager::create('couchdb_test', $this->_config);

		$this->Post = ClassRegistry::init('Post');
		$this->Post->useDbConfig = 'couchdb_test';
		$this->__removeAllDocuments();
	}

/**
 * Test connection.
 *
 * @return void
 */
	public function testConnection() {
		$this->CouchDB = new CouchDBSource($this->_config);
		$this->CouchDB = ConnectionManager::getDataSource($this->Post->useDbConfig);

		$reconnect = $this->CouchDB->reconnect($this->_config);
		$this->assertSame($reconnect, true, __d('test_cases', 'Not reconnected'));

		$disconnect = $this->CouchDB->disconnect();
		$this->assertSame($disconnect, true, __d('test_cases', 'Not disconnect'));
	}

/**
 * Test find.
 *
 * @return void
 */
	public function testFind() {
		$data = array(
			'title' => 'My first post',
			'description' => 'My first post'
		);
		$this->Post->save($data);

		$result = $this->Post->find('all');
		$this->assertEquals(1, count($result));

		$resultData = $result[0]['Post'];
		$this->assertEquals(4, count($resultData));
		$this->assertTrue(!empty($resultData['id']));
		$this->assertEquals($this->Post->id, $resultData['id']);
		$this->assertEquals($this->Post->rev, $resultData['rev']);
		$this->assertEquals($data['title'], $resultData['title']);
		$this->assertEquals($data['description'], $resultData['description']);
	}

/**
 * Test find conditions.
 *
 * @return void
 */
	public function testFindConditions() {
		$data = array(
			'title' => 'My first post',
			'description' => 'My first post'
		);
		$this->Post->save($data);

		$this->Post->create();
		$this->Post->save($data);

		$result = $this->Post->find('all');
		$this->assertEquals(2, count($result));

		$result = $this->Post->find('all', array('conditions' => array('Post.id' => $this->Post->id)));
		$this->assertEquals(1, count($result));

		$result = $this->Post->find('all', array('conditions' => array('id' => $this->Post->id)));
		$this->assertEquals(1, count($result));
	}

/**
 * Test find revs.
 *
 * @return void
 */
	public function testFindRevs() {
		$data = array(
			'title' => 'My first post',
			'description' => 'My first post'
		);
		$this->Post->save($data);
		$this->Post->save($data);

		$this->Post->recursive = 0;
		$result = $this->Post->find('all', array('conditions' => array('id' => $this->Post->id)));
		$this->assertEquals(2, count($result[0]['Post']['_revs_info']));
	}

/**
 * Tests save method.
 *
 * @return void
 */
	public function testSave() {
		$data = array(
			'title' => 'My first post',
			'description' => 'My first post'
		);

		$this->Post->create();
		$saveResult = $this->Post->save($data);
		$this->assertSame(is_array($saveResult), true);

		$result = $this->Post->find('all');
		$this->assertEquals(1, count($result));

		$resultData = $result[0]['Post'];
		$this->assertEquals(4, count($resultData));
		$this->assertTrue(!empty($resultData['id']));
		$this->assertEquals($this->Post->id, $resultData['id']);
		$this->assertEquals($this->Post->rev, $resultData['rev']);
		$this->assertEquals($data['title'], $resultData['title']);
		$this->assertEquals($data['description'], $resultData['description']);
	}

/**
 * Tests save method.
 *
 * @return void
 */
	public function testSaveWithId() {
		$data = array(
			'id' => String::uuid(),
			'title' => 'My first post',
			'description' => 'My first post'
		);

		$this->Post->create();
		$saveResult = $this->Post->save($data);
		$this->assertSame(is_array($saveResult), true);

		$result = $this->Post->find('all');
		$this->assertEquals(1, count($result));

		$resultData = $result[0]['Post'];
		$this->assertEquals(4, count($resultData));
		$this->assertTrue(!empty($resultData['id']));
		$this->assertEquals($resultData['id'], $data['id']);
		$this->assertEquals($this->Post->id, $resultData['id']);
		$this->assertEquals($this->Post->rev, $resultData['rev']);
		$this->assertEquals($data['title'], $resultData['title']);
		$this->assertEquals($data['description'], $resultData['description']);
	}

/**
 * Tests saveAll method.
 *
 * @return void
 */
	public function testSaveAll() {
		$data[0]['Post'] = array(
			'title' => 'My first post',
			'description' => 'My first post'
		);

		$data[1]['Post'] = array(
			'title' => 'My second post',
			'description' => 'My second post'
		);

		$this->Post->create();
		$saveResult = $this->Post->saveAll($data);

		$result = $this->Post->find('all');
		$this->assertEquals(2, count($result));

		$resultData = $result[0]['Post'];
		$this->assertEquals(4, count($resultData));
		$this->assertTrue(!empty($resultData['id']));
		$this->assertEquals($data[0]['Post']['title'], $resultData['title']);
		$this->assertEquals($data[0]['Post']['description'], $resultData['description']);

		$resultData = $result[1]['Post'];
		$this->assertEquals(4, count($resultData));
		$this->assertTrue(!empty($resultData['id']));
		$this->assertEquals($data[1]['Post']['title'], $resultData['title']);
		$this->assertEquals($data[1]['Post']['description'], $resultData['description']);
	}

/**
 * Tests update method.
 *
 * @return void
 */
	public function updateTest() {
		// Count posts
		$uri = '/posts/_temp_view?group=true';
		$post = array(
			'map' => 'function(doc) { emit(doc._id,1); }',
			'reduce' => 'function(keys, values) { return sum(values); }'
		);

		$mapReduce = $this->Post->query($uri, $post);

		if (isset($mapReduce->rows[0]->value)) {
			$count0 = $mapReduce->rows[0]->value;
		} else {
			$count0 = 0;
		}

		$count1 = $this->__updateTest1($uri, $post, $count0);
		$count2 = $this->__updateTest2($uri, $post, $count1);
		$count3 = $this->__updateTest3($uri, $post, $count2);
		$count4 = $this->__updateTest4($uri, $post, $count2);
		$updateData = $this->__updateTest5($uri, $post, $count4);

		// Final test
		$result = $this->Post->find('all');
		$this->assertEquals(1, count($result));

		$resultData = $result[0]['Post'];
		$this->assertEquals(4, count($resultData));
		$this->assertTrue(!empty($resultData['id']));
		$this->assertEquals($this->Post->id, $resultData['id']);
		$this->assertEquals($this->Post->rev, $resultData['rev']);
		$this->assertNotEquals($updateData['title'], $resultData['title']);
		$this->assertNotEquals($updateData['description'], $resultData['description']);
	}

/**
 * Tests update1 method.
 *
 * @param string $uri
 * @param array $post
 * @param integer $previousCount
 * @return integer
 */
	private function __updateTest1($uri, $post, $previousCount) {
		$data = array(
			'title' => 'My first post',
			'description' => 'My first post'
		);

		$this->Post->create();
		$saveResult = $this->Post->save($data);
		$this->assertSame(is_array($saveResult), true);
		$this->assertSame(!empty($this->Post->id), true);

		$mapReduce = $this->Post->curlPost($uri, $post);
		$count1 = $mapReduce['rows'][0]['value'];

		$this->assertSame($count1 - $previousCount, 1);

		return $count1;
	}

/**
 * Tests update2 method.
 *
 * @param string $uri
 * @param array $post
 * @param integer $previousCount
 * @return integer
 */
	private function __updateTest2($uri, $post, $previousCount) {
		$findResult = $this->Post->find('first');
		$this->assertEquals(4, count($findResult['Post']));

		$updateData = array(
			'title' => 'My post update',
			'description' => 'My post update'
		);

		$this->Post->id = $findResult['Post']['id'];
		$this->Post->rev = $findResult['Post']['rev'];
		$saveResult = $this->Post->save($updateData);
		$this->assertSame(is_array($saveResult), true);

		$mapReduce = $this->Post->curlPost($uri, $post);
		$count2 = $mapReduce['rows'][0]['value'];

		$this->assertSame($count2 - $previousCount, 0);

		return $count2;
	}

/**
 * Tests update3 method.
 *
 * @param string $uri
 * @param array $post
 * @param integer $previousCount
 * @return integer
 */
	private function __updateTest3($uri, $post, $previousCount) {
		$findResult = $this->Post->find('first');
		$this->assertEquals(4, count($findResult['Post']));

		$updateData = array(
			'id' => $findResult['Post']['id'],
			'title' => 'My post update',
			'description' => 'My post update'
		);

		$this->Post->rev = $findResult['Post']['rev'];
		$saveResult = $this->Post->save($updateData);
		$this->assertSame(is_array($saveResult), true);
		$this->assertSame($this->Post->id, $findResult['Post']['id']);

		$mapReduce = $this->Post->curlPost($uri, $post);
		$count3 = $mapReduce['rows'][0]['value'];

		$this->assertSame($count3 - $previousCount, 0);

		return $count3;
	}

/**
 * Tests update4 method.
 *
 * @param string $uri
 * @param array $post
 * @param integer $previousCount
 * @return integer
 */
	private function __updateTest4($uri, $post, $previousCount) {
		$findResult = $this->Post->find('first');
		$this->assertEquals(4, count($findResult['Post']));

		$updateData = array(
			'id' => $findResult['Post']['id'],
			'rev' => $findResult['Post']['rev'],
			'title' => 'My post update',
			'description' => 'My post update'
		);

		$saveResult = $this->Post->save($updateData);
		$this->assertSame(is_array($saveResult), true);
		$this->assertSame($this->Post->id, $findResult['Post']['id']);

		$mapReduce = $this->Post->curlPost($uri, $post);
		$count4 = $mapReduce['rows'][0]['value'];

		$this->assertSame($count4 - $previousCount, 0);

		return $count4;
	}

/**
 * Tests update5 method.
 *
 * @param string $uri
 * @param array $post
 * @param integer $previousCount
 * @return integer
 */
	private function __updateTest5($uri, $post, $previousCount) {
		$findResult = $this->Post->find('first');
		$this->assertEquals(4, count($findResult['Post']));

		$updateData = array(
			'id' => $findResult['Post']['id'],
			'rev' => 'whatever',
			'title' => 'My post fail',
			'description' => 'My post fail'
		);

		$saveResult = $this->Post->save($updateData);
		$this->assertFalse($saveResult);
		$this->assertSame($this->Post->id, $findResult['Post']['id']);

		$mapReduce = $this->Post->curlPost($uri, $post);
		$count5 = $mapReduce['rows'][0]['value'];

		$this->assertSame($count5 - $previousCount, 0);

		return $count5;
	}

/**
 * Test update without revision.
 *
 * @return void
 */
	public function testUpdateWithoutRevision() {
		$data = array(
			'title' => 'My first post',
			'description' => 'My first post'
		);

		$this->Post->create();
		$saveResult = $this->Post->save($data);

		$result = $this->Post->find('first');

		unset($result['Post']['rev']);
		unset($this->Post->rev);

		$updateResult = $this->Post->save($result);

		$this->assertSame(is_array($updateResult), true);
		$this->assertSame($this->Post->id, $saveResult['Post']['id']);
	}

/**
 * Tests delete method.
 *
 * @return void
 */
	public function testDelete() {
		$data = array(
			'title' => 'My first post',
			'description' => 'My first post'
		);

		$this->Post->create();
		$saveResult = $this->Post->save($data);

		$result = $this->Post->find('all');
		$this->assertEquals(1, count($result));

		$this->Post->id = $result[0]['Post']['id'];
		$this->Post->rev = $result[0]['Post']['rev'];
		$this->Post->delete();

		$result = $this->Post->find('all');
		$this->assertEquals(0, count($result));
	}

/**
 * Test delete without revision.
 *
 * @return void
 */
	public function testDeleteWithoutRevision() {
		$data = array(
			'title' => 'My first post',
			'description' => 'My first post'
		);

		$this->Post->create();
		$saveResult = $this->Post->save($data);

		$result = $this->Post->find('all');
		$this->assertEquals(1, count($result));

		unset($result['Post']['rev']);
		unset($this->Post->rev);

		$this->Post->delete();

		$result = $this->Post->find('all');
		$this->assertEquals(0, count($result));
	}

/**
 * Tests query method.
 *
 * @return void
 */
	public function testQuery() {
		// GET
		$result = $this->Post->curlGet('_all_dbs');
		$this->assertSame(is_array($result), true);

		// POST
		$data = array(
			'title' => 'My first post',
			'description' => 'My first post'
		);

		$result = $this->Post->curlPost('/posts', $data);
		$this->assertSame($result['ok'], true);

		// PUT
		$data = array(
			'_rev' => $result['rev'],
			'title' => 'My first update',
			'description' => 'My first update'
		);

		$result = $this->Post->curlPut('/posts/' . $result['id'], $data);
		$this->assertSame($result['ok'], true);

		// DELETE
		$result = $this->Post->curlDelete('/posts/' . $result['id'] . '/?rev=' . $result['rev']);
		$this->assertSame($result['ok'], true);
	}

/**
 * Remove all documents from database.
 *
 * @return void
 */
	private function __removeAllDocuments() {
		$posts = $this->Post->find('list', array('fields' => array('Post.rev')));

		foreach ($posts as $id => $post) {
			$this->Post->rev = $post;
			$this->Post->delete($id);
		}
	}

/**
 * End Test
 *
 * @return void
 */
	public function tearDown() {
		$this->__removeAllDocuments();
		unset($this->Post);
		unset($this->CouchDB);
		ClassRegistry::flush();
	}
}
