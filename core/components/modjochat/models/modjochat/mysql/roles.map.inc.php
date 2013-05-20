<?php
$xpdo_meta_map['Roles']= array (
  'package' => 'modjochat',
  'version' => '1.1',
  'table' => 'roles',
  'extends' => 'xPDOSimpleObject',
  'fields' => 
  array (
    'role' => NULL,
  ),
  'fieldMeta' => 
  array (
    'role' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
    ),
  ),
  'composites' => 
  array (
    'RolesPermissions' => 
    array (
      'class' => 'RolesPermissions',
      'local' => 'id',
      'foreign' => 'role_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'UsersChannelsRoles' => 
    array (
      'class' => 'UsersChannelsRoles',
      'local' => 'id',
      'foreign' => 'role_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
);
