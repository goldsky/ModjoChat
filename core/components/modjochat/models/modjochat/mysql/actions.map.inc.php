<?php
$xpdo_meta_map['Actions']= array (
  'package' => 'modjochat',
  'version' => '1.1',
  'table' => 'actions',
  'extends' => 'xPDOSimpleObject',
  'fields' => 
  array (
    'channel_id' => NULL,
    'timestamp' => NULL,
    'originating_user' => NULL,
    'destination_user' => NULL,
    'action_type' => NULL,
    'action_text' => NULL,
  ),
  'fieldMeta' => 
  array (
    'channel_id' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '50',
      'phptype' => 'string',
      'null' => true,
      'index' => 'index',
    ),
    'timestamp' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
    ),
    'originating_user' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
    ),
    'destination_user' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
    ),
    'action_type' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '50',
      'phptype' => 'string',
      'null' => true,
    ),
    'action_text' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
    ),
  ),
  'indexes' => 
  array (
    'channel_id' => 
    array (
      'alias' => 'channel_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'channel_id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => true,
        ),
      ),
    ),
  ),
  'aggregates' => 
  array (
    'Channels' => 
    array (
      'class' => 'Channels',
      'local' => 'channel_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);
