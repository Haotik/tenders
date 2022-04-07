<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Tenders
 *
 */
class Tenders_data extends CI_Model
{
    private $table_name = 'tenders';                    // tenders main
    private $documents_table_name = 'tenders_documents';            // tenders documents
    private $lotes_table_name = 'tenders_lotes';                // tenders lotes
    private $options_table_name = 'tenders_options';            // tenders options
    private $results_table_name = 'tenders_results';            // tenders results
    private $results_options_table_name = 'tenders_results_options';    // tenders results options
    private $results_lotes_table_name = 'tenders_results_lotes';        // tenders results lotes
    private $results_lotes_history_table_name = 'tenders_results_lotes_history';        // tenders results lotes
    private $comments_table_name = 'comments';                    // comments

    function __construct()
    {

        parent::__construct();
    }

    function get_tenders_by_id($tender_id, $user_id = 0)
    {
        $this->db->where('id', $tender_id);
        if ($user_id > 0)
            $this->db->where('user_id', (int)$user_id);

        $query = $this->db->get($this->table_name);
        if ($query->num_rows() == 1) return $query->row_array();
        return NULL;
    }

    function get_last_tender_id()
    {
        $this->db->select_max('id');

        $query = $this->db->get($this->table_name);
        if ($query->num_rows() == 1) return $query->row_array();
        return NULL;
    }

