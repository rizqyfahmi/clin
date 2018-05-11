<?php

class Model_products extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	/* get the brand data */
	public function getProductData($id = null)
	{
		if($id) {
			$sql = "SELECT products.*, items.weight, stores.name as 'store' FROM products join items on items.id = products.itemid JOIN stores ON stores.id = products.store_id where products.id = ?";
			$query = $this->db->query($sql, array($id));
			return $query->row_array();
		}

		$sql = "SELECT * FROM products ORDER BY id DESC";
		$query = $this->db->query($sql);
		return $query->result_array();
	}

	public function getProductDataRetrun($id = null)
	{
		if($id) {
			$sql = "select main_items.itemid, items.name, items.weight, main_items.qty from main_items join items on items.id = main_items.itemid where main_items.inlaundry = 1 and main_items.isclean = 1 and main_items.clientid = 1";
			$query = $this->db->query($sql, array($id));
			return $query->row_array();
		}

		$sql = "SELECT * FROM products ORDER BY id DESC";
		$query = $this->db->query($sql);
		return $query->result_array();
	}

	public function getProductDataMain($id = null)
	{
		if($id) {
			$sql = "select main_items.itemid, items.name, items.weight, main_items.qty from main_items join items on items.id = main_items.itemid where main_items.inlaundry = 0 and main_items.isclean = 1 and main_items.clientid = 1";
			$query = $this->db->query($sql, array($id));
			return $query->row_array();
		}

		$sql = "select main_items.itemid, items.name, items.weight, main_items.qty from main_items join items on items.id = main_items.itemid where main_items.inlaundry = 0 and main_items.isclean = 1 and main_items.clientid = 1";
		$query = $this->db->query($sql);
		return $query->result_array();
	}

	public function getActiveProductData()
	{
		$sql = "SELECT * FROM products WHERE availability = ? ORDER BY id DESC";
		$query = $this->db->query($sql, array(1));
		return $query->result_array();
	}

	public function getActiveStoreProductData()
	{
		$sql = "SELECT products.id, products.name, stores.name as 'store', items.weight FROM products JOIN items ON items.id = products.itemid JOIN stores ON stores.id = products.store_id WHERE products.availability = ? ORDER BY products.id DESC";
		$query = $this->db->query($sql, array(1));
		return $query->result_array();
	}

	public function create($data)
	{
		if($data) {
			$insert = $this->db->insert('products', $data);
			return ($insert == true) ? true : false;
		}
	}

	public function update($data, $id)
	{
		if($data && $id) {
			$this->db->where('id', $id);
			$update = $this->db->update('products', $data);
			return ($update == true) ? true : false;
		}
	}

	public function remove($id)
	{
		if($id) {
			$this->db->where('id', $id);
			$delete = $this->db->delete('products');
			return ($delete == true) ? true : false;
		}
	}

	public function countTotalProducts()
	{
		$sql = "SELECT * FROM products";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

}
