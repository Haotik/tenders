<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Commission
 *
 */
class Commission_data extends CI_Model
{
	private $table_name					= 'tenders';					// tenders main
	private $commission_table_name		= 'tenders_commission';			// tenders commission

	function __construct()
	{
		parent::__construct();
	}

	function get_tenders_commission($user_id = 0)
	{
		$sql = "SELECT `t1`.*, `t2`.`name` as `author_name` FROM `" . $this->commission_table_name . "` `t1`, `user_profiles` `t2` WHERE `t1`.`admin_id` = `t2`.`user_id`" . ($user_id > 0 ? " AND `t1`.`admin_id` = " . (int)$user_id : "") . " ORDER BY `t2`.`name` ASC, `t1`.`rank` ASC, `t1`.`fio` ASC";

		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) return $query->result_array();
		return NULL;
	}

	function get_person_by_id($person_id, $user_id = 0)
	{
		$this->db->where('id', $person_id);
		if ($user_id > 0)
			$this->db->where('admin_id', (int)$user_id);

		$query = $this->db->get($this->commission_table_name);
		if ($query->num_rows() == 1) return $query->row_array();
		return NULL;
	}

	function create_person($data)
	{
		return $this->db->insert($this->commission_table_name, $data);
	}

	function update_person($data, $person_id)
	{
		$this->db->where('id', $person_id);
		return $this->db->update($this->commission_table_name, $data);
	}

	function delete_person($tender_id)
	{
		$this->db->where('id', $person_id);
		$this->db->delete($this->commission_table_name);
		if ($this->db->affected_rows() > 0) {
			return TRUE;
		}
		return FALSE;
	}
}

/* End of file commission_data.php */
/* Location: ./application/models/commission_data.php */