<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Orders extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->not_logged_in();

		$this->data['page_title'] = 'Orders';

		$this->load->model('model_orders');
		$this->load->model('model_products');
		$this->load->model('model_mainproducts');
		$this->load->model('model_company');
	}

	/*
	* It only redirects to the manage order page
	*/
	public function index()
	{
		if(!in_array('viewOrder', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

		$this->data['page_title'] = 'Laundry Request List';
		$this->render_template('orders/index', $this->data);
	}

	public function orderin()
	{
		if(!in_array('viewOrder', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

		$this->data['page_title'] = 'Laundry Return List';
		
		$id = $this->input->get('order_id');
        $sql = "SELECT b.name, b.id AS products_id, a.*, IF(c.paid_status = 7, a.qty_rtn, a.qty) AS qty, a.qty AS qty_req FROM orders_item AS a 
        LEFT JOIN products AS b ON a.product_id = b.id 
        LEFT JOIN orders AS c ON c.id = a.order_id 
        WHERE order_id = '$id' ORDER BY a.id DESC";
		$query = $this->db->query($sql);
		
		$this->data['orders_laundry'] = $query->result_array();
		$this->data['order_id'] = $id;
		$this->data['action'] = $this->input->get('action');
		$this->render_template('orders/orderin', $this->data);
	}

	/*
	* Fetches the orders data from the orders table
	* this function is called from the datatable ajax function
	*/
	public function fetchOrdersData()
	{
		$result = array('data' => array());

		$data = $this->model_orders->getOrdersData();
		// print_r($data);
		foreach ($data as $key => $value) {

			$count_total_item = $this->model_orders->countOrderItem($value['id']);
			$date = date('d-m-Y', $value['date_time']);
			$time = date('h:i a', $value['date_time']);

			$date_time = $date . ' ' . $time;

			// button
			$buttons = '';

			if(in_array('viewOrder', $this->permission)) {
				$buttons .= '<a target="__blank" href="'.base_url('orders/printDiv/'.$value['id'].'/'.$value['paid_status']).'" class="btn btn-default"><i class="fa fa-print"></i></a>';
				$buttons .= ' <a class="btn btn-default" href="'.base_url('orders/orderin?order_id='.$value['id'].'&action=0').'"><i class="fa fa-eye"></i></a>';
			}

			if(in_array('viewOrder', $this->permission)) {
				// if(($value['paid_status'] == '3') OR ($value['paid_status'] == '6')){
				if(($value['paid_status'] == '3') OR ($value['paid_status'] == '8')){
					$buttons .= ' <button type="button" class="btn btn-default" disabled><i class="fa fa-magic"></i></button>';
				}else{
					$buttons .= ' <button type="button" class="btn btn-default" onclick="notifFunc('.$value['id'].',\''.$value['paid_status'].'\',\''.$value['bill_no'].'\')"><i class="fa fa-magic"></i></button>';
					
				}
			}

			switch($value['paid_status']){
				case '1':
					$paid_status = '<span class="label label-warning">Request Approval</span>';
					break;
				case '2':
					$paid_status = '<span class="label label-success">Request Approved</span>';
					break;
				case '3':
					$paid_status = '<span class="label label-danger">Request Reject</span>';
					break;
				case '4':
					$paid_status = '<span class="label label-warning">Send to Laundry</span>';
					break;
				case '5':
					$paid_status = '<span class="label label-success">Laundry Received</span>';
					break;
				case '6':
					$paid_status = '<span class="label label-warning">Ready to return</span>';
					break;
				case '7':
					$paid_status = '<span class="label label-warning">Partial Completed</span>';
					break;
				default:
					$paid_status = '<span class="label label-success">Completed</span>';
					break;
			}

			$result['data'][$key] = array(
				$value['bill_no'],
				$date_time,
				$count_total_item,
				$value['net_amount'],
				$paid_status,
				$buttons
			);
		} // /foreach

		echo json_encode($result);
	}

	public function fetchOrdersDataIn()
	{
		$result = array('data' => array());

		$data = $this->model_orders->getOrdersDataIn();

		foreach ($data as $key => $value) {

			$count_total_item = $this->model_orders->countOrderItem($value['id']);
			$date = date('d-m-Y', $value['date_time']);
			$time = date('h:i a', $value['date_time']);

			$date_time = $date . ' ' . $time;

			// button
			$buttons = '';

			if(in_array('viewOrder', $this->permission)) {
				$buttons .= '<a target="__blank" href="'.base_url('orders/printDiv/'.$value['id'].'/'.$value['status']).'" class="btn btn-default"><i class="fa fa-print"></i></a>';
			}

			if(in_array('viewOrder', $this->permission)) {
				$buttons .= ' <button type="button" class="btn btn-default" onclick="notifFunc('.$value['id'].',\''.$value['status'].'\',\''.$value['bill_no'].'\')"><i class="fa fa-magic"></i></button>';
			}

			switch($value['status']){
				case '7':
					$status = '<span class="label label-warning">Laundry Returned</span>';
					break;
				case '8':
					$status = '<span class="label label-success">Completed</span>';
					break;
				default:
					$status = '<span class="label label-success">Completed</span>';
					break;
			}

			$result['data'][$key] = array(
				$value['bill_no'],
				$date_time,
				$count_total_item,
				$status,
				$buttons
			);
		} // /foreach

		echo json_encode($result);
	}

	/*
	* If the validation is not valid, then it redirects to the create page.
	* If the validation for each input field is valid then it inserts the data into the database
	* and it stores the operation message into the session flashdata and display on the manage group page
	*/
	public function create()
	{
		if(!in_array('createOrder', $this->permission)) {
	        redirect('dashboard', 'refresh');
	    }

		$this->data['page_title'] = 'Add Order';
		$this->form_validation->set_rules('product[]', 'Product name', 'trim|required');

        if ($this->form_validation->run() == TRUE) {

        	$order_id = $this->model_orders->create();

        	if($order_id) {
        		$this->session->set_flashdata('success', 'Successfully created');
						redirect('orders', 'refresh');
        	}
        	else {
        		$this->session->set_flashdata('errors', 'Error occurred!!');
        		redirect('orders/create/', 'refresh');
        	}
        }
        else {
            // false case
        	$company = $this->model_company->getCompanyData(1);
        	$this->data['company_data'] = $company;
        	$this->data['is_vat_enabled'] = ($company['vat_charge_value'] > 0) ? true : false;
        	$this->data['is_service_enabled'] = ($company['service_charge_value'] > 0) ? true : false;

        	$this->data['products'] = $this->model_products->getActiveStoreProductData();

          $this->render_template('orders/create', $this->data);
        }
	}


	public function getOrderItemByIdOrder(){
		// $id = $this->input->get('order_id');
  //       $sql = "SELECT b.name, b.id AS products_id, a.*, IF(c.paid_status = 7, a.qty_rtn, a.qty) AS qty, a.qty AS qty_req FROM orders_item AS a 
  //       LEFT JOIN products AS b ON a.product_id = b.id 
  //       LEFT JOIN orders AS c ON c.id = a.order_id 
  //       WHERE order_id = '$id' ORDER BY a.id DESC";
		// $query = $this->db->query($sql);
		// // return $query->result_array();
		// echo json_encode($query->result_array());
		// echo $sql;

		$start = $this->input->post('start');
        $length = $this->input->post('length');
        $draw = $this->input->post('draw');
        $search = $this->input->post('search');
        $order = $this->input->post('order');
        $columns = $this->input->post('columns');
        $status = $this->input->post('order_id');
        $conditions = $this->input->post('conditions');
        $conditions = empty($conditions) ? array() : ((array) $conditions);

        $tempColumns = array();
        foreach ($columns as $column) {
            array_push($tempColumns, $column['data']);
        }

        $order = $tempColumns[$order[0]['column']] . ' ' . $order[0]['dir'];
        $limit = '';
        if ($length != -1) {
            $limit = 'LIMIT ' . $start . ', ' . $length;
        }
	
		$tempCondition1 = array();
        foreach ($conditions as $key => $value) {
            array_push($tempCondition1, $key . ' LIKE UPPER("%' . $value . '%")');
        }
		
		$tempCondition2 = array();
		if($search['value'] != NULL){
			foreach ($tempColumns as $field) {
				if(empty($conditions[$field])) 
					array_push($tempCondition2, ($field . ' LIKE UPPER("%' . $search['value'] . '%")'));            
			}
		} 

		$tempI = 0;
		$where = array();		
		if($status != NULL){
			$where[$tempI] = '(a.order_id = "' . $status . '")';
			$tempI++;
		}
		
		if(COUNT($tempCondition1) > 0){
			$where[$tempI] = '(' . join(' OR ', $tempCondition1) . ')';
			$tempI++;
		}
		
		if(COUNT($tempCondition2)>0){
			$where[$tempI] = '(' . join(' OR ', $tempCondition2) . ')';
			$tempI++;
		}
		
		if(COUNT($where)>0){
			$where = 'WHERE '.join(' AND ', $where);
		}else{
			$where = '';
		}


		$query = 'SELECT '.join(', ', $tempColumns).' FROM (
			SELECT a.order_id, b.name, a.qty, a.qty_rtn, (a.qty - a.qty_rtn) AS qty_remains, a.id FROM orders_laundry AS a 
			LEFT JOIN items AS b ON a.item_id = b.id
		) AS a';
		
		
        $result = $this->db->query($query.' '.$where.' ORDER BY '.$order.' '.$limit)->result();
        $data = array(
            "draw" => $draw,
            "recordsTotal" => $this->db->query($query.' '.$where)->num_rows(),
            "recordsFiltered" => $this->db->query($query.' '.$where.' ORDER BY '.$order)->num_rows(),
            "data" => $result,
			"query" => ($query.' '.$where.' ORDER BY '.$order.' '.$limit)
        );
//        print_r($result);
        header("content-type: application/json");
        echo json_encode($data);
        exit;
	}


	public function returnin()
	{
		if(!in_array('createOrder', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

		$this->data['page_title'] = 'Add Return';

		$this->form_validation->set_rules('product[]', 'Product name', 'trim|required');


        if ($this->form_validation->run() == TRUE) {

        	$order_id = $this->model_orders->createreturn();

        	if($order_id) {
        		$this->session->set_flashdata('success', 'Successfully created');
						redirect('orders/orderin/', 'refresh');
        	}
        	else {
        		$this->session->set_flashdata('errors', 'Error occurred!!');
        		redirect('orders/returnin/', 'refresh');
        	}
        }
        else {
            // false case
        	$company = $this->model_company->getCompanyData(1);
        	$this->data['company_data'] = $company;
        	$this->data['is_vat_enabled'] = ($company['vat_charge_value'] > 0) ? true : false;
        	$this->data['is_service_enabled'] = ($company['service_charge_value'] > 0) ? true : false;

        	$this->data['products'] = $this->model_mainproducts->getActiveStoreProductData();

          $this->render_template('orders/createin', $this->data);
        }
	}

	/*
	* It gets the product id passed from the ajax method.
	* It checks retrieves the particular product data from the product id
	* and return the data into the json format.
	*/
	public function getProductValueById()
	{
		$product_id = $this->input->post('product_id');
		if($product_id) {
			$product_data = $this->model_products->getProductData($product_id);
			echo json_encode($product_data);
		}
	}

	/*
	* It gets the all the active product inforamtion from the product table
	* This function is used in the order page, for the product selection in the table
	* The response is return on the json format.
	*/
	public function getTableProductRow()
	{
		$products = $this->model_products->getActiveStoreProductData();
		echo json_encode($products);
	}

	public function getTableProductRowIn()
	{
		$products = $this->model_mainproducts->getActiveStoreProductData();
		echo json_encode($products);
	}


	/*
	* If the validation is not valid, then it redirects to the edit orders page
	* If the validation is successfully then it updates the data into the database
	* and it stores the operation message into the session flashdata and display on the manage group page
	*/
	public function update($id){
		if(!in_array('updateOrder', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

		if(!$id) {
			redirect('dashboard', 'refresh');
		}

		$this->data['page_title'] = 'Update Order';

		$this->form_validation->set_rules('product[]', 'Product name', 'trim|required');


        if ($this->form_validation->run() == TRUE) {

        	$update = $this->model_orders->update($id);

        	if($update == true) {
        		$this->session->set_flashdata('success', 'Successfully updated');
        		redirect('orders/update/'.$id, 'refresh');
        	}
        	else {
        		$this->session->set_flashdata('errors', 'Error occurred!!');
        		redirect('orders/update/'.$id, 'refresh');
        	}
        }
        else {
            // false case
        	$company = $this->model_company->getCompanyData(1);
        	$this->data['company_data'] = $company;
        	$this->data['is_vat_enabled'] = ($company['vat_charge_value'] > 0) ? true : false;
        	$this->data['is_service_enabled'] = ($company['service_charge_value'] > 0) ? true : false;

        	$result = array();
        	$orders_data = $this->model_orders->getOrdersData($id);

    		$result['order'] = $orders_data;
    		$orders_item = $this->model_orders->getOrdersItemData($orders_data['id']);

    		foreach($orders_item as $k => $v) {
    			$result['order_item'][] = $v;
    		}

    		$this->data['order_data'] = $result;

        	//$this->data['products'] = $this->model_products->getActiveProductData();
					$this->data['products'] = $this->model_products->getActiveStoreProductData();
            $this->render_template('orders/edit', $this->data);
        }
	}

	/*
	* It removes the data from the database
	* and it returns the response into the json format
	*/
	public function remove()
	{
		if(!in_array('deleteOrder', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

		$order_id = $this->input->post('order_id');

        $response = array();
        if($order_id) {
            $delete = $this->model_orders->remove($order_id);
            if($delete == true) {
                $response['success'] = true;
                $response['messages'] = "Successfully removed";
            }
            else {
                $response['success'] = false;
                $response['messages'] = "Error in the database while removing the product information";
            }
        }
        else {
            $response['success'] = false;
            $response['messages'] = "Refersh the page again!!";
        }

        echo json_encode($response);
	}

	/*
	* It approve the data from the database
	* and it returns the response into the json format
	*/
	public function approve()
	{
		if(!in_array('viewOrder', $this->permission)) {
        redirect('dashboard', 'refresh');
    }

		$order_id = $this->input->post('order_id');
		$status = $this->input->post('status');
		$objects = $this->input->post('objects');

        $response = array();
        if($order_id) {
            $approve = $this->model_orders->approve($order_id,$status,$objects);
            if($approve == true) {
                $response['success'] = true;
                $response['messages'] = "Successfully";
            }
            else {
                $response['success'] = false;
                $response['messages'] = "Error in the database while process the request";
            }
        }
        else {
            $response['success'] = false;
            $response['messages'] = "Refersh the page again!!";
        }

        echo json_encode($response);
	}

	/*
	* It gets the product id and fetch the order data.
	* The order print logic is done here
	*/
	public function printDiv($id,$status)
	{
		if(!in_array('viewOrder', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

		if($id) {
			$order_data = $this->model_orders->getOrdersData($id);
			if($status <= 4){
				$orders_items = $this->model_orders->getOrdersItemData($id);
			}else{
				$orders_items = $this->model_orders->getOrdersItemDataOut($id);
			}
			$company_info = $this->model_company->getCompanyData(1);

			$order_date = date('d/m/Y', $order_data['date_time']);
			$paid_status = ($order_data['paid_status'] == 1) ? "Paid" : "Unpaid";

			$html = '<!-- Main content -->
			<!DOCTYPE html>
			<html>
			<head>
			  <meta charset="utf-8">
			  <meta http-equiv="X-UA-Compatible" content="IE=edge">
			  <title>CLIN | Detail Request</title>
			  <!-- Tell the browser to be responsive to screen width -->
			  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
			  <!-- Bootstrap 3.3.7 -->
			  <link rel="stylesheet" href="'.base_url('assets/bower_components/bootstrap/dist/css/bootstrap.min.css').'">
			  <!-- Font Awesome -->
			  <link rel="stylesheet" href="'.base_url('assets/bower_components/font-awesome/css/font-awesome.min.css').'">
			  <link rel="stylesheet" href="'.base_url('assets/dist/css/AdminLTE.min.css').'">
			</head>
			<body onload="window.print();">

			<div class="wrapper">
			  <section class="invoice">
			    <!-- title row -->
			    <div class="row">
			      <div class="col-xs-12">
			        <h2 class="page-header">
			          '.$company_info['company_name'].'
			          <small class="pull-right">Date: '.$order_date.'</small>
			        </h2>
			      </div>
			      <!-- /.col -->
			    </div>
			    <!-- info row -->
			    <div class="row invoice-info">

			      <div class="col-sm-4 invoice-col">

			        <b>Bill ID:</b> '.$order_data['bill_no'].'<br>
			        <b>Name:</b> '.$order_data['customer_name'].'<br>
			        <b>Address:</b> '.$order_data['customer_address'].' <br />
			        <b>Phone:</b> '.$order_data['customer_phone'].'
			      </div>
			      <!-- /.col -->
			    </div>
			    <!-- /.row -->

			    <!-- Table row -->
			    <div class="row">
			      <div class="col-xs-12 table-responsive">
			        <table class="table table-striped">
			          <thead>
			          <tr>
			            <th>Product</th>
			            <th>Price</th>
			            <th>Qty/pcs</th>
			            <th>Return Qty/pcs</th>
						<th>Qty/kg</th>
			            <th>Amount</th>
			          </tr>
			          </thead>
			          <tbody>';

			          $sql = "SELECT b.name, b.id AS products_id, a.*, IF(c.paid_status = 7, a.qty_rtn, a.qty) AS qty, IFNULL(qty_rtn, 'N/A') AS qty_rtn FROM orders_item AS a 
				        LEFT JOIN products AS b ON a.product_id = b.id 
				        LEFT JOIN orders AS c ON c.id = a.order_id 
				        WHERE order_id = '$id' ORDER BY a.id DESC";
						$query = $this->db->query($sql);
						$orders_items = $query->result_array();

			          foreach ($orders_items as $k => $v) {

			          	$product_data = $this->model_products->getProductData($v['product_id']);

			          	$html .= '<tr>
				            <td>'.$product_data['name'].'</td>
				            <td>'.$v['rate'].'</td>
				            <td>'.$v['qty'].'</td>
				            <td>'.$v['qty_rtn'].'</td>
							<td>'.$v['weight'].'</td>
				            <td>'.$v['amount'].'</td>
			          	</tr>';
			          }

			          $html .= '</tbody>
								<tfoot>
			          <tr>
			            <th></th>
			            <th></th>
			            <th></th>
									<th>Gross Amount:</th>
			            <th>'.$order_data['gross_amount'].'</th>
			          </tr>
			          </tfoot>
			        </table>
			      </div>
			      <!-- /.col -->
			    </div>
			    <!-- /.row -->
			  </section>
			  <!-- /.content -->
			</div>
		</body>
	</html>';

			  echo $html;
		}
	}

}