    function get_tenders_by_status($status = 1, $user_id = 0, $tag = null, $params = array())
    {
        $join = '';
        $where = '';
        if ($tag != null && $tag != '' && is_numeric($tag)) {
            $join .= 'INNER JOIN `tenders_tags` as `tt`
                ON `t1`.`id` = `tt`.`tender_id`';
            $where .= ' AND `tt`.`tag_id` = ' . $tag;
        }
        if (!empty($params['date_from']) && !empty($params['date_to'])) {
            $where .= " AND `t1`.`end_date` BETWEEN '{$params['date_from']}' AND '{$params['date_to']}'";
        }
        $sql = "SELECT `t1`.*, `t2`.`name` as `author_name` 
                FROM  `user_profiles` `t2`, `" . $this->table_name . "` `t1`
                " . $join . "
		        WHERE `t1`.`status` = " . (int)$status . ($user_id > 0 ? " AND `t1`.`user_id` = " . (int)$user_id : "") . " AND `t1`.`user_id` = `t2`.`user_id`
		        " . $where . " 
		        ORDER BY `t1`.`end_date` DESC";

        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) return $query->result_array();
        return NULL;
    }

    function get_tenders_by_archive($user_id = 0, $tag = null, $params = array())
    {
        $join = '';
        $where = '';
        if ($tag != null && $tag != '' && is_numeric($tag)) {
            $join .= 'INNER JOIN `tenders_tags` as `tt`
                ON `t1`.`id` = `tt`.`tender_id`';
            $where .= ' AND `tt`.`tag_id` = ' . $tag;
        }
        if (!empty($params['date_from']) && !empty($params['date_to'])) {
            $where .= " AND `t1`.`end_date` BETWEEN '{$params['date_from']}' AND '{$params['date_to']}'";
        }
        $sql = "SELECT `t1`.*, `t2`.`name` as `author_name` 
                FROM `user_profiles` `t2`, `" . $this->table_name . "` `t1`
                " . $join . " 
                WHERE `t1`.`in_history` = 1 AND `t1`.`status` = 3 " . ($user_id > 0 ? " AND `t1`.`user_id` = " . (int)$user_id : "") . " AND `t1`.`user_id` = `t2`.`user_id`
                " . $where . "  
                ORDER BY `t1`.`end_date` DESC";

        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) return $query->result_array();
        return NULL;
    }

    function get_cnt_lotes()
    {
        $new_cnt = array();
        $sql = "SELECT COUNT(`id`) as `cnt`, `tender_id` FROM `" . $this->lotes_table_name . "` GROUP BY `tender_id`";

        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $key => $value) {
                $new_cnt[(int)$value['tender_id']] = $value['cnt'];
            }
            return $new_cnt;
        }
        return NULL;
    }

    function get_cnt_results()
    {
        $new_cnt = array();
        $sql = "SELECT COUNT(`id`) as `cnt`, `tender_id` FROM `" . $this->results_table_name . "` GROUP BY `tender_id`";

        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $key => $value) {
                $new_cnt[(int)$value['tender_id']] = $value['cnt'];
            }
            return $new_cnt;
        }
        return NULL;
    }

    function get_best_summa()
    {
        $new_cnt = array();
        $sql = "SELECT MIN(`total_sum`) as `best_summa`, `tender_id` FROM `" . $this->results_table_name . "` GROUP BY `tender_id`";

        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $key => $value) {
                $new_cnt[(int)$value['tender_id']] = $value['best_summa'];
            }
            return $new_cnt;
        }
        return NULL;
    }

    function create_tender($data)
    {
        return $this->db->insert($this->table_name, $data);
    }

    function update_tender($data, $tender_id)
    {
        $this->db->where('id', $tender_id);
        return $this->db->update($this->table_name, $data);
    }

    function delete_tender($tender_id)
    {
        $this->db->where('id', $tender_id);
        $this->db->delete($this->table_name);
        if ($this->db->affected_rows() > 0) {
            $this->delete_options($tender_id);
            $this->delete_lotes($tender_id);
            $this->set_tenders_reset($tender_id);

            if (is_dir($_SERVER['DOCUMENT_ROOT'] . '/upload_tender/files/' . $tender_id))
                @unlink($_SERVER['DOCUMENT_ROOT'] . '/upload_tender/files/' . $tender_id);

            return TRUE;
        }
        return FALSE;
    }

    function archive_tender($tender_id)
    {
        $data = array(
            'in_history' => 1,
            'status' => 3
        );
        $this->db->where('id', $tender_id);
        $this->db->update($this->table_name, $data);
        if ($this->db->affected_rows() > 0)
            return TRUE;
        else
            return FALSE;
    }

    function get_tenders_options($tender_id)
    {
        $this->db->where('tender_id', (int)$tender_id);
        $this->db->order_by('id', 'ASC');

        $query = $this->db->get($this->options_table_name);
        if ($query->num_rows() > 0) return $query->result_array();
        return NULL;
    }

    function set_tenders_options($data, $tender_id, $user_id)
    {
        // Удаляем старые значения допусловий
        $this->db->where('tender_id', (int)$tender_id);
        $this->db->where('user_id', (int)$user_id);
        $this->db->delete($this->results_options_table_name);

        $i = 0;
        $data_arr = array();
        foreach ($data as $k => $v) {
            $data_arr[$i]['option_id'] = (int)$k;
            $data_arr[$i]['user_id'] = (int)$user_id;
            $data_arr[$i]['created'] = date("Y-m-d H:i:s");
            $data_arr[$i]['tender_id'] = $tender_id;
            $data_arr[$i]['value'] = $v;
            $i++;
        }
        return $this->db->insert_batch($this->results_options_table_name, $data_arr);
    }

    function get_tenders_options_by_user($tender_id, $user_id = 0)
    {
        $this->db->where('tender_id', (int)$tender_id);
        if ($user_id > 0)
            $this->db->where('user_id', (int)$user_id);
        $this->db->order_by('option_id', 'ASC');

        $query = $this->db->get($this->results_options_table_name);
        if ($query->num_rows() > 0) {
            $rows = array();
            if ($user_id > 0) {
                foreach ($query->result_array() as $key => $value) {
                    $rows[(int)$value['option_id']] = $value['value'];
                }
            } else {
                foreach ($query->result_array() as $key => $value) {
                    $rows[(int)$value['user_id']][(int)$value['option_id']] = $value['value'];
                }
            }
            return $rows;
        }
        return NULL;
    }

    function get_options_by_params($name_field, $type_field, $tender_id)
    {
        $this->db->where('name_field', $name_field);
        $this->db->where('type_field', ($type_field == "Строка" ? 0 : ($type_field == "Число" ? 1 : 2)));
        $this->db->where('tender_id', (int)$tender_id);

        $query = $this->db->get($this->options_table_name);
        if ($query->num_rows() == 1) return $query->row_array();
        return NULL;
    }

    function add_options($data, $tender_id, $is_edit = FALSE)
    {
        if ($is_edit == FALSE) {
            foreach ($data as $k => $v) {

//				$data[$k]['created'] = date("Y-m-d H:i:s");
//				$data[$k]['tender_id'] = $tender_id;
//				$data[$k]['type_field'] = ($v['type_field'] == "Строка" ? 0 : ($v['type_field'] == "Число" ? 1 : 2) );

                $this->db->insert($this->options_table_name, array(
                    'created' => date("Y-m-d H:i:s"),
                    'tender_id' => (int)$tender_id,
                    'type_field' => ($v['type_field'] == "Строка" ? 0 : ($v['type_field'] == "Число" ? 1 : 2)),
                    'name_field' => $v['name_field']));
            }
//			return $this->db->insert_batch($this->options_table_name, $data);
            return TRUE;
        } else {
            foreach ($data as $k => $v) {
                if (!$this->get_options_by_params($v['name_field'], $v['type_field'], $tender_id)) {
                    $this->db->insert($this->options_table_name, array('created' => date("Y-m-d H:i:s"), 'tender_id' => (int)$tender_id, 'type_field' => ($v['type_field'] == "Строка" ? 0 : ($v['type_field'] == "Число" ? 1 : 2)), 'name_field' => $v['name_field']));
                }
            }
            return TRUE;
        }
    }

    function delete_options($tender_id)
    {
        $this->db->where('tender_id', $tender_id);
        $this->db->delete($this->options_table_name);
        if ($this->db->affected_rows() > 0) {
            return TRUE;
        }
        return FALSE;
    }

    function delete_option($row_id)
    {
        $this->db->where('id', (int)$row_id);
        $this->db->delete($this->options_table_name);
        if ($this->db->affected_rows() > 0) {
            return TRUE;
        }
        return FALSE;
    }

    function delete_results_options($tender_id, $user_id = 0)
    {
        $this->db->where('tender_id', $tender_id);
        if ($user_id > 0)
            $this->db->where('user_id', (int)$user_id);
        $this->db->delete($this->results_options_table_name);
        if ($this->db->affected_rows() > 0) {
            return TRUE;
        }
        return FALSE;
    }

    function get_tenders_lotes($tender_id)
    {
        $this->db->where('tender_id', (int)$tender_id);
        $this->db->order_by('id', 'ASC');

        $query = $this->db->get($this->lotes_table_name);
        if ($query->num_rows() > 0) return $query->result_array();
        return NULL;
    }

    function get_tender_results_lotes_best_min($tender_id)
    {
        $new_cnt = array();
        $arr_lote_id = "";
        $arr_best_value = array();
        $sql = "SELECT MIN(`t1`.`value`) as `best_value`, `t1`.`lote_id` FROM `" . $this->results_lotes_table_name . "` `t1` WHERE `t1`.`tender_id` = " . (int)$tender_id . " GROUP BY `t1`.`lote_id`";

        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $key => $value) {
                $arr_best_value[$value['lote_id']] = $value['best_value'];
                $arr_lote_id .= $value['lote_id'] . ",";
            }
            $query->free_result();
            $arr_lote_id = substr($arr_lote_id, 0, -1);

            $sql = "SELECT `t1`.`value`, `t1`.`lote_id`, `t2`.`name` as `res_name` FROM `" . $this->results_lotes_table_name . "` `t1`, `user_profiles` `t2` WHERE `t1`.`user_id` = `t2`.`user_id` AND `t1`.`tender_id` = " . (int)$tender_id . " AND `t1`.`lote_id` IN (" . $arr_lote_id . ") ORDER BY `t1`.`created` DESC, `t1`.`value` ASC";

            $query = $this->db->query($sql);
            if ($query->num_rows() > 0) {
                foreach ($query->result_array() as $key => $value) {
                    if (empty($new_cnt[(int)$value['lote_id']]) && $arr_best_value[$value['lote_id']] == $value['value']) {
                        $new_cnt[(int)$value['lote_id']]['best_value'] = $value['value'];
                        $new_cnt[(int)$value['lote_id']]['name'] = $value['res_name'];
                    }
                }
                return $new_cnt;
            }
        }
        return NULL;
    }

    function get_tender_results_lotes_best_max($tender_id)
    {
        $new_cnt = array();
        $arr_lote_id = "";
        $arr_best_value = array();
        $sql = "SELECT MAX(`t1`.`value`) as `best_value`, `t1`.`lote_id` FROM `" . $this->results_lotes_table_name . "` `t1` WHERE `t1`.`tender_id` = " . (int)$tender_id . " GROUP BY `t1`.`lote_id`";

        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $key => $value) {
                $arr_best_value[$value['lote_id']] = $value['best_value'];
                $arr_lote_id .= $value['lote_id'] . ",";
            }
            $query->free_result();
            $arr_lote_id = substr($arr_lote_id, 0, -1);

            $sql = "SELECT `t1`.`value`, `t1`.`lote_id`, `t2`.`name` as `res_name` FROM `" . $this->results_lotes_table_name . "` `t1`, `user_profiles` `t2` WHERE `t1`.`user_id` = `t2`.`user_id` AND `t1`.`tender_id` = " . (int)$tender_id . " AND `t1`.`lote_id` IN (" . $arr_lote_id . ") ORDER BY `t1`.`created` DESC, `t1`.`value` ASC";

            $query = $this->db->query($sql);
            if ($query->num_rows() > 0) {
                foreach ($query->result_array() as $key => $value) {
                    if (empty($new_cnt[(int)$value['lote_id']]) && $arr_best_value[$value['lote_id']] == $value['value']) {
                        $new_cnt[(int)$value['lote_id']]['best_value'] = $value['value'];
                        $new_cnt[(int)$value['lote_id']]['name'] = $value['res_name'];
                    }
                }
                return $new_cnt;
            }
        }
        return NULL;
    }

    function get_tender_results_lotes_expensive($tender_id)
    {
        $new_cnt = array();
        $arr_lote_id = $arr_expensive_value = "";
        $sql = "SELECT MAX(`t1`.`value`) as `expensive_value`, `t1`.`lote_id` FROM `" . $this->results_lotes_table_name . "` `t1` WHERE `t1`.`tender_id` = " . (int)$tender_id . " GROUP BY `t1`.`lote_id`";

        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $key => $value) {
                $arr_expensive_value .= "'" . $value['expensive_value'] . "',";
                $arr_lote_id .= $value['lote_id'] . ",";
            }
            $query->free_result();
            $arr_expensive_value = substr($arr_expensive_value, 0, -1);
            $arr_lote_id = substr($arr_lote_id, 0, -1);

            $sql = "SELECT `t1`.`value`, `t1`.`lote_id`, `t2`.`name` as `res_name` FROM `" . $this->results_lotes_table_name . "` `t1`, `user_profiles` `t2` WHERE `t1`.`user_id` = `t2`.`user_id` AND `t1`.`tender_id` = " . (int)$tender_id . " AND `t1`.`lote_id` IN (" . $arr_lote_id . ") AND `t1`.`value` IN (" . $arr_expensive_value . ") ORDER BY `created` DESC";
            $query = $this->db->query($sql);
            if ($query->num_rows() > 0) {
                foreach ($query->result_array() as $key => $value) {
                    $new_cnt[(int)$value['lote_id']]['expensive_value'] = $value['value'];
                    $new_cnt[(int)$value['lote_id']]['name'] = $value['res_name'];
                }
                return $new_cnt;
            }
        }
        return NULL;
    }

    function set_tenders_lotes($data, $tender_id, $user_id)
    {
        $i = 0;
        $data_arr = array();
        foreach ($data as $k => $v) {
            if (!empty($v)) {
                // Удаляем старое значение лота
                $this->db->where('lote_id', (int)$k);
                $this->db->where('tender_id', (int)$tender_id);
                $this->db->where('user_id', (int)$user_id);
                $this->db->delete($this->results_lotes_table_name);

                $data_arr[$i]['lote_id'] = (int)$k;
                $data_arr[$i]['user_id'] = (int)$user_id;
                $data_arr[$i]['created'] = date("Y-m-d H:i:s");
                $data_arr[$i]['tender_id'] = $tender_id;
                $data_arr[$i]['value'] = $v;
                //$data_arr[$i]['seller_name'] = $v["seller_name"];
                $i++;
            }
        }
        if (!empty($data_arr)) {
//var_dump($data_arr); exit;
            $this->db->insert_batch($this->results_lotes_table_name, $data_arr);
            $this->db->insert_batch($this->results_lotes_history_table_name, $data_arr);
        }

        return TRUE;
    }

    function get_tenders_lotes_by_user($tender_id, $user_id = 0)
    {
        $this->db->where('tender_id', (int)$tender_id);
        if (!empty($user_id))
            $this->db->where('user_id', (int)$user_id);
        $this->db->order_by('lote_id', 'ASC');

        $query = $this->db->get($this->results_lotes_table_name);

        if ($query->num_rows() > 0) {
            $rows = array();
            foreach ($query->result_array() as $key => $value) {
                if (!empty($user_id))
                    $rows[(int)$value['lote_id']] = $value['value'];
                else
                    $rows[(int)$value['user_id']][(int)$value['lote_id']] = $value['value'];
            }
            return $rows;
        }
        return NULL;
    }

    function get_lotes_by_id($id)
    {
        $this->db->where('id', (int)$id);

        $query = $this->db->get($this->lotes_table_name);
        if ($query->num_rows() == 1) return $query->row_array();
        return NULL;
    }

    function get_lotes_by_params($name, $unit, $need, $start_sum, $step_lot = 0, $tender_id,  $product_link = '')
    {
        $this->db->where('name', $name);
        $this->db->where('unit', $unit);
        $this->db->where('need', $need, FALSE);
        $this->db->where('start_sum', $start_sum, FALSE);
        if (!empty($step_lot))
            $this->db->where('step_lot', $step_lot, FALSE);
        $this->db->where('tender_id', (int)$tender_id);
        $this->db->where('product_link', $product_link);

        $query = $this->db->get($this->lotes_table_name);
        if ($query->num_rows() > 0) return $query->row_array();
        return NULL;
    }

    function add_lotes($data, $tender_id, $is_edit = FALSE)
    {
        if ($is_edit == FALSE) {
            foreach ($data as $k => $v) {
//				$data[$k]['created'] = date("Y-m-d H:i:s");
//				$data[$k]['tender_id'] = $tender_id;

                $this->db->insert($this->lotes_table_name, array(
                    'created' => date("Y-m-d H:i:s"),
                    'tender_id' => (int)$tender_id, 
                    'name' => $v['name'], 
                    'unit' => $v['unit'], 
                    'need' => $v['need'], 
                    'start_sum' => (!empty($v['start_sum']) ? $v['start_sum'] : "0.00"), 
                    'step_lot' => (!empty($v['step_lot']) ? $v['step_lot'] : ""),
                    'product_link' => (!empty($v['product_link']) ? $v['product_link'] : "")
                ));
            }
            return TRUE;
        } else {
            foreach ($data as $k => $v) {
                $res_lot = $this->get_lotes_by_params($v['name'], $v['unit'], $v['need'], $v['start_sum'], $v['step_lot'], $tender_id, $v['product_link']);
                if (empty($res_lot)) {
                    $this->db->insert($this->lotes_table_name, array(
                        'created' => date("Y-m-d H:i:s"), 
                        'tender_id' => (int)$tender_id, 
                        'name' => $v['name'], 
                        'unit' => $v['unit'], 
                        'need' => $v['need'], 
                        'start_sum' => (!empty($v['start_sum']) ? $v['start_sum'] : "0.00"), 
                        'step_lot' => (!empty($v['step_lot']) ? $v['step_lot'] : "0.00"),
                        'product_link' => (!empty($v['product_link']) ? $v['product_link'] : "")
                    ));
                }
            }
            return TRUE;
        }
    }

    function delete_lotes($tender_id)
    {
        $this->db->where('tender_id', $tender_id);
        $this->db->delete($this->lotes_table_name);
        if ($this->db->affected_rows() > 0) {
            return TRUE;
        }
        return FALSE;
    }

    function delete_lote($row_id)
    {
        $this->db->where('id', (int)$row_id);
        $this->db->delete($this->lotes_table_name);
        if ($this->db->affected_rows() > 0) {
            return TRUE;
        }
        return FALSE;
    }

    function clear_step_lotes($tender_id)
    {
        $this->db->where('tender_id', (int)$tender_id);
        $this->db->update($this->lotes_table_name, array('step_lot' => "0.00"));
        if ($this->db->affected_rows() > 0)
            return TRUE;
        else
            return FALSE;
    }

    function reset_lot($row_id, $user_id, $tender_id)
    {
        // Удаляем последнюю ставку
        $this->db->where('lote_id', (int)$row_id);
        $this->db->where('tender_id', (int)$tender_id);
        $this->db->where('user_id', (int)$user_id);
        $this->db->where('is_deleted', (int)0);
        $this->db->order_by('created', 'DESC');
        $this->db->limit(1);
        $lot_to_reset = $this->db->get($this->results_lotes_history_table_name)->row_array();
        if (!empty($lot_to_reset['value'])) {
            $author_id = $this->tank_auth->get_user_id();
            $author = $this->tank_auth->user($author_id);
            $now = date("H:i:s в d.m.Y");
            $comment = "Ставка была удалена пользователем \"{$author["user_name"]}\" {$now}";
            $this->db->where(array(
                'lote_id' => $lot_to_reset['lote_id'],
                'tender_id' => $lot_to_reset['tender_id'],
                'user_id' => $lot_to_reset['user_id'],
                'value' => $lot_to_reset['value'],
            ))
                ->update($this->results_lotes_history_table_name, array(
                    'is_deleted' => 1,
                    'comment' => $comment,
                ));
//                ->delete($this->results_lotes_history_table_name);
        }

        if ($this->db->affected_rows() > 0) {
            // Определяем "новую" последнюю ставку
            $this->db->where('lote_id', (int)$row_id);
            $this->db->where('tender_id', (int)$tender_id);
            $this->db->where('user_id', (int)$user_id);
            $this->db->where('is_deleted', (int)0);
            $this->db->order_by('created', 'DESC');
            $this->db->limit(1);
            $query = $this->db->get($this->results_lotes_history_table_name);
            if ($query->num_rows() == 1) $last_lot = $query->row_array();

            // Записываем в основную таблицу лотов
            if (!empty($last_lot['value'])) {
                $this->db->where('lote_id', (int)$row_id);
                $this->db->where('tender_id', (int)$tender_id);
                $this->db->where('user_id', (int)$user_id);
                $this->db->update($this->results_lotes_table_name, array('created' => date("Y-m-d H:i:s"), 'value' => (!empty($last_lot['value']) ? $last_lot['value'] : "0.00")));
                if ($this->db->affected_rows() > 0) {
                    // Считаем результаты
                    $this->set_tenders_results((int)$tender_id, (int)$user_id);

                    // Определяем победителя
                    $this->set_tenders_leader((int)$tender_id);

                    return TRUE;
                } else
                    return FALSE;
            }

            // Проверяем общее количество заявок в истории
            $cnt_lot = 0;
            $this->db->where('tender_id', (int)$tender_id);
            $this->db->where('user_id', (int)$user_id);
            $this->db->where('is_deleted', (int)0);
            $query = $this->db->get($this->results_lotes_history_table_name);
            $cnt_lot = $query->num_rows();

            if ($cnt_lot == 0) {
                // Все ставки пользователя удалены, значит удаляем его всю историю участия
                $this->delete_results_options($tender_id, $user_id);

                // Удаляем пользователя из результатов
                $this->db->where('tender_id', (int)$tender_id);
                $this->db->where('user_id', (int)$user_id);
                $this->db->delete($this->results_table_name);

                $this->db->where('tender_id', (int)$tender_id);
                $this->db->where('user_id', (int)$user_id);
                $this->db->delete($this->results_lotes_table_name);

            }

            return TRUE;
        }
        return FALSE;
    }

    function get_tenders_documents($tender_id)
    {
        $this->db->where('tender_id', (int)$tender_id);
        $this->db->where('type !=', 'completed_protocol');
        $this->db->order_by('created', 'ASC');

        $query = $this->db->get($this->documents_table_name);
        if ($query->num_rows() > 0) return $query->result_array();
        return NULL;
    }

    function set_tenders_documents($tender_id, $filename, $filesize)
    {
        $this->db->where('tender_id', (int)$tender_id);
        $this->db->where('type', 'completed_protocol');
        $this->db->delete('tenders_documents');

        $data_arr = array();
        $data_arr['tender_id'] = (int)$tender_id;
        $data_arr['created'] = date("Y-m-d H:i:s");
        $data_arr['filename'] = $filename;
        $data_arr['filesize'] = $filesize;
        $data_arr['type'] = 'completed_protocol';
        return $this->db->insert('tenders_documents', $data_arr);
    }

    function get_tenders_completed_protocol_documents($tender_id)
    {
        $this->db->where('tender_id', (int)$tender_id);
        $this->db->where('type', 'completed_protocol');
        $this->db->order_by('created', 'ASC');

        $query = $this->db->get($this->documents_table_name);
        if ($query->num_rows() > 0) return $query->result_array();
        return NULL;
    }

    function get_tenders_results($tender_id)
    {
        $sql = "SELECT `t1`.`user_id`, `t1`.`total_sum`, `t1`.`leader`, `t2`.`email`, `t3`.`name` as `res_name`, `t3`.`phone`, `t3`.`director_name`, `t3`.`inn` FROM `" . $this->results_table_name . "` `t1`, `users` `t2`, `user_profiles` `t3` WHERE `t1`.`tender_id` = " . (int)$tender_id . " AND `t1`.`user_id` = `t2`.`id` AND `t1`.`user_id` = `t3`.`user_id` ORDER BY `t1`.`created` DESC";

        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) return $query->result_array();
        return NULL;
    }

    function set_tenders_results($tender_id, $user_id)
    {
        // Удаляем старые значения результатов
        $this->db->where('tender_id', (int)$tender_id);
        $this->db->where('user_id', (int)$user_id);
        $this->db->delete($this->results_table_name);

        $list_lotes = $this->get_tenders_lotes_by_user($tender_id, $user_id);
        $lotes = $this->get_tenders_lotes($tender_id);

        $data_lotes = array();
        if (!empty($lotes)) {
            foreach ($lotes as $l) {
                $data_lotes[$l['id']] = $l['need'];
            }
        }

        $data_arr = array();
        $data_arr['user_id'] = (int)$user_id;
        $data_arr['created'] = date("Y-m-d H:i:s");
        $data_arr['tender_id'] = $tender_id;
        $data_arr['total_sum'] = "0.00";
        if (!empty($list_lotes)) {
            foreach ($list_lotes as $key => $value) {
                $data_arr['total_sum'] = floatval($data_arr['total_sum']) + (floatval($value) * floatval($data_lotes[$key]));
            }
        }
        return $this->db->insert($this->results_table_name, $data_arr);
    }

    function set_tenders_leader($tender_id)
    {
        $sql = "SELECT MIN(`total_sum`) as `best_summa`, `id` FROM `" . $this->results_table_name . "` WHERE `tender_id` = " . (int)$tender_id;

        $query = $this->db->query($sql);
        if ($query->num_rows() == 1) {
            $row = $query->row_array();
            if ($row['best_summa'] != '0.00') {
                // Сбрасываем победителя
                $sql_clear = "UPDATE `" . $this->results_table_name . "` SET `leader` = 0 WHERE `tender_id` = " . (int)$tender_id;
                $query_clear = $this->db->query($sql_clear);

                $this->db->where('id', $row['id']);
                $this->db->update($this->results_table_name, array('leader' => 1, 'comment' => 'Авто выбор, минимальная цена'));
                if ($this->db->affected_rows() > 0)
                    return TRUE;
                else
                    return FALSE;
            } else
                return NULL;
        }
        return NULL;
    }

    function set_tenders_leader_manual($tender_id, $user_id, $comment = '')
    {
        // Сбрасываем победителя
        $sql_clear = "UPDATE `" . $this->results_table_name . "` SET `leader` = 0 WHERE `tender_id` = " . (int)$tender_id;
        $query_clear = $this->db->query($sql_clear);

        $this->db->where('tender_id', (int)$tender_id);
        $this->db->where('user_id', (int)$user_id);
        $this->db->update($this->results_table_name, array('leader' => 1, 'comment'=> $comment));
        if ($this->db->affected_rows() > 0) {
            // Записываем ID победителя в тендеры
            $this->db->where('id', (int)$tender_id);
            $this->db->update($this->table_name, array('winner' => (int)$user_id , 'winner_reason' => $comment));
            return TRUE;
        } else
            return FALSE;
    }

    function set_tenders_end($tender_id, $user_id = 0)
    {
        $this->db->where('id', (int)$tender_id);
        if ($user_id > 0)
            $this->db->where('user_id', (int)$user_id);
        $this->db->update($this->table_name, array('end_date' => date("Y-m-d H:i:s"), 'status' => 2));
        if ($this->db->affected_rows() > 0)
            return TRUE;
        else
            return FALSE;
    }

    function set_tenders_reset($tender_id)
    {
        // Удаление результатов
        $this->db->where('tender_id', (int)$tender_id);
        $this->db->delete($this->results_table_name);
        if ($this->db->affected_rows() > 0) {
            // Удаление ставок
            $this->db->where('tender_id', (int)$tender_id);
            $this->db->delete($this->results_lotes_table_name);

            // Удаление истории ставок
            $this->db->where('tender_id', (int)$tender_id);
            $this->db->delete($this->results_lotes_history_table_name);

            // Удаление допусловий
            $this->db->where('tender_id', (int)$tender_id);
            $this->db->delete($this->results_options_table_name);

            return TRUE;
        }
        return FALSE;
    }

    function get_results_lotes_history($tender_id)
    {
        $sql = "SELECT `t1`.*, `t2`.`name` as `member_name`, `t3`.`name` as `lote_name` FROM `" . $this->results_lotes_history_table_name . "` `t1`, `user_profiles` `t2`, `" . $this->lotes_table_name . "` `t3` WHERE `t1`.`tender_id` = " . (int)$tender_id . " AND `t1`.`lote_id` = `t3`.`id` AND `t1`.`user_id` = `t2`.`user_id` ORDER BY `t1`.`created` DESC";

        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) return $query->result_array();
        return NULL;
    }

    function get_settings($name = "")
    {
        if (!empty($name))
            $this->db->where('name', $name);
        $query = $this->db->get("settings");
        if (!empty($name) && $query->num_rows() == 1) return $query->row_array();
        if (empty($name) && $query->num_rows() > 0) return $query->result_array();
        return NULL;
    }

    function set_settings($name, $value)
    {
        $this->db->where('name', $name);
        return $this->db->update("settings", array('value' => $value));
    }

    function get_comments($comment_id = 0, $start = 0, $per_page = 1)
    {
        $sql = "SELECT `t1`.*, `t2`.`name` FROM `" . $this->comments_table_name . "` `t1`, `user_profiles` `t2` WHERE `t1`.`user_id` = `t2`.`user_id`" . (!empty($comment_id) ? " AND `t1`.`id` = " . (int)$comment_id : "") . " ORDER BY `t1`.`date_publish` DESC";

        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) return $query->result_array();
        return NULL;
    }

    function get_comments_count()
    {
        $sql = "SELECT COUNT(`id`) as `cnt` FROM `" . $this->comments_table_name . "`";

        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            return $query->num_rows();
        }
        return 0;
    }

    function create_comment($data)
    {
        return $this->db->insert($this->comments_table_name, $data);
    }

    function update_comment($data, $comment_id)
    {
        $this->db->where('id', $comment_id);
        return $this->db->update($this->comments_table_name, $data);
    }

    function delete_comment($comment_id)
    {
        $this->db->where('id', $comment_id);
        $this->db->delete($this->comments_table_name);
        if ($this->db->affected_rows() > 0) {
            return TRUE;
        } else
            return FALSE;
    }

    function get_all_tender_tags($order = 'caption')
    {
        $sql = "SELECT * FROM `tags` ORDER BY `" . $order . "` ASC";

        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) return $query->result_array();
        return NULL;
    }

    function get_all_users($order = 'name')
    {
        $sql = "SELECT up.`user_id`, up.`name`, up.`group_id` FROM `user_profiles` up LEFT JOIN users u ON u.id = up.user_id 
        WHERE u.activated = 1 AND u.banned = 0 
        ORDER BY up.`" . $order . "` ASC";

        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) return $query->result_array();
        return NULL;
    }

    function get_tenders_tags($tender_id)
    {
        $sql = "SELECT * FROM `tenders_tags` WHERE `tender_id` = " . $tender_id;

        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) return $query->result_array();
        return NULL;
    }

    function get_tenders_tags_caption($tender_id)
    {
        $sql = "SELECT `tt`.*, `t`.`caption` 
                FROM `tenders_tags` as `tt` 
                LEFT JOIN `tags` as `t` 
                ON `tt`.`tag_id` = `t`.`id` 
                WHERE `tt`.`tender_id` =" . $tender_id;

        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) return $query->result_array();
        return NULL;
    }

    function get_tenders_users($tender_id)
    {
        $sql = "SELECT * FROM `tender_users` WHERE `tender_id` = " . $tender_id;
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) return $query->result_array();
        return NULL;
    }

    function get_tenders_users_caption($tender_id)
    {
        $sql = "SELECT `tu`.*, `up`.`name` 
                FROM `tender_users` as `tu` 
                LEFT JOIN `user_profiles` as `up` 
                ON `tu`.`user_id` = `up`.`user_id` 
                WHERE `tu`.`tender_id` =" . $tender_id;

        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) return $query->result_array();
        return NULL;
    }

    function get_tenders_tags_by_user_id($id)
    {
        $sql = "";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) return $query->result_array();
        return NULL;
    }


    function get_tender_lot_history($id)
    {

        $sql = "SELECT `trlh`.*, `t`.`title` as lot_name, `up`.`name` as user_name
                FROM `tenders_results_lotes_history` as `trlh` 
                LEFT JOIN `user_profiles` as `up` 
                ON `trlh`.`user_id` = `up`.`user_id` 
                LEFT JOIN `tenders` as `t` 
                ON `trlh`.`tender_id` = `t`.`id`                 
                WHERE `trlh`.`tender_id` = {$id}
                ORDER BY created DESC";

        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) return $query->result_array();

        return null;
    }

    function get_export_tenders($ids)
    {
        $sql = "SELECT *
                FROM `tenders` `t`
                
                WHERE `t`.`id` IN ({$ids})";
        $result = array();
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $tenders = $query->result_array();
            foreach ($tenders as $tender) {
                $result[$tender['id']]['tender'] = $tender;
                $result[$tender['id']]['author'] = $this->tank_auth->user($tender['user_id']);
                $lotes = array();
                if (($q = $this->get_tenders_lotes($tender['id'])) != null) {
                    $lotes = $q;
                }
                $res = array();
                if (($q = $this->get_tenders_results($tender['id'])) != null) {
                    $res = $q;
                }
                $competitors = array();
                if (($q = $this->get_tenders_users_caption($tender['id'])) != null) {
                    $competitors = $q;
                }
                $tags = array();
                if (($q = $this->get_tenders_tags_caption($tender['id'])) != null) {
                    $tags = $q;
                }
                $result[$tender['id']]['lotes'] = $lotes;
                $result[$tender['id']]['results'] = $res;
                $result[$tender['id']]['competitors'] = $competitors;
                $result[$tender['id']]['tags'] = $tags;
            }
            return $result;
        }

        return null;
    }
}

/* End of file tenders_data.php */
/* Location: ./application/models/tenders_data.php */