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
			$sql = "SELECT * FROM orders WHERE islaundry = 0 and id = ?";
			$query = $this->db->query($sql, array($id));
			return $query->row_array();
		}

		$sql = "SELECT * FROM orders WHERE islaundry = 0 ORDER BY id DESC";
		$query = $this->db->query($sql);
		return $query->result_array();
	}

	public function getOrdersDataIn($id = null)
	{
		if($id) {
			$sql = "SELECT * FROM main_store WHERE id = ?";
			$query = $this->db->query($sql, array($id));
			return $query->row_array();
		}

		$sql = "SELECT * FROM main_store ORDER BY id DESC";
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

	public function getLaundryItemData($main_id = null)
	{
		if(!$main_id) {
			return false;
		}

		$sql = "SELECT * FROM main_laundry_item WHERE main_id = ?";
		$query = $this->db->query($sql, array($main_id));
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
    		'user_id' => $user_id,
    		'islaundry' => 0
    	);

		$insert = $this->db->insert('orders', $data);//INSERT INTO DB ORDERS
		$order_id = $this->db->insert_id();

		$this->load->model('model_products');

		$count_product = count($this->input->post('product'));
    	for($x = 0; $x < $count_product; $x++) {
    		$items = array(
    			'order_id' => $order_id,
    			'product_id' => $_POST['product'][$x],
    			'qty' => $_POST['qty'][$x], 
    			'weight' => $_POST['wgt_value'][$x],
    			'rate' => $_POST['rate_value'][$x],
    			'amount' => $_POST['amount_value'][$x],
    		);

    		$this->db->insert('orders_item', $items); //INSERT INTO DB ORDERS ITEM
    	}

    	//INSERT INTO ORDERS LAUNDRY
    	$sql = 'SELECT CONCAT(a.order_id, b.itemid) AS id, 
				a.order_id, 
				b.itemid AS item_id, 
				SUM(a.qty) AS qty, 
				a.rate, 
				SUM(a.amount) AS amount,
				ROUND(SUM(a.weight), 2) AS weight,
				0 AS qty_rtn,
				"N/A" AS note

				FROM orders_item AS a 
				LEFT JOIN products AS b ON a.product_id = b.id 
				WHERE a.order_id = "'.$order_id.'" GROUP BY b.itemid';
		$query = $this->db->query($sql);
		foreach ($query->result() as $row) {
			$this->db->insert('orders_laundry', ((array) $row));
		}
		//END OF INSERT INTO ORDERS LAUNDRY

		return ($order_id) ? $order_id : false;
	}

	public function createreturn(){
		$user_id = $this->session->userdata('id');
		$bill_no = 'RETHK-'.strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
    	$data = array(
    		'bill_no' => $bill_no,
				'clientid' => 1,
    		'date_time' => strtotime(date('Y-m-d h:i:s a')),
    		'status' => 1,
    		'desc' => ''
    	);

		$insert = $this->db->insert('main_store', $data);
		$order_id = $this->db->insert_id();

		$this->load->model('model_mainproducts');

		$count_product = count($this->input->post('product'));
    	for($x = 0; $x < $count_product; $x++) {
    		$items = array(
    			'mainid' => $order_id,
    			'itemid' => $_POST['product'][$x],
    			'qty' => $_POST['qty'][$x],
    			'description' => $_POST['desc'][$x]
    		);

    		$this->db->insert('main_store_item', $items);

				$data = array(1,$_POST['product'][$x],$_POST['qty'][$x],'',1,1);
				$insert = $this->db->query("insert into main_items(clientid,itemid,qty,description,inlaundry,isclean) values (?,?,?,?,?,?) on duplicate key update qty = (qty-values(qty))", $data);
				$data = array(1,$_POST['product'][$x],$_POST['qty'][$x],'',0,1);
				$insert = $this->db->query("insert into main_items(clientid,itemid,qty,description,inlaundry,isclean) values (?,?,?,?,?,?) on duplicate key update qty = (qty+values(qty))", $data);

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
	    			'order_id' => $id,
	    			'product_id' => $_POST['product'][$x],
	    			'qty' => $_POST['qty'][$x],
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

	public function approve($id,$status,$objects)
	{
		if($id) {
			switch ($status) {
				case '4':
					$get_order_item = $this->getOrdersItemData($id);
					foreach ($get_order_item as $k => $v) {
						// now decrease the stock from the product
						$product_data = $this->model_products->getProductData($v['product_id']);
						$qty = (int) $product_data['qty'] - (int) $v['qty'];
						$update_product = array('qty' => $qty);
						$this->model_products->update($update_product, $v['product_id']);

						$data = array(1,$v['product_id'],$v['qty'],0,'',0,0,0);
						$insert = $this->db->query("insert into main_items(clientid,itemid,qty,qty_rtn,description,inlaundry,isclean,isreturn) values (?,?,?,?,?,?,?,?) on duplicate key update qty = (qty+values(qty))", $data);
					}
					break;
				case '5':
					$get_order_item = $this->getOrdersItemData($id);
					foreach ($get_order_item as $k => $v) {
						$data = array(1,$v['product_id'],$v['qty'],0,'',1,0,0);
						$insert = $this->db->query("insert into main_items(clientid,itemid,qty,qty_rtn,description,inlaundry,isclean,isreturn) values (?,?,?,?,?,?,?,?)  on duplicate key update qty = (qty+values(qty))", $data);
						$data = array(1,$v['product_id'],$v['qty'],0,'',0,0,0);
						$insert = $this->db->query("insert into main_items(clientid,itemid,qty,qty_rtn,description,inlaundry,isclean,isreturn) values (?,?,?,?,?,?,?,?)  on duplicate key update qty = (qty-values(qty))", $data);
					}
					break;

				case '6':
					$get_order_item = $this->getOrdersItemData($id);
					foreach ($get_order_item as $k => $v) {
						$data = array(1,$v['product_id'],$v['qty'],0,'',1,1,0);
						$insert = $this->db->query("insert into main_items(clientid,itemid,qty,qty_rtn,description,inlaundry,isclean,isreturn) values (?,?,?,?,?,?,?,?)  on duplicate key update qty = (qty+values(qty))", $data);
						$data = array(1,$v['product_id'],$v['qty'],0,'',1,0,0);
						$insert = $this->db->query("insert into main_items(clientid,itemid,qty,qty_rtn,description,inlaundry,isclean,isreturn) values (?,?,?,?,?,?,?,?)  on duplicate key update qty = (qty-values(qty))", $data);
					}
					break;
				case '8':
					$get_order_item = $this->getOrdersItemData($id);
					foreach ($get_order_item as $k => $v) {
						$data = array(1,$v['product_id'],$v['qty'],0,'',0,1,0);
						$insert = $this->db->query("insert into main_items(clientid,itemid,qty,qty_rtn,description,inlaundry,isclean,isreturn) values (?,?,?,?,?,?,?,?)  on duplicate key update qty = (qty+values(qty))", $data);
						$data = array(1,$v['product_id'],$v['qty'],0,'',1,1,0);
						$insert = $this->db->query("insert into main_items(clientid,itemid,qty,qty_rtn,description,inlaundry,isclean,isreturn) values (?,?,?,?,?,?,?,?)  on duplicate key update qty = (qty-values(qty))", $data);
					}
					break;
				// default:
				// 	if(count($objects)>0){
				// 		$get_order_item = $this->getOrdersItemData($id);
				// 		foreach ($get_order_item as $k => $v) {
				// 			foreach ($objects as $Okey => $Ovalue) {

				// 				if($v['product_id'] == $Ovalue['products_id']){
				// 					// print_r($Ovalue);
				// 					if($status==7){
				// 						$data = array(1,$v['product_id'],$v['qty'],$Ovalue['qty_rtn'],$Ovalue['description'],1,1,0);
				// 					}else if($status==8){
				// 						$data = array(1,$v['product_id'],$v['qty'],$Ovalue['qty_rtn'],$Ovalue['description'],0,0,1);
				// 					}
				// 					$insert = $this->db->query("insert into main_items(clientid,itemid,qty,qty_rtn,description,inlaundry,isclean,isreturn) values (?,?,?,?,?,?,?,?)  on duplicate key update qty = (qty+values(qty))", $data);
				// 					$data = array(1,$v['product_id'],$v['qty'],$Ovalue['qty_rtn'],$Ovalue['description'],1,1,0);
				// 					$insert = $this->db->query("insert into main_items(clientid,itemid,qty,qty_rtn,description,inlaundry,isclean,isreturn) values (?,?,?,?,?,?,?,?)  on duplicate key update qty = (qty-values(qty))", $data);
				// 				}	
				// 			}							
				// 		}	
				// 	}
					

				// 	break;
			}

			$data = array(
				'paid_status' => $status
			);

			if(count($objects)>0){
				foreach ($objects as $Okey => $Ovalue) {
					// $this->db->where('id', $Ovalue['order_item_id']);
					// $this->db->update('orders_item', array('id' => $Ovalue['order_item_id'], 'qty_rtn' => $Ovalue['qty_rtn'], 'note' => $Ovalue['description']));
					$this->db->query('UPDATE orders_laundry SET qty_rtn = qty_rtn + '.$Ovalue['qty_remains'].' WHERE id="'.$Ovalue['id'].'"');
				}		
			}


			$this->db->where('id', $id);
			$approve = $this->db->update('orders', $data);
			
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
