<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Manage
      <small>Requests</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Requests</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <!-- Small boxes (Stat box) -->
    <div class="row">
      <div class="col-md-12 col-xs-12">

        <div id="messages"></div>

        <?php if ($this->session->flashdata('success')) : ?>
          <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <?php echo $this->session->flashdata('success'); ?>
          </div>
        <?php elseif ($this->session->flashdata('error')) : ?>
          <div class="alert alert-error alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <?php echo $this->session->flashdata('error'); ?>
          </div>
        <?php endif; ?>


        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Edit Request</h3>
          </div>
          <!-- /.box-header -->
          <form role="form" action="<?php base_url('requests/update') ?>" method="post" class="form-horizontal">
            <div class="box-body">

              <?php echo validation_errors(); ?>

              <div class="form-group">
                <label for="date" class="col-sm-12 control-label">Date: <?php echo date('Y-m-d') ?></label>
              </div>
              <div class="form-group">
                <label for="time" class="col-sm-12 control-label">Date: <?php echo date('h:i a') ?></label>
              </div>

              <div class="col-md-4 col-xs-12 pull pull-left">

                <table class="table table-bordered" id="product_info_table">
                  <thead>
                    <tr>
                      <th style="width:50%">Product</th>
                      <th style="width:30%">Qty</th>
                      <th style="width:10%"><button type="button" id="add_row" class="btn btn-default"><i class="fa fa-plus"></i></button></th>
                    </tr>
                  </thead>

                  <tbody>

                    <?php if (isset($request_data['request_item'])) : ?>
                      <?php $x = 1; ?>
                      <?php foreach ($request_data['request_item'] as $key => $val) : ?>
                        <?php //print_r($v); 
                        ?>
                        <tr id="row_<?php echo $x; ?>">
                          <td>
                            <select class="form-control select_group product" data-row-id="row_<?php echo $x; ?>" id="product_<?php echo $x; ?>" name="product[]" style="width:100%;" onchange="getProductData(<?php echo $x; ?>)" required>
                              <option value=""></option>
                              <?php foreach ($products as $k => $v) : ?>
                                <option value="
                                <?php
                                echo $v['id']
                                ?>" <?php
                                    if ($val['product_id'] == $v['id']) {
                                      echo "selected='selected'";
                                    } ?>>
                                  <?php echo $v['name'] ?></option>
                              <?php endforeach ?>
                            </select>
                          </td>
                          <td><input type="number" name="qty[]" id="qty_<?php echo $x; ?>" class="form-control" required onkeyup="getTotal(<?php echo $x; ?>)" value="<?php echo $val['qty'] ?>" autocomplete="off"></td>
                          <td><button type="button" class="btn btn-default" onclick="removeRow('<?php echo $x; ?>')"><i class="fa fa-close"></i></button></td>
                        </tr>
                        <?php $x++; ?>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>

                <br /> <br />
              </div>
              <!-- /.box-body -->

              <div class="box-footer">
                <a target="__blank" href="<?php echo base_url() . 'requests/printDiv/' . $request_data['request']['id'] ?>" class="btn btn-default">Print</a>
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="<?php echo base_url('requests/') ?>" class="btn btn-warning">Back</a>
              </div>
          </form>
          <!-- /.box-body -->
        </div>
        <!-- /.box -->
      </div>
      <!-- col-md-12 -->
    </div>
    <!-- /.row -->


  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<script type="text/javascript">
  var base_url = "<?php echo base_url(); ?>";

  // function printRequest(id)
  // {
  //   if(id) {
  //     $.ajax({
  //       url: base_url + 'requests/printDiv/' + id,
  //       type: 'post',
  //       success:function(response) {
  //         var mywindow = window.open('', 'new div', 'height=400,width=600');
  //         // mywindow.document.write('<html><head><title></title>');
  //         // mywindow.document.write('<link rel="stylesheet" href="<?php //echo base_url('assets/bower_components/bootstrap/dist/css/bootstrap.min.css') 
                                                                      ?>" type="text/css" />');
  //         // mywindow.document.write('</head><body >');
  //         mywindow.document.write(response);
  //         // mywindow.document.write('</body></html>');

  //         mywindow.print();
  //         mywindow.close();

  //         return true;
  //       }
  //     });
  //   }
  // }

  $(document).ready(function() {
    $(".select_group").select2();
    // $("#description").wysihtml5();

    $("#mainRequestsNav").addClass('active');
    $("#manageRequestsNav").addClass('active');

    // Add new row in the table 
    $("#add_row").unbind('click').bind('click', function() {
      var table = $("#product_info_table");
      var count_table_tbody_tr = $("#product_info_table tbody tr").length;
      var row_id = count_table_tbody_tr + 1;

      $.ajax({
        url: base_url + '/requests/getTableProductRow/',
        type: 'post',
        dataType: 'json',
        success: function(response) {

          // console.log(reponse.x);
          var html = '<tr id="row_' + row_id + '">' +
            '<td>' +
            '<select class="form-control select_group product" data-row-id="' + row_id + '" id="product_' + row_id + '" name="product[]" style="width:100%;" onchange="getProductData(' + row_id + ')">' +
            '<option value=""></option>';
          $.each(response, function(index, value) {
            html += '<option value="' + value.id + '">' + value.name + '</option>';
          });

          html += '</select>' +
            '</td>' +
            '<td><input type="number" name="qty[]" id="qty_' + row_id + '" class="form-control" onkeyup="getTotal(' + row_id + ')"></td>' +
            '<td><button type="button" class="btn btn-default" onclick="removeRow(\'' + row_id + '\')"><i class="fa fa-close"></i></button></td>' +
            '</tr>';

          if (count_table_tbody_tr >= 1) {
            $("#product_info_table tbody tr:last").after(html);
          } else {
            $("#product_info_table tbody").html(html);
          }

          $(".product").select2();

        }
      });

      return false;
    });

  }); // /document

  // get the product information from the server
  function getProductData(row_id) {
    var product_id = $("#product_" + row_id).val();
    if (product_id == "") {
      $("#qty_" + row_id).val("");
    } else {
      $.ajax({
        url: base_url + 'requests/getProductValueById',
        type: 'post',
        data: {
          product_id: product_id
        },
        dataType: 'json',
        success: function(response) {
          $("#qty_" + row_id).val(1);
          $("#qty_value_" + row_id).val(1);
        } // /success
      }); // /ajax function to fetch the product data 
    }
  }

  function removeRow(tr_id) {
    $("#product_info_table tbody tr#row_" + tr_id).remove();
  }
</script>