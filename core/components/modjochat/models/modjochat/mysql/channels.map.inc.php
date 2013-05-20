<?php
$xpdo_meta_map['Channels']= array (
  'package' => 'modjochat',
  'version' => '1.1',
  'table' => 'channels',
  'extends' => 'xPDOObject',
  'fields' => 
  array (
    'id' => NULL,
    'description' => NULL,
    'greeting' => NULL,
    'open_time' => NULL,
    'open_day' => NULL,
    'close_time' => NULL,
    'close_day' => NULL,
    'created_by' => NULL,
    'is_restricted' => 0,
    'password' => NULL,
    'is_guest_allowed' => 1,
  ),
  'fieldMeta' => 
  array (
    'id' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '50',
      'phptype' => 'string',
      'null' => false,
      'index' => 'pk',
    ),
    'description' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
    ),
    'greeting' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
    ),
    'open_time' => 
    array (
      'dbtype' => 'time',
      'phptype' => 'string',
      'null' => true,
    ),
    'open_day' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
    ),
    'close_time' => 
    array (
      'dbtype' => 'time',
      'phptype' => 'string',
      'null' => true,
    ),
    'close_day' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
    ),
    'created_by' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
    ),
    'is_restricted' => 
    array (
      'dbtype' => 'int',
      'precision' => '1',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
      'default' => 0,
    ),
    'password' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
    ),
    'is_guest_allowed' => 
    array (
      'dbtype' => 'int',
      'precision' => '1',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
      'default' => 1,
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
        'id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
  ),
  'composites' => 
  array (
    'OnlineUsers' => 
    array (
      'class' => 'OnlineUsers',
      'local' => 'id',
      'foreign' => 'channel_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'Messages' => 
    array (
      'class' => 'Messages',
      'local' => 'id',
      'foreign' => 'channel_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'UsersChannelsRoles' => 
    array (
      'class' => 'UsersChannelsRoles',
      'local' => 'id',
      'foreign' => 'channel_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'Actions' => 
    array (
      'class' => 'Actions',
      'local' => 'id',
      'foreign' => 'channel_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
);
