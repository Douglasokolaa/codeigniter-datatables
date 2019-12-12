<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Datatables_server_side {

	private $table;

	private $primary_key;

	private $columns;

	private $where;

	private $CI;

	private $request;

	public function __construct($params)
	{
		$this->table = (array_key_exists('table', $params) === TRUE && is_string($params['table']) === TRUE) ? $params['table'] : '';
		
		$this->primary_key = (array_key_exists('primary_key', $params) === TRUE && is_string($params['primary_key']) === TRUE) ? $params['primary_key'] : '';
		
		$this->columns = (array_key_exists('columns', $params) === TRUE && is_array($params['columns']) === TRUE) ? $params['columns'] : [];

		$this->where = (array_key_exists('where', $params) === TRUE && (is_array($params['where']) === TRUE || is_string($params['where']) === TRUE)) ? $params['where'] : [];
		
		$this->CI =& get_instance();

		$this->request = $this->CI->input->get();

		$this->validate_table();

		$this->validate_primary_key();

		$this->validate_columns();

		$this->validate_request();
	}

	private function validate_table()
	{
		if ($this->CI->db->table_exists($this->table) === FALSE)
		{
			$this->response(array(
				'error' => 'Table doesn\'t exist.'
			));
		}
	}

	private function validate_primary_key()
	{
		if ($this->CI->db->field_exists($this->primary_key, $this->table) === FALSE)
		{
			$this->response(array(
				'error' => 'Invalid primary key.'
			));
		}
	}

	private function validate_columns()
	{
		foreach ($this->columns as $column)
		{
			if (is_string($column) === FALSE || $this->CI->db->field_exists($column, $this->table) === FALSE)
			{
				$this->response(array(
					'error' => 'Invalid column.'
				));
			}
		}
	}

	private function validate_request()
	{
		if (count($this->request['columns']) !== count($this->columns))
		{
			$this->response(array(
				'error' => 'Column count mismatch.'
			));
		}

		foreach ($this->request['columns'] as $column)
		{
			if (isset($this->columns[$column['data']]) === FALSE)
			{
				$this->response(array(
					'error' => 'Missing column.'
				));
			}

			$this->request['columns'][$column['data']]['name'] = $this->columns[$column['data']];
		}
	}

	private function order()
	{
		foreach ($this->request['order'] as $order)
		{
			$column = $this->request['columns'][$order['column']];

			if ($column['orderable'] === 'true')
			{
				$this->CI->db->order_by($column['name'], strtoupper($order['dir']));
			}
		}
	}

	private function search()
	{
		$search_value = $this->request['search']['value'];

		if (empty($search_value) === FALSE)
		{
			$first_like = TRUE;

			foreach ($this->request['columns'] as $column)
			{
				if ($column['searchable'] === 'true')
				{
					if ($first_like === TRUE)
					{
						$this->CI->db->like($column['name'], $search_value);

						$first_like = FALSE;
					}
					else
					{
						$this->CI->db->or_like($column['name'], $search_value);
					}
				}
			}
		}
	}

	private function where()
	{
		$this->CI->db->where($this->where);
	}

	private function response($data)
	{
		$this->CI->output->set_content_type('application/json');
        $this->CI->output->set_output(json_encode($data));
        $this->CI->output->_display();

        exit;
	}

	private function records_total()
	{
		$this->CI->db->reset_query();

		$this->where();

		$this->CI->db->from($this->table);

		return $this->CI->db->count_all_results();
	}

	private function records_filtered()
	{
		$this->CI->db->reset_query();

		$this->search();

		$this->where();

		$this->CI->db->from($this->table);

		return $this->CI->db->count_all_results();
	}

	public function process($row_id = 'data', $row_class = '')
	{
		if (in_array($row_id, array('id', 'data', 'none'), TRUE) === FALSE)
		{
			$this->response(array(
				'error' => 'Invalid DT_RowId.'
			));
		}

		if (is_string($row_class) === FALSE)
		{
			$this->response(array(
				'error' => 'Invalid DT_RowClass.'
			));
		}

		$columns = array();

		$add_primary_key = TRUE;

		foreach ($this->columns as $column)
		{
			$columns[] = $column;

			if ($column === $this->primary_key)
			{
				$add_primary_key = FALSE;
			}
		}

		if ($add_primary_key === TRUE)
		{
			$columns[] = $this->primary_key;
		}

		$this->CI->db->select(implode(',', $columns));

		$this->order();

		$this->search();

		$this->where();

		$query = $this->CI->db->get($this->table, $this->request['length'], $this->request['start']);

		$data['data'] = array();

		foreach ($query->result_array() as $row)
		{
			$r = [];

			foreach ($this->columns as $column)
			{
				$r[] = $row[$column];
			}

			if ($row_id === 'id')
			{
				$r['DT_RowId'] = $row[$this->primary_key];
			}

			if ($row_id === 'data')
			{
				$r['DT_RowData'] = array(
					'id' => $row[$this->primary_key]
				);
			}

			if ($row_class !== '')
			{
				$r['DT_RowClass'] = $row_class;
			}

			$data['data'][] = $r;
		}

		$data['draw'] = intval($this->request['draw']);

		$data['recordsTotal'] = $this->records_total();

		$data['recordsFiltered'] = $this->records_filtered();

		$this->response($data);
	}
}