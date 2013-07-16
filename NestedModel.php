<?php

  // Class name : NestedModel
  // Author     : KhanhIceTea
  // Website    : SongViDamMe.com
  
	class NestedModel
	{
		private $table = '';
		private $id_field = '';
		private $left_field = '';
		private $right_field = '';
		private $level_field = '';
		
		public function get_all_nodes()
		{
			$order = array('field' => $this->left_field, 'type' => 'ASC');
			
			return Model::get_data($this->table, '*', NULL, NULL, $order);
		}
		
		public function install($table, $id_field, $left_field, $right_field, $level_field)
		{
			$this->table = $table;
			$this->id_field = $id_field;
			$this->left_field = $left_field;
			$this->right_field = $right_field;
			$this->level_field = $level_field;
		}
		
		private function get_max_right()
		{
			$sql = "SELECT MAX(`{$this->right_field}`) as `max` FROM `{$this->table}`";
			$query = DB::query($sql);
			$row = DB::fetch($query);			
			$max = $row['max'];
			
			return ($max == NULL) ? 0 : intval($max) + 1;
		}
		
		public function get_node($node_id)
		{
			if ($node_id == 0)
			{
				return array(
					$this->id_field => 0,
					$this->left_field => -1,
					$this->right_field => $this->get_max_right(),
					$this->level_field => 0
				);
			}
			
			$sql = "SELECT * FROM `{$this->table}` WHERE `{$this->id_field}` = '{$node_id}'";
			$query = DB::query($sql);
			$row = DB::fetch($query);
			
			return $row;
		}
		
		public function add_node($node, $parent_id)
		{
			$parent = $this->get_node($parent_id);
			$parent_right = $parent[$this->right_field];
			$parent_level = $parent[$this->level_field];
			
			$sql = "UPDATE `{$this->table}` SET `{$this->left_field}` = `{$this->left_field}` + 2 WHERE `{$this->left_field}` > '{$parent_right}'";
			DB::query($sql);
			
			$sql = "UPDATE `{$this->table}` SET `{$this->right_field}` = `{$this->right_field}` + 2 WHERE `{$this->right_field}` >= '{$parent_right}'";
			DB::query($sql);
			
			$node[$this->left_field] = $parent_right;
			$node[$this->right_field] = $parent_right + 1;
			$node[$this->level_field] = $parent_level + 1;
			
			Model::insert_data($this->table, $node);
			
			return TRUE;
		}
		
		public function remove_node($node_id)
		{
			$node = $this->get_node($node_id);
			$node_left = $node[$this->left_field];
			$node_right = $node[$this->right_field];
			$size = $node_right - $node_left + 1;
			
			$sql = "DELETE FROM `{$this->table}` WHERE `{$this->left_field}` >= '$node_left' AND `{$this->right_field}` <= '$node_right'";
			DB::query($sql);
			
			$sql = "UPDATE `{$this->table}` SET `{$this->left_field}` = `{$this->left_field}` - $size WHERE `{$this->left_field}` > '$node_right'";
			DB::query($sql);
			
			$sql = "UPDATE `{$this->table}` SET `{$this->right_field}` = `{$this->right_field}` - $size WHERE `{$this->right_field}` > '$node_right'";
			DB::query($sql);
			
			return TRUE;
		}
		
		public function move_node($node_id, $parent_id)
		{
			if ($node_id == $parent_id)
				return FALSE;
			
			$node = $this->get_node($node_id);
			$parent = $this->get_node($parent_id);
			
			$node_left = $node[$this->left_field];
			$node_right = $node[$this->right_field];
			$node_level = $node[$this->level_field];
			$size = $node_right - $node_left + 1;
			
			$parent_left = $parent[$this->left_field];
			$parent_right = $parent[$this->right_field];
			$parent_level = $parent[$this->level_field];
			
			if ($parent_left > $node_left && $parent_right < $node_right)
			{
				return FALSE;
			}
			
			$distance_level = $parent_level - $node_level + 1;
			
			$sql = "UPDATE `{$this->table}` SET `{$this->left_field}` = - `{$this->left_field}`, `{$this->right_field}` = - `{$this->right_field}` WHERE `{$this->left_field}` >= '$node_left' AND `{$this->right_field}` <= '$node_right'";
			DB::query($sql);
			
			if ($parent_right > $node_right)
			{
				$sql = "UPDATE `{$this->table}` SET `{$this->left_field}` = `{$this->left_field}` - $size WHERE `{$this->left_field}` > '$node_right' AND `{$this->left_field}` < '$parent_right'";
				DB::query($sql);
				
				$sql = "UPDATE `{$this->table}` SET `{$this->right_field}` = `{$this->right_field}` - $size WHERE `{$this->right_field}` > '$node_right' AND `{$this->right_field}` < '$parent_right'";
				DB::query($sql);
				
				$need = $parent_right - $size;
				$distance = $need - $node_left;
				
				$sql = "UPDATE `{$this->table}` SET `{$this->left_field}` = - `{$this->left_field}` + $distance , `{$this->right_field}` = - `{$this->right_field}` + $distance, `{$this->level_field}` = `{$this->level_field}` + ({$distance_level}) WHERE `{$this->left_field}` < 0";
				DB::query($sql);
			}
			else
			{
				$sql = "UPDATE `{$this->table}` SET `{$this->left_field}` = `{$this->left_field}` + $size WHERE `{$this->left_field}` > '$parent_right' AND `{$this->left_field}` < '$node_left'";
				DB::query($sql);
				
				$sql = "UPDATE `{$this->table}` SET `{$this->right_field}` = `{$this->right_field}` + $size WHERE `{$this->right_field}` >= '$parent_right' AND `{$this->right_field}` < '$node_left'";
				DB::query($sql);
				
				$distance = $node_left - $parent_right;
				
				$sql = "UPDATE `{$this->table}` SET `{$this->left_field}` = - `{$this->left_field}` - $distance , `{$this->right_field}` = - `{$this->right_field}` - $distance, `{$this->level_field}` = `{$this->level_field}` + ({$distance_level}) WHERE `{$this->left_field}` < 0";
				DB::query($sql);
			}
			
			return TRUE;
		}
	}
