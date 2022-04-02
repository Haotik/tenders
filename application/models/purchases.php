<?php
/**
 * Created by PhpStorm.
 * User: Anatoly
 * Date: 19.07.2018
 * Time: 12:25
 */

class Purchases extends CI_Model
{
    private $table_name = 'purchases';

    public function __construct()
    {
        parent::__construct();
    }

    public function findAll()
    {
        $query = $this->db->get($this->table_name);;
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return null;
    }

    public function findByPk($purchase_id)
    {
        $this->db->where('purchase_id', (int)$purchase_id);
        $query = $this->db->get($this->table_name);;
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return null;
    }

    public function create($data)
    {
        return $this->db->insert($this->table_name, $data);
    }

    public function update($data, $purchase_id)
    {
        $this->db->where('id', $purchase_id);
        return $this->db->update($this->table_name, $data);
    }

    public function findAllByAttributes(array $condition, $limit = null, $offset = null)
    {
        $query = $this->db->get_where($this->table_name, $condition, $limit, $offset);
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return null;
    }
}