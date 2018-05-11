<?php

class Model_mainproducts extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	/* get the brand data */
	public function getProductData($id = null)
	{
		if($id) {
			$sql = "select main_items.itemid, items.name, items.weight, main_items.qty from main_items join items on items.id = main_items.itemid where main_items.inlaundry = 1 and main_items.isclean = 1 and main_items.clientid = 1";
			$query = $this->db->query($sql, array($id));
			return $query->row_array();
		}

		$sql = "select main_items.itemid, items.name, items.weight, main_items.qty from main_items join items on items.id = main_items.itemid where main_items.inlaundry = 1 and main_items.isclean = 1 and main_items.clientid = 1";
		$query = $this->db->query($sql);
		return $query->result_array();
	}

	public function getActiveProductData()
	{
		$sql = "SELECT * FROM main_products WHERE availability = ? ORDER BY id DESC";
		$query = $this->db->query($sql, array(1));
		return $query->result_array();
	}

	public function getActiveStoreProductData()
	{
		$sql = "select main_items.itemid, items.name, items.weight from main_items join items on items.id = main_items.itemid where main_items.inlaundry = 1 and main_items.isclean = 1 and main_items.clientid = 1 order by 1 desc";
		$query = $this->db->query($sql, array(1));
		return $query->result_array();
	}

	public function create($data)
	{
		if($data) {
			$insert = $this->db->insert('main_products', $data);
			return ($insert == true) ? true : false;
		}
	}

	public function update($data, $id)
	{
		if($data && $id) {
			$this->db->where('itemid', $id);
			$update = $this->db->update('main_items', $data);
			return ($update == true) ? true : false;
		}
	}

	public function remove($id)
	{
		if($id) {
			$this->db->where('id', $id);
			$delete = $this->db->delete('main_products');
			return ($delete == true) ? true : false;
		}
	}

	public function countTotalProducts()
	{
		$sql = "SELECT * FROM main_products";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

}
