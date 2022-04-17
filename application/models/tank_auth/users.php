<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Users
 *
 * This model represents user authentication data. It operates the following tables:
 * - user account data,
 * - user profiles
 *
 * @package	Tank_auth
 * @author	Ilya Konyukhov (http://konyukhov.com/soft/)
 */
class Users extends CI_Model
{
	private $table_name			= 'users';			// user accounts
	private $profile_table_name	= 'user_profiles';	// user profiles
	private $group_table_name	= 'user_groups';	// user groups

	function __construct()
	{
		parent::__construct();

		$ci =& get_instance();
		$this->table_name			= $ci->config->item('db_table_prefix', 'tank_auth').$this->table_name;
		$this->profile_table_name	= $ci->config->item('db_table_prefix', 'tank_auth').$this->profile_table_name;
		$this->group_table_name		= $ci->config->item('db_table_prefix', 'tank_auth').$this->group_table_name;
	}

	/**
	 * Get user record by Id
	 *
	 * @param	int
	 * @param	bool
	 * @return	object
	 */
	function get_user_by_id($user_id)
	{
		$this->db->where('id', $user_id);

		$query = $this->db->get($this->table_name);
		if ($query->num_rows() == 1) return $query->row();
		return NULL;
	}

	/**
	 * Get user record by login (username or email)
	 *
	 * @param	string
	 * @return	object
	 */
	function get_user_by_login($login)
	{
		$this->db->where('LOWER(username)=', strtolower($login));
		$this->db->or_where('LOWER(email)=', strtolower($login));

		$query = $this->db->get($this->table_name);
		if ($query->num_rows() == 1) return $query->row();
		return NULL;
	}

	/**
	 * Get user record by username
	 *
	 * @param	string
	 * @return	object
	 */
	function get_user_by_username($username)
	{
		$this->db->where('LOWER(username)=', strtolower($username));

		$query = $this->db->get($this->table_name);
		if ($query->num_rows() == 1) return $query->row();
		return NULL;
	}

	/**
	 * Get user record by email
	 *
	 * @param	string
	 * @return	object
	 */
	function get_user_by_email($email)
	{
		$this->db->where('LOWER(email)=', strtolower($email));

		$query = $this->db->get($this->table_name);
		if ($query->num_rows() == 1) return $query->row();
		return NULL;
	}

	/**
	 * Get user profile by Id
	 *
	 * @param	string
	 * @return	object
	 */
	function get_user_profile($user_id)
	{
		$this->db->where('user_id', $user_id);

		$query = $this->db->get($this->profile_table_name);
		if ($query->num_rows() == 1) return $query->row();
		return NULL;
	}

	/**
	 * Get user group by Id
	 *
	 * @param	string
	 * @return	object
	 */
	function get_user_group($group_id)
	{
		$this->db->where('id', $group_id);

		$query = $this->db->get($this->group_table_name);
		if ($query->num_rows() == 1) return $query->row();
		return NULL;
	}

	/**
	 * Get users list
	 *
	 * @return	object
	 */
	function get_users($confirm = FALSE, $banned = FALSE)
	{
		$sql_query = "SELECT `t1`.`id`, `t1`.`email`, `t2`.`name` as `user_name`, `t1`.`password`, `t1`.`activated`, `t1`.`banned`, `t2`.*, `t3`.`title` as `group_title`, `t2`.`notice_other_members`, `t2`.`notice_new_auctions` FROM `" . $this->table_name . "` `t1`, `" . $this->profile_table_name . "` `t2`, `" . $this->group_table_name . "` `t3` WHERE `t1`.`id` = `t2`.`user_id` AND `t2`.`group_id` = `t3`.`id` " . ($confirm == TRUE ? " AND `t1`.`activated` = 0 ORDER BY `t1`.`created` DESC" : "") . ($banned == TRUE ? " AND `t1`.`banned` = 1 ORDER BY `t1`.`created` DESC" : "");
		$query = $this->db->query($sql_query);
		if ($query->num_rows() > 0) return $query->result_array();
		return NULL;
	}

	/**
	 * Get user data
	 *
	 * @param	string
	 * @return	object
	 */
	function get_user($user_id)
	{
		$sql_query = "SELECT `t1`.`id`, `t1`.`email`, `t2`.`name` as `user_name`, `t1`.`password`, `t1`.`activated`, `t1`.`banned`, `t1`.`ban_reason`, `t2`.*, `t3`.`title` as `group_title`, `t3`.`id` as `group_id` FROM `" . $this->table_name . "` `t1`, `" . $this->profile_table_name . "` `t2`, `" . $this->group_table_name . "` `t3` WHERE `t1`.`id` = `t2`.`user_id` AND `t2`.`group_id` = `t3`.`id` AND `t1`.`id` = " . (int)$user_id . " ORDER BY `t1`.`id` ASC LIMIT 1";
		$query = $this->db->query($sql_query);
		if ($query->num_rows() == 1) return $query->row_array();
		return NULL;
	}

	/**
	 * Check if username available for registering
	 *
	 * @param	string
	 * @return	bool
	 */
	function is_username_available($username)
	{
		$this->db->select('1', FALSE);
		$this->db->where('LOWER(username)=', strtolower($username));

		$query = $this->db->get($this->table_name);
		return $query->num_rows() == 0;
	}

	/**
	 * Check if email available for registering
	 *
	 * @param	string
	 * @return	bool
	 */
	function is_email_available($email)
	{
		$this->db->select('1', FALSE);
		$this->db->where('LOWER(email)=', strtolower($email));
		$this->db->or_where('LOWER(new_email)=', strtolower($email));

		$query = $this->db->get($this->table_name);
		return $query->num_rows() == 0;
	}

	/**
	 * Create new user record
	 *
	 * @param	array
	 * @param	bool
	 * @return	array
	 */
	function create_user($data, $activated = TRUE)
	{
		$data['created'] = date('Y-m-d H:i:s');
        $data['activated'] = 0;
		if ($this->db->insert($this->table_name, $data)) {
			$user_id = $this->db->insert_id();
			//if ($activated)	$this->create_profile($user_id);
			return array('user_id' => $user_id);
		}
		return NULL;
	}

	function create_user_from_admin($data, $activated = TRUE)
	{
		$data['created'] = date('Y-m-d H:i:s');
        $data['activated'] = 1;
		if ($this->db->insert($this->table_name, $data)) {
			$user_id = $this->db->insert_id();
			//if ($activated)	$this->create_profile($user_id);
			return array('user_id' => $user_id);
		}
		return NULL;
	}

	/**
	 * Activate user if activation key is valid.
	 * Can be called for not activated users only.
	 *
	 * @param	int
	 * @param	string
	 * @param	bool
	 * @return	bool
	 */
	function activate_user($user_id, $activation_key, $activate_by_email)
	{
		$this->db->select('1', FALSE);
		$this->db->where('id', $user_id);
		if ($activate_by_email) {
			$this->db->where('new_email_key', $activation_key);
		} else {
			$this->db->where('new_password_key', $activation_key);
		}
		$this->db->where('activated', 0);
		$query = $this->db->get($this->table_name);

		if ($query->num_rows() == 1) {

			$this->db->set('activated', 1);
			$this->db->set('new_email_key', NULL);
			$this->db->where('id', $user_id);
			$this->db->update($this->table_name);

			//$this->create_profile($user_id);
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Purge table of non-activated users
	 *
	 * @param	int
	 * @return	void
	 */
	function purge_na($expire_period = 172800)
	{
		$this->db->where('activated', 0);
		$this->db->where('UNIX_TIMESTAMP(created) <', time() - $expire_period);
		$this->db->delete($this->table_name);
	}

	/**
	 * Delete user record
	 *
	 * @param	int
	 * @return	bool
	 */
	function delete_user($user_id)
	{
		$this->db->where('id', $user_id);
		$this->db->delete($this->table_name);
		if ($this->db->affected_rows() > 0) {
			$this->delete_profile($user_id);
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Set new password key for user.
	 * This key can be used for authentication when resetting user's password.
	 *
	 * @param	int
	 * @param	string
	 * @return	bool
	 */
	function set_password_key($user_id, $new_pass_key)
	{
		$this->db->set('new_password_key', $new_pass_key);
		$this->db->set('new_password_requested', date('Y-m-d H:i:s'));
		$this->db->where('id', $user_id);

		$this->db->update($this->table_name);
		return $this->db->affected_rows() > 0;
	}

	/**
	 * Check if given password key is valid and user is authenticated.
	 *
	 * @param	int
	 * @param	string
	 * @param	int
	 * @return	void
	 */
	function can_reset_password($user_id, $new_pass_key, $expire_period = 900)
	{
		$this->db->select('1', FALSE);
		$this->db->where('id', $user_id);
		$this->db->where('new_password_key', $new_pass_key);
		$this->db->where('UNIX_TIMESTAMP(new_password_requested) >', time() - $expire_period);

		$query = $this->db->get($this->table_name);
		return $query->num_rows() == 1;
	}

	/**
	 * Change user password if password key is valid and user is authenticated.
	 *
	 * @param	int
	 * @param	string
	 * @param	string
	 * @param	int
	 * @return	bool
	 */
	function reset_password($user_id, $new_pass, $new_pass_key, $expire_period = 900)
	{
		$this->db->set('password', $new_pass);
		$this->db->set('new_password_key', NULL);
		$this->db->set('new_password_requested', NULL);
		$this->db->where('id', $user_id);
		$this->db->where('new_password_key', $new_pass_key);
		$this->db->where('UNIX_TIMESTAMP(new_password_requested) >=', time() - $expire_period);

		$this->db->update($this->table_name);
		return $this->db->affected_rows() > 0;
	}

	/**
	 * Change user password
	 *
	 * @param	int
	 * @param	string
	 * @return	bool
	 */
	function change_password($user_id, $new_pass)
	{
		$this->db->set('password', $new_pass);
		$this->db->where('id', $user_id);

		$this->db->update($this->table_name);
		return $this->db->affected_rows() > 0;
	}

	/**
	 * Set new email for user (may be activated or not).
	 * The new email cannot be used for login or notification before it is activated.
	 *
	 * @param	int
	 * @param	string
	 * @param	string
	 * @param	bool
	 * @return	bool
	 */
	function set_new_email($user_id, $new_email, $new_email_key, $activated)
	{
		$this->db->set($activated ? 'new_email' : 'email', $new_email);
		$this->db->set('new_email_key', $new_email_key);
		$this->db->where('id', $user_id);
		$this->db->where('activated', $activated ? 1 : 0);

		$this->db->update($this->table_name);
		return $this->db->affected_rows() > 0;
	}

	/**
	 * Activate new email (replace old email with new one) if activation key is valid.
	 *
	 * @param	int
	 * @param	string
	 * @return	bool
	 */
	function activate_new_email($user_id, $new_email_key)
	{
		$this->db->set('email', 'new_email', FALSE);
		$this->db->set('new_email', NULL);
		$this->db->set('new_email_key', NULL);
		$this->db->where('id', $user_id);
		$this->db->where('new_email_key', $new_email_key);

		$this->db->update($this->table_name);
		return $this->db->affected_rows() > 0;
	}

	/**
	 * Update user login info, such as IP-address or login time, and
	 * clear previously generated (but not activated) passwords.
	 *
	 * @param	int
	 * @param	bool
	 * @param	bool
	 * @return	void
	 */
	function update_login_info($user_id, $record_ip, $record_time)
	{
		$this->db->set('new_password_key', NULL);
		$this->db->set('new_password_requested', NULL);

		if ($record_ip)		$this->db->set('last_ip', $this->input->ip_address());
		if ($record_time)	$this->db->set('last_login', date('Y-m-d H:i:s'));

		$this->db->where('id', $user_id);
		$this->db->update($this->table_name);
	}

	/**
	 * Ban user
	 *
	 * @param	int
	 * @param	string
	 * @return	void
	 */
	function ban_user($user_id, $reason = NULL)
	{
		$this->db->where('id', $user_id);
		$this->db->update($this->table_name, array(
			'banned'		=> 1,
			'ban_reason'	=> $reason,
		));
	}

	/**
	 * Unban user
	 *
	 * @param	int
	 * @return	void
	 */
	function unban_user($user_id)
	{
		$this->db->where('id', $user_id);
		$this->db->update($this->table_name, array(
			'banned'		=> 0,
			'ban_reason'	=> NULL,
		));
	}

	/**
	 * Create an empty profile for a new user
	 *
	 * @param	array
	 * @return	bool
	 */
	function create_profile($data)
	{
		//$this->db->set('user_id', $user_id);
		return $this->db->insert($this->profile_table_name, $data);
	}

	/**
	 * Update profile for user
	 *
	 * @param	int
	 * @param	array
	 * @return	bool
	 */
	function update_profile($user_id, $data)
	{
		$this->db->where('user_id', $user_id);
		return $this->db->update($this->profile_table_name, $data);
	}

	/**
	 * Delete user profile
	 *
	 * @param	int
	 * @return	void
	 */
	private function delete_profile($user_id)
	{
		$this->db->where('user_id', $user_id);
		$this->db->delete($this->profile_table_name);
	}

	/**
	 * Update data user
	 *
	 * @param	int
	 * @param	array
	 * @return	bool
	 */
	function update_user($user_id, $data)
	{
		$this->db->where('id', $user_id);
		return $this->db->update($this->table_name, $data);
	}
    function get_all_tender_tags($order = 'caption')
    {
        $sql = "SELECT * FROM `tags` ORDER BY `".$order."` ASC";

        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) return $query->result_array();
        return NULL;
    }
    function get_tags_by_user_id($id)
    {
        $sql = "SELECT * 
                FROM `users_tags`
                WHERE `user_id` = ".$id;
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0)
            return $query->result_array();

        return NULL;
    }
    function users_list_newtender($tender_id)
    {
        $tender_tags = array();
        $in_array = '';
        $sql_in = '';
        $tags = $this->tenders->get_tenders_tags($tender_id);
        if ($tags != null) {
            foreach ($tags as $one) {
                $tender_tags[] = $one['tag_id'];
            }
            $in_array = implode(',', $tender_tags);
            $sql_in = 'OR ut.tag_id IN ( '.$in_array.' )';
        }

        $users = $this->tenders->get_tenders_users($tender_id);
        $users_filter = '';
        if (count($users)) {
            $users_ids = array_map(function ($item) {
                return $item['user_id'];
            }, $users);
            $users_filter = ' AND u.id IN ( '.implode(', ', $users_ids).' )';
        }

        $sql_query = "SELECT u.id, u.email, up.name as user_name, u.password, u.activated, up.*, up.notice_other_members, up.notice_new_auctions
                      FROM users u
                      INNER JOIN user_profiles up ON u.id = up.user_id 
                      LEFT JOIN users_tags ut ON u.id = ut.user_id
                      WHERE u.activated = 1 AND 
                            up.notice_disable = 0 AND 
                            (up.select_all_tags = 1 ".$sql_in." )
                            ".$users_filter."
                      GROUP BY u.id";
        $query = $this->db->query($sql_query);
        if ($query->num_rows() > 0) return $query->result_array();
        return NULL;
    }
    function users_list_updatetender($tender_id)
    {
        $tender_stakes = $this->tenders->get_tenders_lotes_by_user($tender_id);

        $tender_users = array();
        $in_array = '';
        if ($tender_stakes != null) {
            foreach ($tender_stakes as $id => $stakes) {
                $tender_users[] = $id;
            }
            $in_array = implode(',', $tender_users);
        }

        $users = $this->tenders->get_tenders_users($tender_id);
        $users_filter = '';
        if (count($users)) {
            $users_ids = array_map(function ($item) {
                return $item['user_id'];
            }, $users);
            $users_filter = ' AND u.id IN ( '.implode(', ', $users_ids).' )';
        }

        $sql_query = "SELECT u.id, u.email, up.name as user_name, u.password, u.activated, up.*, up.notice_other_members, up.notice_new_auctions
                      FROM users u
                      INNER JOIN user_profiles up ON u.id = up.user_id                       
                      WHERE u.activated = 1 AND 
                            up.notice_disable = 0 AND
                            up.notice_other_members = 1 AND 
                            u.id IN ( ".$in_array." )"
                            . $users_filter;

        $query = $this->db->query($sql_query);
        if ($query->num_rows() > 0) return $query->result_array();
        return NULL;
    }

    function users_list_updatetender_it($tender_id)
    {
        $tender_stakes = $this->tenders->get_tenders_lotes_by_user($tender_id);
        
        $tender_users = array();
        $in_array = '';
        if ($tender_stakes != null) {
            foreach ($tender_stakes as $id => $stakes) {
                $tender_users[] = $id;
            }
            $in_array = implode(',', $tender_users);
        }

        $users = $this->tenders->get_tenders_users($tender_id);
        $users_filter = '';
        if (count($users)) {
            $users_ids = array_map(function ($item) {
                return $item['user_id'];
            }, $users);
            $users_filter = ' AND u.id IN ( '.implode(', ', $users_ids).' )';
        }

        $sql_query = "SELECT u.id, u.email, up.name as user_name
                      FROM users u
                      INNER JOIN user_profiles up ON u.id = up.user_id                       
                      WHERE u.activated = 1". $users_filter;

        $query = $this->db->query($sql_query);

        if ($query->num_rows() > 0) return $query->result_array();
        return NULL;
    }
}

/* End of file users.php */
/* Location: ./application/models/auth/users.php */