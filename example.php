<?php
  include 'Db.php';
  include 'Model.php';
  include 'NestedModel.php';
  
  $nested_model = new NestedModel;
  $nested_model->install('categories', 'id', 'left', 'right', 'level');
  
  $category = array(
    'name' => 'Cat 1',
    'description' => 'Category 1'
  );
  
  $nested_model->add_node($category, 0);  ///// id = 1
  
  $category = array(
    'name' => 'Cat 2',
    'description' => 'Category 2'
  );
  
  $nested_model->add_node($category, 0); ///// id = 2
  
  $category = array(
    'name' => 'Cat 3',
    'description' => 'Sub category of 1'
  );
  
  $nested_model->add_node($category, 1); ////// id = 3
  
  
  // Delete node 'Category 1'
  $nested_model->remove_node(1);
