<?php
	
	class Model
	{
		private static $db = NULL;
		
		    private static function get_insert_field_name($data) {
            $fieldNames = array_keys($data);
            $result = '`' . implode('`, `', $fieldNames) . '`';

            return $result;
        }

        private static function flatten_2d_array($arr) {
            $result = array();
            foreach ($arr as $row) {
                foreach ($row as $value) {
                    $result[] = $value;
                }
            }
            return $result;
        }

        public static function insert_data($table, $data) {
            if ($table == NULL || $data == NULL || !is_array($data))
                return FALSE;

            $sql = NULL;
            $record = array();

            if (!isset($data[0]) || !is_array($data[0])) {
                $temp[0] = $data;
                $data = $temp;
            }

            $fieldText = self::get_insert_field_name($data[0]);
            $questionText = '';
            $numFields = count($data[0]);
            foreach ($data as $row) {
                $questionMarks = array_fill(0, $numFields, '?');
                $questionText .= ', (' . implode(', ', $questionMarks) . ')';
            }
            $questionText = substr($questionText, 2);
            $record = self::flatten_2d_array($data);

            $sql = 'INSERT INTO `' . $table . '` (' . $fieldText . ') VALUES ' . $questionText;
            $query = DB::prepare($sql);
            $query->execute($record);

            if ($query->rowCount() > 0)
                return TRUE;

            return FALSE;
        }

        private static function get_field_string($fields) {
            if ($fields === NULL || $fields == '*') {
                return '*';
            }

            return ('`' . implode('`, `', $fields) . '`');
        }

        private static function get_where_string($where) {
            if ($where == NULL) {
                return NULL;
            }
            if (is_string($where)) {
                return (' WHERE ' . $where);
            } elseif (is_array($where)) {
                if (!isset($where[0]) || !is_array($where[0])) {
                    $temp[0] = $where;
                    $where = $temp;
                }

                $sql = ' WHERE';
                foreach ($where as $condition) {
                    $type = isset($condition['type']) ? $condition['type'] : '';
                    $signal = isset($condition['signal']) ? $condition['signal'] : '=';
                    $value = DB::quote($condition['value']);

                    $sql .= ' ' . $type . ' `' . $condition['field'] . '` ' . $signal . ' ' . $value;
                }
                
                $sql = str_replace(' WHERE AND', ' WHERE', $sql);
                $sql = str_replace(' WHERE OR', ' WHERE', $sql);

                return $sql;
            }

            return NULL;
        }

        private static function get_order_string($order) {
            if ($order == NULL) {
                return NULL;
            }
            if (is_string($order)) {
                return (' ORDER BY ' . $order);
            } elseif (is_array($order)) {
                if (!isset($order[0]) || !is_array($order[0])) {
                    $temp[0] = $order;
                    $order = $temp;
                }

                $orderString = '';
                foreach ($order as $field) {
                    $orderString .= ', `' . $field['field'] . '` ' . $field['type'];
                }
                $orderString = substr($orderString, 2);
                $sql = 'ORDER BY ' . $orderString;

                return $sql;
            }

            return NULL;
        }

        private static function get_limit_string($limit) {
            if ($limit == NULL) {
                return NULL;
            }
            $limit = intval($limit);
            $sql = ' LIMIT ' . $limit;

            return $sql;
        }

        private static function get_offset_string($offset) {
            if ($offset == NULL) {
                return NULL;
            }
            $offset = intval($offset);
            $sql = ' OFFSET ' . $offset;

            return $sql;
        }

        public static function get_data($table, $fields = '*', $where = NULL, $limit = NULL,$order = NULL, $offset = NULL) {
            $fieldString = self::get_field_string($fields);
            $whereString = self::get_where_string($where);
            $orderString = self::get_order_string($order);
            $limitString = self::get_limit_string($limit);
            $offsetString = self::get_offset_string($offset);

            $sql = 'SELECT ' . $fieldString . ' FROM `' . $table . '` ' . $whereString . ' ' . $orderString . $limitString . $offsetString;

            $query = DB::prepare($sql);
            $query->execute();

            if ($query->rowCount() === 0)
                return array();

            if ($limit == 1)
                $data = $query->fetch(PDO::FETCH_ASSOC);
            else
                $data = $query->fetchAll(PDO::FETCH_ASSOC);

            return $data;
        }

        private static function get_set_string($data) {
            $setString = '';
            foreach ($data as $key => $value) {
                $setString .= ', `' . $key . '` = ' . DB::quote($value);
            }
            $setString = substr($setString, 2);
            $sql = 'SET ' . $setString;

            return $sql;
        }

        public static function update_data($table, $data, $where) {
            $setString = self::get_set_string($data);
            $whereString = self::get_where_string($where);
            $sql = 'UPDATE `' . $table . '` ' . $setString . $whereString;

            $query = DB::prepare($sql);
            $query->execute();

            $numRowsAffected = intval($query->rowCount());

            return $numRowsAffected;
        }

        public static function delete_data($table, $where) {
            $whereString = self::get_where_string($where);
            $sql = 'DELETE FROM `' . $table . '` ' . $whereString;

            $query = DB::prepare($sql);
            $query->execute();

            $numRowsAffected = intval($query->rowCount());

            return $numRowsAffected;
        }
	}
