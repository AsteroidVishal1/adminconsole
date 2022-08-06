<?php

class Model_requests extends CI_Model
{
  public function __construct()
  {
    parent::__construct();
  }

  /* get the requests data */
  public function getRequestsData($id = null)
  {
    if ($id) {
      $sql = "SELECT * FROM requests WHERE id = ?";
      $query = $this->db->query($sql, array($id));
      return $query->row_array();
    }

    $sql = "SELECT * FROM requests ORDER BY id DESC";
    $query = $this->db->query($sql);
    return $query->result_array();
  }

  // get the requests item data
  public function getRequestsItemData($request_id = null)
  {
    if (!$request_id) {
      return false;
    }

    $sql = "SELECT * FROM requests_item WHERE request_id = ?";
    $query = $this->db->query($sql, array($request_id));
    return $query->result_array();
  }

  // get the requests item data
  public function getProductId($request_id = null)
  {
    if (!$request_id) {
      return false;
    }

    $sql = "SELECT product_id, qty FROM requests_item WHERE request_id = ?";
    $query = $this->db->query($sql, array($request_id));
    return $query->result_array();
  }

  public function create()
  {
    $user_id = $this->session->userdata('id');
    $data = array(
      'user_id' => $user_id,
      'date_time' => strtotime(date('Y-m-d h:i:s a')),
      'request_status' => 'pending'
    );

    $this->db->insert('requests', $data);
    $request_id = $this->db->insert_id();

    $this->load->model('model_products');

    $count_product = count($this->input->post('product'));
    for ($x = 0; $x < $count_product; $x++) {
      $items = array(
        'request_id' => $request_id,
        'product_id' => $this->input->post('product')[$x],
        'qty' => $this->input->post('qty')[$x],
      );

      $this->db->insert('requests_item', $items);

    // now decrease the stock from the product
    // $product_data = $this->model_products->getProductData($this->input->post('product')[$x]);
    // $qty = (int) $product_data['qty'] - (int) $this->input->post('qty')[$x];

    // $update_product = array('qty' => $qty);


    // $this->model_products->update($update_product, $this->input->post('product')[$x]);
    }

    return ($request_id) ? $request_id : false;
  }

  public function countRequestItem($request_id)
  {
    if ($request_id) {
      $sql = "SELECT * FROM requests_item WHERE request_id = ?";
      $query = $this->db->query($sql, array($request_id));
      return $query->num_rows();
    }
  }

  public function update($id)
  {
    if ($id) {
      $user_id = $this->session->userdata('id');
      // fetch the request data 

      $data = array(
        'user_id' => $user_id,
        'request_status' => $this->input->post('request_status')
      );

      $this->db->where('id', $id);
      $update = $this->db->update('requests', $data);

      // now the request item 
      // first we will replace the product qty to original and subtract the qty again
      if ($this->input->post('request_status') == 'approved') {
        $this->load->model('model_products');
        $get_request_item = $this->getRequestsItemData($id);
        foreach ($get_request_item as $k => $v) {
          $product_id = $v['product_id'];
          $qty = $v['qty'];
          // get the product 
          $product_data = $this->model_products->getProductData($product_id);
          $update_qty = $product_data['qty'] - $qty;
          $update_product_data = array('qty' => $update_qty);

          // update the product qty
          $this->model_products->update($update_product_data, $product_id);
        }
      }

      // now remove the request item data 
      // $this->db->where('request_id', $id);
      // $this->db->delete('requests_item');

      // now decrease the product qty
      // $count_product = count($this->input->post('product'));
      // for ($x = 0; $x < $count_product; $x++) {
      //   $items = array(
      //     'request_id' => $id,
      //     'product_id' => $this->input->post('product')[$x],
      //     'qty' => $this->input->post('qty')[$x],
      //     'rate' => $this->input->post('rate_value')[$x],
      //     'amount' => $this->input->post('amount_value')[$x],
      //   );
      //   $this->db->insert('requests_item', $items);

      //   // now decrease the stock from the product
      //   $product_data = $this->model_products->getProductData($this->input->post('product')[$x]);
      //   $qty = (int) $product_data['qty'] - (int) $this->input->post('qty')[$x];

      //   $update_product = array('qty' => $qty);
      //   $this->model_products->update($update_product, $this->input->post('product')[$x]);
      // }

      return true;
    }
  }
  public function remove($id)  {
    if ($id) {
      $this->db->where('id', $id);
      $delete = $this->db->delete('requests');

      $this->db->where('request_id', $id);
      $delete_item = $this->db->delete('requests_item');
      return ($delete == true && $delete_item) ? true : false;
    }  }

// public function countTotalPaidRequests()
// {
//   $sql = "SELECT * FROM requests WHERE request_status = ?";
//   $query = $this->db->query($sql, array(1));
//   return $query->num_rows();
// }
}