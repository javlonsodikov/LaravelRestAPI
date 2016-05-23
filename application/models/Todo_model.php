<?php

/**
 * Created by PhpStorm.
 * Author: Javlon Sodikov
 * Date time: 26.04.2016 11:42
 */
class Todo_model extends CI_Model
{
    const TABLE = "todolist";

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function add_todo($item)
    {
        $this->db->insert(self::TABLE, $item);
        return $this->db->insert_id();
    }

    public function update_todo($item, $where)
    {
        $this->db->where("user_id", $where["user_id"]);
        $this->db->where("todo_id", $where["todo_id"]);
        $this->db->update(self::TABLE, $item);
        return $this->db->affected_rows();
    }

    public function list_todo()
    {
        $this->db->select('SQL_CALC_FOUND_ROWS todolist.*', false);
        $this->db->order_by('created', 'DESC');
        $this->db->join('users', "users.user_id=" . self::TABLE . ".user_id");
        $query = $this->db->get(self::TABLE);
        $return['items'] = $query->result_array();
        $return['count'] = $this->db->query('SELECT FOUND_ROWS() count;')->row()->count;
        return $return;
    }

    public function delete_todo($item)
    {
        $this->db->delete(self::TABLE, $item);
        return $this->db->affected_rows();
    }

}
