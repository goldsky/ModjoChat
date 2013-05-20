<?php
$xpdo_meta_map['OnlineUsers']= array (
  'package' => 'modjochat',
  'version' => '1.1',
  'table' => 'online_users',
  'extends' => 'xPDOObject',
  'fields' => 
  array (
    'user_id' => NULL,
    'ip_address' => NULL,
    'user_name' => NULL,
    'channel_id' => NULL,
    'timeout' => NULL,
  ),
  'fieldMeta' => 
  array (
    'user_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'index' => 'pk',
    ),
    'ip_address' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '50',
      'phptype' => 'string',
      'null' => false,
      'index' => 'pk',
    ),
    'user_name' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '50',
      'phptype' => 'string',
      'null' => false,
      'index' => 'pk',
    ),
    'channel_id' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '50',
      'phptype' => 'string',
      'null' => false,
      'index' => 'pk',
    ),
    'timeout' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
    ),
  ),
  'indexes' => 
  array (
    'PRIMARY' => 
    array (
      'alias' => 'PRIMARY',
      'primary' => true,
      'unique' => true,
      'type' => 'BTREE',
      'columns' => 
      array (
        'user_id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
        'ip_address' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
        'user_name' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
        'channel_id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
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
