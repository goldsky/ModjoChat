<?php
$xpdo_meta_map['RolesPermissions']= array (
  'package' => 'modjochat',
  'version' => '1.1',
  'table' => 'roles_permissions',
  'extends' => 'xPDOObject',
  'fields' => 
  array (
    'role_id' => NULL,
    'key' => NULL,
    'value' => NULL,
  ),
  'fieldMeta' => 
  array (
    'role_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'index' => 'pk',
    ),
    'key' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'index' => 'pk',
    ),
    'value' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
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
        'role_id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
        'key' => 
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
    'Roles' => 
    array (
      'class' => 'Roles',
      'local' => 'role_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);
