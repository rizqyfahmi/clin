<?php

class Model_orders extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	/* get the orders data */
	public function getOrdersData($id = null)
	{
		if($id) {
			$sql = "SELECT * FROM orders WHERE id = ?";
			$query = $this->db->query($sql, array($id));
			return $query->row_array();
		}

		$sql = "SELECT * FROM orders ORDER BY id DESC";
		$query = $this->db->query($sql);
		return $query->result_array();
	}

	public function getOrdersDataIn($id = null)
	{
		if($id) {
			$sql = "SELECT * FROM main_laundry WHERE id = ?";
			$query = $this->db->query($sql, array($id));
			return $query->row_array();
		}

		$sql = "SELECT * FROM main_laundry ORDER BY id DESC";
		$query = $this->db->query($sql);
		return $query->result_array();
	}

	// get the orders item data
	public function getOrdersItemData($order_id = null)
	{
		if(!$order_id) {
			return false;
		}

		$sql = "SELECT * FROM orders_item WHERE order_id = ?";
		$query = $this->db->query($sql, array($order_id));
		return $query->result_array();
	}

	public function getOrdersItemDataOut($order_id = null)
	{
		if(!$order_id) {
			return false;
		}

		$sql = "SELECT orders_item.order_id, orders_item.product_id, sum(orders_item.qty) as qty, orders_item.rate, round(sum(orders_item.weight * orders_item.rate),2) as amount, round(sum(orders_item.weight),2) as weight FROM orders_item join products on orders_item.product_id = products.id where orders_item.order_id = ? group by orders_item.order_id, orders_item.product_id, orders_item.rate";
		$query = $this->db->query($sql, array($order_id));
		return $query->result_array();
	}

	public function create(){
		$user_id = $this->session->userdata('id');
		$bill_no = 'REQHK-'.strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
    	$data = array(
    		'bill_no' => $bill_no,
    		'customer_name' => $this->input->post('customer_name'),
    		'customer_address' => $this->input->post('customer_address'),
    		'customer_phone' => $this->input->post('customer_phone'),
    		'date_time' => strtotime(date('Y-m-d h:i:s a')),
    		'gross_amount' => $this->input->post('gross_amount_value'),
    		'service_charge_rate' => $this->input->post('service_charge_rate'),
    		'service_charge' => ($this->input->post('service_charge_value') > 0) ?$this->input->post('service_charge_value'):0,
    		'vat_charge_rate' => $this->input->post('vat_charge_rate'),
    		'vat_charge' => ($this->input->post('vat_charge_value') > 0) ? $this->input->post('vat_charge_value') : 0,
    		'net_amount' => $this->input->post('gross_amount_value'),
    		'discount' => 0,
    		'paid_status' => 1,
    		'user_id' => $user_id
    	);

		$insert = $this->db->insert('orders', $data);
		$order_id = $this->db->insert_id();

		$this->load->model('model_products');

		$count_product = count($this->input->post('product'));
    	for($x = 0; $x < $count_product; $x++) {
    		$items = array(
    			'order_id' => $order_id,
    			'product_id' => ($_POST['product'][$x]),
    			'qty' => ($_POST['qty'][$x]),
				'weight' => $_POST['wgt_value'][$x],
    			'rate' => $_POST['rate_value'][$x],
    			'amount' => $_POST['amount_value'][$x],
    		);

    		$this->db->insert('orders_item', $items);

    		// now decrease the stock from the product
    		$product_data = $this->model_products->getProductData($_POST['product'][$x]);
    		$qty = (int) $product_data['qty'] - (int) $_POST['qty'][$x];

    		$update_product = array('qty' => $qty);


    		$this->model_products->update($update_product, $_POST['product'][$x]);
    	}

		return ($order_id) ? $order_id : false;
	}

	public function createreturn(){
		$user_id = $this->session->userdata('id');
		$bill_no = 'RETHK-'.strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
    	$data = array(
    		'bill_no' => $bill_no,
    		'customer_name' => $this->input->post('customer_name'),
    		'customer_address' => $this->input->post('customer_address'),
    		'customer_phone' => $this->input->post('customer_phone'),
    		'date_time' => strtotime(date('Y-m-d h:i:s a')),
    		'gross_amount' => $this->input->post('gross_amount_value'),
    		'service_charge_rate' => $this->input->post('service_charge_rate'),
    		'service_charge' => ($this->input->post('service_charge_value') > 0) ?$this->input->post('service_charge_value'):0,
    		'vat_charge_rate' => $this->input->post('vat_charge_rate'),
    		'vat_charge' => ($this->input->post('vat_charge_value') > 0) ? $this->input->post('vat_charge_value') : 0,
    		'net_amount' => $this->input->post('gross_amount_value'),
    		'discount' => 0,
    		'paid_status' => 1,
    		'user_id' => $user_id
    	);

		$insert = $this->db->insert('orders', $data);
		$order_id = $this->db->insert_id();

		$this->load->model('model_products');

		$count_product = count($this->input->post('product'));
    	for($x = 0; $x < $count_product; $x++) {
    		$items = array(
    			'order_id' => $order_id,
    			'product_id' => ($_POST['product'][$x]),
    			'qty' => ($_POST['qty'][$x]),
				'weight' => $_POST['wgt_value'][$x],
    			'rate' => $_POST['rate_value'][$x],
    			'amount' => $_POST['amount_value'][$x],
    		);

    		$this->db->insert('orders_item', $items);

    		// now decrease the stock from the product
    		$product_data = $this->model_products->getProductData($_POST['product'][$x]);
    		$qty = (int) $product_data['qty'] - (int) $_POST['qty'][$x];

    		$update_product = array('qty' => $qty);


    		$this->model_products->update($update_product, $_POST['product'][$x]);
    	}

		return ($order_id) ? $order_id : false;
	}

	public function countOrderItem($order_id)
	{
		if($order_id) {
			$sql = "SELECT * FROM orders_item WHERE order_id = ?";
			$query = $this->db->query($sql, array($order_id));
			return $query->num_rows();
		}
	}

	public function update($id)
	{
		if($id) {
			$user_id = $this->session->userdata('id');
			// fetch the order data

			$data = array(
				'customer_name' => $this->input->post('customer_name'),
	    		'customer_address' => $this->input->post('customer_address'),
	    		'customer_phone' => $this->input->post('customer_phone'),
	    		'gross_amount' => $this->input->post('gross_amount_value'),
	    		'service_charge_rate' => $this->input->post('service_charge_rate'),
	    		'service_charge' => ($this->input->post('service_charge_value') > 0) ? $this->input->post('service_charge_value'):0,
	    		'vat_charge_rate' => $this->input->post('vat_charge_rate'),
	    		'vat_charge' => ($this->input->post('vat_charge_value') > 0) ? $this->input->post('vat_charge_value') : 0,
	    		'net_amount' => $this->input->post('gross_amount_value'),
	    		'discount' => 0,
	    		'paid_status' => $this->input->post('paid_status'),
	    		'user_id' => $user_id
	    	);

			$this->db->where('id', $id);
			$update = $this->db->update('orders', $data);

			// now the order item
			// first we will replace the product qty to original and subtract the qty again
			$this->load->model('model_products');
			$get_order_item = $this->getOrdersItemData($id);
			foreach ($get_order_item as $k => $v) {
				$product_id = $v['product_id'];
				$qty = $v['qty'];
				// get the product
				$product_data = $this->model_products->getProductData($product_id);
				$update_qty = $qty + $product_data['qty'];
				$update_product_data = array('qty' => $update_qty);

				// update the product qty
				$this->model_products->update($update_product_data, $product_id);
			}

			// now remove the order item data
			$this->db->where('order_id', $id);
			$this->db->delete('orders_item');

			// now decrease the product qty
			$count_product = count($this->input->post('product'));
	    	for($x = 0; $x < $count_product; $x++) {
	    		$items = array(
    				'order_id' => $order_id,
    				'product_id' => ($_POST['product'][$x]),
    				'qty' => ($_POST['qty'][$x]),
					'weight' => $_POST['wgt_value'][$x],
    				'rate' => $_POST['rate_value'][$x],
    				'amount' => $_POST['amount_value'][$x],
    			);
	    		$this->db->insert('orders_item', $items);

	    		// now decrease the stock from the product
	    		$product_data = $this->model_products->getProductData($_POST['product'][$x]);
	    		$qty = (int) $product_data['qty'] - (int) $_POST['qty'][$x];

	    		$update_product = array('qty' => $qty);
	    		$this->model_products->update($update_product, $_POST['product'][$x]);
	    	}

			return true;
		}
	}



	public function remove($id)
	{
		if($id) {
			$this->db->where('id', $id);
			$delete = $this->db->delete('orders');

			$this->db->where('order_id', $id);
			$delete_item = $this->db->delete('orders_item');
			return ($delete == true && $delete_item) ? true : false;
		}
	}

	public function approve($id,$status)
	{
		if($id) {
			if($status == '2'){
				$sql_ml = "insert into main_laundry select id, 1, bill_no, date_time, gross_amount, 4, '' from orders where id = ?";
				$this->db->query($sql_ml, $id);

				$sql_mli = "insert into main_laundry_item select null,orders_item.order_id, products.itemid, products.name, sum(orders_item.qty) as total from orders_item join products on orders_item.product_id = products.id where orders_item.order_id = ? group by products.itemid, products.name";
				$this->db->query($sql_mli, $id);
			}

			$data = array(
				'paid_status' => $status
			);
			$this->db->where('id', $id);
			$approve = $this->db->update('orders', $data);
			if($status == '5'){
				$data = array(
					'status' => $status
				);
				$this->db->where('id', $id);
				$this->db->update('main_laundry', $data);
			}

			return ($approve == true) ? true : false;
		}
	}

	public function countTotalPaidOrders()
	{
		$sql = "SELECT * FROM orders WHERE paid_status = ?";
		$query = $this->db->query($sql, array(1));
		return $query->num_rows();
	}

}
