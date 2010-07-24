<?php
class Post extends AppModel {
	public $name = 'Post';
	public $displayField = 'title';
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
?>