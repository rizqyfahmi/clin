<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Items extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->not_logged_in();

		$this->data['page_title'] = 'Items';

		$this->load->model('model_Items');
	}

	/*
	* It only redirects to the manage product page and
	*/
	public function index()
	{
		if(!in_array('viewItem', $this->permission)) {
			redirect('dashboard', 'refresh');
		}

		$result = $this->model_Items->getItemData();

		$this->data['results'] = $result;

		$this->render_template('Items/index', $this->data);
	}

	/*
	* Fetches the Item data from the Item table
	* this function is called from the datatable ajax function
	*/
	public function fetchItemData()
	{
		$result = array('data' => array());

		$data = $this->model_Items->getItemData();
		foreach ($data as $key => $value) {

			// button
			$buttons = '';

			if(in_array('viewItem', $this->permission)) {
				$buttons .= '<button type="button" class="btn btn-default" onclick="editItem('.$value['id'].')" data-toggle="modal" data-target="#editItemModal"><i class="fa fa-pencil"></i></button>';
			}

			if(in_array('deleteItem', $this->permission)) {
				$buttons .= ' <button type="button" class="btn btn-default" onclick="removeItem('.$value['id'].')" data-toggle="modal" data-target="#removeItemModal"><i class="fa fa-trash"></i></button>
				';
			}

			$status = ($value['active'] == 1) ? '<span class="label label-success">Active</span>' : '<span class="label label-warning">Inactive</span>';

			$result['data'][$key] = array(
				$value['name'],
				$value['weight'],
				$status,
				$buttons
			);
		} // /foreach

		echo json_encode($result);
	}

	/*
	* It checks if it gets the Item id and retreives
	* the Item information from the Item model and
	* returns the data into json format.
	* This function is invoked from the view page.
	*/
	public function fetchItemDataById($id)
	{
		if($id) {
			$data = $this->model_Items->getItemData($id);
			echo json_encode($data);
		}

		return false;
	}

	/*
	* Its checks the Item form validation
	* and if the validation is successfully then it inserts the data into the database
	* and returns the json format operation messages
	*/
	public function create()
	{

		if(!in_array('createItem', $this->permission)) {
			redirect('dashboard', 'refresh');
		}

		$response = array();

		$this->form_validation->set_rules('Item_name', 'Item name', 'trim|required');
		$this->form_validation->set_rules('Item_weight', 'Item weight', 'trim|required');
		$this->form_validation->set_rules('active', 'Active', 'trim|required');

		$this->form_validation->set_error_delimiters('<p class="text-danger">','</p>');

        if ($this->form_validation->run() == TRUE) {
        	$data = array(
        		'name' => $this->input->post('Item_name'),
						'weight' => $this->input->post('Item_weight'),
        		'active' => $this->input->post('active'),
        	);

        	$create = $this->model_Items->create($data);
        	if($create == true) {
        		$response['success'] = true;
        		$response['messages'] = 'Succesfully created';
        	}
        	else {
        		$response['success'] = false;
        		$response['messages'] = 'Error in the database while creating the Item information';
        	}
        }
        else {
        	$response['success'] = false;
        	foreach ($_POST as $key => $value) {
        		$response['messages'][$key] = form_error($key);
        	}
        }

        echo json_encode($response);

	}

	/*
	* Its checks the Item form validation
	* and if the validation is successfully then it updates the data into the database
	* and returns the json format operation messages
	*/
	public function update($id)
	{
		if(!in_array('updateItem', $this->permission)) {
			redirect('dashboard', 'refresh');
		}

		$response = array();

		if($id) {
			$this->form_validation->set_rules('edit_Item_name', 'Item name', 'trim|required');
			$this->form_validation->set_rules('edit_Item_weight', 'Item weight', 'trim|required');
			$this->form_validation->set_rules('edit_active', 'Active', 'trim|required');

			$this->form_validation->set_error_delimiters('<p class="text-danger">','</p>');

	        if ($this->form_validation->run() == TRUE) {
	        	$data = array(
	        		'name' => $this->input->post('edit_Item_name'),
							'weight' => $this->input->post('edit_Item_weight'),
	        		'active' => $this->input->post('edit_active'),
	        	);

	        	$update = $this->model_Items->update($data, $id);
	        	if($update == true) {
	        		$response['success'] = true;
	        		$response['messages'] = 'Succesfully updated';
	        	}
	        	else {
	        		$response['success'] = false;
	        		$response['messages'] = 'Error in the database while updated the Item information';
	        	}
	        }
	        else {
	        	$response['success'] = false;
	        	foreach ($_POST as $key => $value) {
	        		$response['messages'][$key] = form_error($key);
	        	}
	        }
		}
		else {
			$response['success'] = false;
    		$response['messages'] = 'Error please refresh the page again!!';
		}

		echo json_encode($response);
	}

	/*
	* It removes the Item information from the database
	* and returns the json format operation messages
	*/
	public function remove()
	{
		if(!in_array('deleteItem', $this->permission)) {
			redirect('dashboard', 'refresh');
		}

		$Item_id = $this->input->post('Item_id');
		$response = array();
		if($Item_id) {
			$delete = $this->model_Items->remove($Item_id);

			if($delete == true) {
				$response['success'] = true;
				$response['messages'] = "Successfully removed";
			}
			else {
				$response['success'] = false;
				$response['messages'] = "Error in the database while removing the Item information";
			}
		}
		else {
			$response['success'] = false;
			$response['messages'] = "Refersh the page again!!";
		}

		echo json_encode($response);
	}

}
