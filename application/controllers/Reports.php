<?php  

defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends Admin_Controller 
{	
	public function __construct()
	{
		parent::__construct();
		$this->data['page_title'] = 'Stores';
		$this->load->model('model_reports');
	}

	/* 
    * It redirects to the report page
    * and based on the year, all the orders data are fetch from the database.
    */
	public function index()
	{
		if(!in_array('viewReports', $this->permission)) {
            redirect('dashboard', 'refresh');
        }
		
		$today_year = date('Y');

		if($this->input->post('select_year')) {
			$today_year = $this->input->post('select_year');
		}

		$parking_data = $this->model_reports->getOrderData($today_year);
		$this->data['report_years'] = $this->model_reports->getOrderYear();
		

		$final_parking_data = array();
		foreach ($parking_data as $k => $v) {
			
			if(count($v) > 1) {
				$total_amount_earned = array();
				foreach ($v as $k2 => $v2) {
					if($v2) {
						$total_amount_earned[] = $v2['gross_amount'];						
					}
				}
				$final_parking_data[$k] = array_sum($total_amount_earned);	
			}
			else {
				$final_parking_data[$k] = 0;	
			}
			
		}
		
		$this->data['selected_year'] = $today_year;
		$this->data['company_currency'] = $this->company_currency();
		$this->data['results'] = $final_parking_data;

		$this->render_template('reports/index', $this->data);
	}




	public function grid($month = null, $type = null)
	{
		if(!in_array('viewReports', $this->permission)) {
            redirect('dashboard', 'refresh');
        }
		// echo 'Hello '.$month;

		$month = $this->input->get('q');
		// echo date("Y-m-t", strtotime($month.'-01'));
        $days = array();
		for($i=1; $i<=date("t", strtotime($month.'-01'));$i++){
			// echo date("Y-m-d", strtotime($month.'-'.$i))."<br/>"; 
			// $query = $this->db->query('
			// 	SELECT b.name, 
			// 	b.id AS products_id, 
			// 	a.*, IF(c.paid_status = 7, a.qty_rtn, a.qty) AS qty, 
			// 	a.qty AS qty_req, 
			// 	SUM(a.qty) AS sum_qty_req 
			// 	FROM orders_item AS a 
   //      		LEFT JOIN products AS b ON a.product_id = b.id 
   //      		LEFT JOIN orders AS c ON c.id = a.order_id 
   //      		WHERE FROM_UNIXTIME(date_time, "%Y-%m-%d") = "'.date("Y-m-d", strtotime($month.'-'.$i)).' " 
   //      		AND c.paid_status >= 4   
   //      		GROUP BY b.id ORDER BY a.id DESC'
   //      	);
        	$query = $this->db->query('
				SELECT
				c.id AS item_id, 
				SUM(a.qty) AS qty_req, 
				SUM(a.qty_rtn) AS qty_rtn, 
				SUM(a.qty - a.qty_rtn) AS qty_remains 
				FROM orders_laundry AS a
				LEFT JOIN orders AS b ON a.order_id = b.id
				LEFT JOIN items AS c ON c.id = a.item_id
				
        		WHERE FROM_UNIXTIME(b.date_time, "%Y-%m-%d") = "'.date("Y-m-d", strtotime($month.'-'.$i)).' " 
        		AND b.paid_status >= 4   
        		GROUP BY c.id ORDER BY c.id DESC'
        	);

			 
			array_push($days, array(
				'fulldate' => date("Y-m-d", strtotime($month.'-'.$i)),
				'date' => date("d", strtotime($month.'-'.$i)),
				'is_weekend' => (strtoupper(date("D", strtotime($month.'-'.$i))) == "SUN"),
				'product' => $query->result_array()
			));
		}

		$query = $this->db->query('
				SELECT * FROM items AS b 
        		GROUP BY b.id');


		$date_group = $this->db->query('
			SELECT IF(FROM_UNIXTIME(date_time, "%Y-%m")="'.$month.'", CONCAT("<option value=\"", FROM_UNIXTIME(date_time, "%Y-%m"), "\" selected>", FROM_UNIXTIME(date_time, "%M %Y"),"</option>"), CONCAT("<option value=\"", FROM_UNIXTIME(date_time, "%Y-%m"), "\">", FROM_UNIXTIME(date_time, "%M %Y"),"</option>")) AS month FROM orders AS c 
       		WHERE c.paid_status >= 4   
        	GROUP BY FROM_UNIXTIME(date_time, "%Y-%m")

		');

		// return $query->result_array();
		//echo json_encode($days);



		// strtotime(date('Y-m-d h:i:s a'));
		// print_r($days);
		// $today_year = date('Y');

		// if($this->input->post('select_year')) {
		// 	$today_year = $this->input->post('select_year');
		// }

		// $parking_data = $this->model_reports->getOrderData($today_year);
		// $this->data['report_years'] = $this->model_reports->getOrderYear();
		

		// $final_parking_data = array();
		// foreach ($parking_data as $k => $v) {
			
		// 	if(count($v) > 1) {
		// 		$total_amount_earned = array();
		// 		foreach ($v as $k2 => $v2) {
		// 			if($v2) {
		// 				$total_amount_earned[] = $v2['gross_amount'];						
		// 			}
		// 		}
		// 		$final_parking_data[$k] = array_sum($total_amount_earned);	
		// 	}
		// 	else {
		// 		$final_parking_data[$k] = 0;	
		// 	}
			
		// }
		
		// $this->data['selected_year'] = $today_year;
		// $this->data['company_currency'] = $this->company_currency();
		// $this->data['results'] = $final_parking_data;
		
		$this->data['date_group'] = $date_group->result_array();
		$this->data['days'] = $days;
		$this->data['products'] = $query->result_array();
		$this->render_template('reports/grid', $this->data);
	}
}	