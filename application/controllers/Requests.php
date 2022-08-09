<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Requests extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->not_logged_in();

		$this->data['page_title'] = 'Requests';

		$this->load->model('model_requests');
		$this->load->model('model_products');
		$this->load->model('model_company');
		$this->load->model('model_users');
	}

	/* 
	 * It only redirects to the manage request page
	 */
	public function index()
	{
		if (!in_array('viewRequest', $this->permission)) {
			redirect('dashboard', 'refresh');
		}

		$this->data['page_title'] = 'Manage Requests';
		$this->render_template('requests/index', $this->data);
	}

	/*
	 * Fetches the requests data from the requests table 
	 * this function is called from the datatable ajax function
	 */
	public function fetchRequestsData()
	{
		$result = array('data' => array());
		$data = $this->model_requests->getRequestsData();
		$sr_no = 0;
		foreach ($data as $key => $value) {
			$sr_no++;
			// $username = $value['user_id'];
			$username = $this->model_users->getUsername($value['user_id']);
			$product_id = $this->model_requests->getProductId($value['id']);
			$remarks = $value['remarks'];
			$ids = array();
			$product_qty = array();
			foreach ($product_id as $product) {
				$product_qty[] = $product['qty'];
				$ids[] = $product['product_id'];
			}
			$product_data = $this->model_products->getMultipleProductData($ids);
			$product_names = array();
			foreach ($product_data as $product) {
				$product_names[] = $product['name'];
			}
			$count_total_item = $this->model_requests->countRequestItem($value['id']);
			$date = date('d-m-Y', $value['date_time']);
			$time = date('h:i a', $value['date_time']);

			$date_time = $date . ' ' . $time;

			// button
			$buttons = '';

			if (in_array('viewRequest', $this->permission)) {
				$buttons .= '<a target="__blank" href="' . base_url('requests/printDiv/' . $value['id']) . '" class="btn btn-default"><i class="fa fa-print"></i></a>';
			}

			if (in_array('viewRequest', $this->permission) && $value['user_id'] == $this->session->id && $value['updated_by'] == "Pending") {
				$buttons .= ' <a href="' . base_url('requests/update/' . $value['id']) . '" class="btn btn-default"><i class="fa fa-pencil"></i></a>';
				$buttons .= ' <a href="' . base_url('requests/updateStatus/revoke/' . $value['id']) . '" class="btn btn-default"><i class="fa fa-undo"></i></a>';
			}

			$updateButtons = '';

			if (in_array('updateRequestStatus', $this->permission)) {
				// $this->model_requests->updateStatus($value['id']);
				$updateButtons .= ' <a href="' . base_url('requests/updateStatus/approve/' . $value['id']) . '" class="btn btn-default"><i class="fa fa-check"></i></a>';
				$updateButtons .= ' <a href="' . base_url('requests/updateStatus/reject/' . $value['id']) . '" class="btn btn-default"><i class="fa fa-ban"></i></a>';
			}

			if ($value['updated_by'] != "Pending")
				$updateButtons = $this->model_users->getUsername($value['updated_by']);

			$result['data'][$key] = array(
				$sr_no,
				$username,
				$product_names,
				$product_qty,
				$remarks,
				$date_time,
				$value['request_status'],
				$buttons,
				$updateButtons
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
		if (!in_array('createRequest', $this->permission)) {
			redirect('dashboard', 'refresh');
		}

		$this->data['page_title'] = 'Add Request';

		$this->form_validation->set_rules('product[]', 'Product name', 'trim|required');


		if ($this->form_validation->run() == TRUE) {

			$request_id = $this->model_requests->create();

			if ($request_id) {
				$this->session->set_flashdata('success', 'Successfully created');
				redirect('requests/update/' . $request_id, 'refresh');
			} else {
				$this->session->set_flashdata('errors', 'Error occurred!!');
				redirect('requests/create/', 'refresh');
			}
		} else {
			// false case
			$company = $this->model_company->getCompanyData(1);
			$this->data['company_data'] = $company;
			$this->data['products'] = $this->model_products->getActiveProductData();

			$this->render_template('requests/create', $this->data);
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
		if ($product_id) {
			$product_data = $this->model_products->getProductData($product_id);
			echo json_encode($product_data);
		}
	}

	/*
	 * It gets the all the active product inforamtion from the product table 
	 * This function is used in the request page, for the product selection in the table
	 * The response is return on the json format.
	 */
	public function getTableProductRow()
	{
		$products = $this->model_products->getActiveProductData();
		echo json_encode($products);
	}

	/*
	 * If the validation is not valid, then it redirects to the edit requests page 
	 * If the validation is successfully then it updates the data into the database 
	 * and it stores the operation message into the session flashdata and display on the manage group page
	 */
	public function update($id)
	{
		if (!in_array('updateRequest', $this->permission)) {
			redirect('dashboard', 'refresh');
		}

		if (!$id) {
			redirect('dashboard', 'refresh');
		}

		$this->data['page_title'] = 'Update Request';

		$this->form_validation->set_rules('product[]', 'Product name', 'trim|required');


		if ($this->form_validation->run() == TRUE) {

			$update = $this->model_requests->update($id);

			if ($update == true) {
				$this->session->set_flashdata('success', 'Successfully updated');
				redirect('requests/update/' . $id, 'refresh');
			} else {
				$this->session->set_flashdata('errors', 'Error occurred!!');
				redirect('requests/update/' . $id, 'refresh');
			}
		} else {
			// false case
			$company = $this->model_company->getCompanyData(1);
			$this->data['company_data'] = $company;
			$result = array();
			$requests_data = $this->model_requests->getRequestsData($id);

			$result['request'] = $requests_data;
			$requests_item = $this->model_requests->getRequestsItemData($requests_data['id']);

			foreach ($requests_item as $k => $v) {
				$result['request_item'][] = $v;
			}

			$this->data['request_data'] = $result;

			$this->data['products'] = $this->model_products->getActiveProductData();

			$this->render_template('requests/edit', $this->data);
		}
	}

	/*
	 * It removes the data from the database
	 * and it returns the response into the json format
	 */
	public function remove()
	{
		if (!in_array('deleteRequest', $this->permission)) {
			redirect('dashboard', 'refresh');
		}

		$request_id = $this->input->post('request_id');

		$response = array();
		if ($request_id) {
			$delete = $this->model_requests->remove($request_id);
			if ($delete == true) {
				$response['success'] = true;
				$response['messages'] = "Successfully removed";
			} else {
				$response['success'] = false;
				$response['messages'] = "Error in the database while removing the product information";
			}
		} else {
			$response['success'] = false;
			$response['messages'] = "Refersh the page again!!";
		}

		echo json_encode($response);
	}

	/*
	 * It gets the product id and fetch the request data. 
	 * The request print logic is done here 
	 */
	public function printDiv($id)
	{
		if (!in_array('viewRequest', $this->permission)) {
			redirect('dashboard', 'refresh');
		}

		if ($id) {
			$request_data = $this->model_requests->getRequestsData($id);
			$requests_items = $this->model_requests->getRequestsItemData($id);
			$company_info = $this->model_company->getCompanyData(1);
			$request_date = date('d/m/Y', $request_data['date_time']);
			$request_status = ($request_data['request_status'] == 1) ? "Paid" : "Unpaid";

			$html = '<!-- Main content -->
			<!DOCTYPE html>
			<html>
			<head>
			  <meta charset="utf-8">
			  <meta http-equiv="X-UA-Compatible" content="IE=edge">
			  <title>AdminLTE 2 | Invoice</title>
			  <!-- Tell the browser to be responsive to screen width -->
			  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
			  <!-- Bootstrap 3.3.7 -->
			  <link rel="stylesheet" href="' . base_url('assets/bower_components/bootstrap/dist/css/bootstrap.min.css') . '">
			  <!-- Font Awesome -->
			  <link rel="stylesheet" href="' . base_url('assets/bower_components/font-awesome/css/font-awesome.min.css') . '">
			  <link rel="stylesheet" href="' . base_url('assets/dist/css/AdminLTE.min.css') . '">
			</head>
			<body onload="window.print();">
			
			<div class="wrapper">
			  <section class="invoice">
			    <!-- title row -->
			    <div class="row">
			      <div class="col-xs-12">
			        <h2 class="page-header">
			          ' . $company_info['company_name'] . '
			          <small class="pull-right">Date: ' . $request_date . '</small>
			        </h2>
			      </div>
			      <!-- /.col -->
			    </div>
			    <!-- info row -->
			    <div class="row invoice-info">
			      
			      <div class="col-sm-4 invoice-col">
			        
			        <b>Bill ID:</b> ' . $request_data['bill_no'] . '<br>
			        <b>Name:</b> ' . $request_data['customer_name'] . '<br>
			        <b>Address:</b> ' . $request_data['customer_address'] . ' <br />
			        <b>Phone:</b> ' . $request_data['customer_phone'] . '
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
			            <th>Product name</th>
			            <th>Qty</th>
			          </tr>
			          </thead>
			          <tbody>';

			foreach ($requests_items as $k => $v) {

				$product_data = $this->model_products->getProductData($v['product_id']);

				$html .= '<tr>
				            <td>' . $product_data['name'] . '</td>
				            <td>' . $v['qty'] . '</td>
			          	</tr>';
			}

			$html .= '</tbody>
			        </table>
			      </div>
			      <!-- /.col -->
			    </div>
			    <!-- /.row -->

			    <div class="row">
			      
			      <div class="col-xs-6 pull pull-right">

			        <div class="table-responsive">
			          <table class="table">
			            
			            <tr>
			              <th>Request Status:</th>
			              <td>' . $request_status . '</td>
			            </tr>
			          </table>
			        </div>
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
