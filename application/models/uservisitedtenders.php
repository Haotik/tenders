<?php

/**
 * Class uservisitedtenders
 *
 * @property int $user_id
 * @property int $tender_id
 *
 */
class uservisitedtenders extends CI_Model
{
    private $table_name = 'user_visited_tenders';

    public function __construct()
    {
        parent::__construct();
    }

    public function findAll()
    {
        $query = $this->db->get($this->table_name);
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return null;
    }

    public function create($data)
    {
        return $this->db->insert($this->table_name, $data);
    }

    public function update($data, $id)
    {
        $this->db->where('id', $id);
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

    public function getVisitedUsers($tender_id)
    {
        $sql = "SELECT *
                FROM `user_profiles` up
                LEFT JOIN `users` u ON up.user_id = u.id
                WHERE `user_id` IN (SELECT `user_id` FROM `user_visited_tenders` WHERE `tender_id` = {$tender_id}) AND 
                `user_id` NOT IN (SELECT `user_id` FROM `tenders_results` WHERE `tender_id` = {$tender_id})";

        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {
            return $query->result();
        }

        return null;
    }
}