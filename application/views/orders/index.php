

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Laundry Out
      <small>Lists</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Laundry Request List</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <!-- Small boxes (Stat box) -->
    <div class="row">
      <div class="col-md-12 col-xs-12">

        <div id="messages"></div>

        <?php if($this->session->flashdata('success')): ?>
          <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <?php echo $this->session->flashdata('success'); ?>
          </div>
        <?php elseif($this->session->flashdata('error')): ?>
          <div class="alert alert-error alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <?php echo $this->session->flashdata('error'); ?>
          </div>
        <?php endif; ?>

        <?php if(in_array('createOrder', $user_permission)): ?>
          <a href="<?php echo base_url('orders/create') ?>" class="btn btn-primary">+ Laundry Request</a>
          <br /> <br />
        <?php endif; ?>

        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Laundry Request List</h3>
          </div>
          <!-- /.box-header -->
          <div class="box-body">
            <table id="manageTable" class="table table-bordered table-striped">
              <thead>
              <tr>
                <th>Bill no</th>
                <th>Date Time</th>
                <th>Total Products</th>
                <th>Total Amount</th>
                <th>Status</th>
                <?php if(in_array('updateOrder', $user_permission) || in_array('viewOrder', $user_permission) || in_array('deleteOrder', $user_permission)): ?>
                  <th>Action</th>
                <?php endif; ?>
              </tr>
              </thead>

            </table>
          </div>
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

<?php if(in_array('deleteOrder', $user_permission)): ?>
<!-- remove brand modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="removeModal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Remove Order</h4>
      </div>

      <form role="form" action="<?php echo base_url('orders/remove') ?>" method="post" id="removeForm">
        <div class="modal-body">
          <p>Do you really want to remove?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
      </form>


    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php endif; ?>

<?php if(in_array('deleteOrder', $user_permission)): ?>
<!-- remove brand modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="notifModal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Notification</h4>
      </div>
        <div class="modal-body">
          <p id="notiftext">Do you really want to approve?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" id="nobtn" onclick="#">Reject</button>
          <button type="button" class="btn btn-success" id="yesbtn" onclick="#">Approve</button>
        </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<div class="modal fade" tabindex="-1" role="dialog" id="finalNotifModal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Notification</h4>
      </div>
        <div class="modal-body">
          <p id="notiftext">Do you really want to approve?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger">Reject</button>
          <button type="button" class="btn btn-success">Approve</button>
        </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<?php endif; ?>



<script type="text/javascript">
var manageTable;
var base_url = "<?php echo base_url(); ?>";

$(document).ready(function() {

  $("#mainOrdersNav").addClass('active');
  $("#manageOrdersNav").addClass('active');

  // initialize the datatable
  manageTable = $('#manageTable').DataTable({
    'ajax': base_url + 'orders/fetchOrdersData',
    'order': []
  });

});

function notifFunc(id,status,orderid)
{
  var v_notiftext = "";
  var v_nobtn = "";
  var v_yesbtn = "";
  switch(status){
    case '1':
      document.getElementById("notiftext").innerHTML = "Are you sure to Approve Request Approval "+orderid+" ?";
      document.getElementById("nobtn").innerHTML = "Reject";
      document.getElementById("nobtn").setAttribute( "onClick", "approvalFunc("+id+",'3')" );
      document.getElementById("yesbtn").innerHTML = "Approve";
      document.getElementById("yesbtn").setAttribute( "onClick", "approvalFunc("+id+",'2')" );
      $("#notifModal").modal("show");
      break;
    case '2':
      document.getElementById("notiftext").innerHTML = "Send "+orderid+" to Laundry ?";
      document.getElementById("nobtn").innerHTML = "No";
      document.getElementById("nobtn").setAttribute( "data-dismiss", "modal");
      document.getElementById("yesbtn").innerHTML = "Yes";
      document.getElementById("yesbtn").setAttribute( "onClick", "approvalFunc("+id+",'4')" );
      $("#notifModal").modal("show");
      break;
    case '4':
      document.getElementById("notiftext").innerHTML = "Did you receive "+orderid+" from client ?";
      document.getElementById("nobtn").innerHTML = "No";
      document.getElementById("nobtn").setAttribute( "data-dismiss", "modal");
      document.getElementById("yesbtn").innerHTML = "Yes";
      document.getElementById("yesbtn").setAttribute( "onClick", "approvalFunc("+id+",'5')" );
      $("#notifModal").modal("show");
      break;
    case '5':
      //window.open("<?=base_url('orders/done/')?>"+id);
      document.getElementById("notiftext").innerHTML = "Are request "+orderid+" completed ?";
      document.getElementById("nobtn").innerHTML = "No";
      document.getElementById("nobtn").setAttribute( "data-dismiss", "modal");
      document.getElementById("yesbtn").innerHTML = "Yes";
      document.getElementById("yesbtn").setAttribute( "onClick", "approvalFunc("+id+",'6')" );
      $("#notifModal").modal("show");
      break;
    case '6':
      // console.log('Hello World!');
      // console.log(id);
      //window.open("<!?=base_url('orders/done/')?>"+id);
      // document.getElementById("notiftext").innerHTML = "Do you want to return "+orderid+" ?";
      // document.getElementById("nobtn").innerHTML = "No";
      // document.getElementById("nobtn").setAttribute( "data-dismiss", "modal");
      // document.getElementById("yesbtn").innerHTML = "Yes";
      // document.getElementById("yesbtn").setAttribute( "onClick", "approvalFunc("+id+",'7')" );
      // $("#notifModal").modal("show");
      fetchFunc(id);
      //console.log(orderid);
      break;
    case '7':
      // console.log('Hello World!');
      // console.log(id);
      //window.open("<!?=base_url('orders/done/')?>"+id);
      // document.getElementById("notiftext").innerHTML = "Do you want to return "+orderid+" ?";
      // document.getElementById("nobtn").innerHTML = "No";
      // document.getElementById("nobtn").setAttribute( "data-dismiss", "modal");
      // document.getElementById("yesbtn").innerHTML = "Yes";
      // document.getElementById("yesbtn").setAttribute( "onClick", "approvalFunc("+id+",'7')" );
      // $("#notifModal").modal("show");
      fetchFunc(id);
      //console.log(orderid);
      break;
  }
}

// remove functions
function removeFunc(id)
{
  if(id) {
    $("#removeForm").on('submit', function() {

      var form = $(this);

      // remove the text-danger
      $(".text-danger").remove();

      $.ajax({
        url: form.attr('action'),
        type: form.attr('method'),
        data: { order_id:id },
        dataType: 'json',
        success:function(response) {

          manageTable.ajax.reload(null, false);

          if(response.success === true) {
            $("#messages").html('<div class="alert alert-success alert-dismissible" role="alert">'+
              '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
              '<strong> <span class="glyphicon glyphicon-ok-sign"></span> </strong>'+response.messages+
            '</div>');

            // hide the modal
            $("#removeModal").modal('hide');

          } else {

            $("#messages").html('<div class="alert alert-warning alert-dismissible" role="alert">'+
              '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
              '<strong> <span class="glyphicon glyphicon-exclamation-sign"></span> </strong>'+response.messages+
            '</div>');
          }
        }
      });

      return false;
    });
  }
}


function fetchFunc(id){
  // console.log(id);
  if(id) {
      // remove the text-danger
      // $(".text-danger").remove();
      window.location = '<?=base_url('orders/orderin') ?>?order_id='+id+'&action=1';
      // $.ajax({
      //   url: "<!?php echo base_url('orders/getOrderItemByIdOrder/') ?>",
      //   type: "GET",
      //   data: { order_id:id },
      //   dataType: 'json',
      //   success:function(response) {
      //     console.log(response);
      //     // manageTable.ajax.reload(null, false);
      //     var markup = '';
      //     var qty_req = 0; 
      //     $(response).each(function(i, e){
      //       qty_req += parseInt(e.qty_req);
      //       markup += '<div class = "row">';
            
      //       markup += '<div class = "col-lg-3 hide">';
      //       markup += '<div class="form-group">';
      //       markup += '<label>order_item_id:</label>';
      //       markup += '<input type="name" name="products_id" readOnly="true" class="form-control" value="'+e.products_id+'"/>';
      //       markup += '</div>';//form-group
      //       markup += '</div>';//col-lg-3

      //       markup += '<div class = "col-lg-3 hide">';
      //       markup += '<div class="form-group">';
      //       markup += '<label>order_item_id:</label>';
      //       markup += '<input type="name" name="order_item_id" readOnly="true" class="form-control" value="'+e.id+'"/>';
      //       markup += '</div>';//form-group
      //       markup += '</div>';//col-lg-3


      //       markup += '<div class = "col-lg-3">';
      //       markup += '<div class="form-group">';
      //       markup += '<label>Product:</label>';
      //       markup += '<input type="name" name="products_name" readOnly="true" class="form-control" value="'+e.name+'"/>';
      //       markup += '</div>';//form-group
      //       markup += '</div>';//col-lg-3

      //       markup += '<div class = "col-lg-3">';
      //       markup += '<div class="form-group">';
      //       markup += '<label>QTY:</label>';
      //       markup += '<input type="name" name="qty_rtn" class="form-control" value="'+e.qty+'"/>';
      //       markup += '</div>';//form-group
      //       markup += '</div>';//col-lg-3

      //       markup += '<div class = "col-lg-6">';
      //       markup += '<div class="form-group">';
      //       markup += '<label>Notes:</label>';
      //       markup += '<input type="name" name="description" class="form-control" value="'+e.note+'"/>';
      //       markup += '</div>';//form-group
      //       markup += '</div>';//col-lg-3

      //       markup += '</div>';//row
      //     });
      //     $("#finalNotifModal .modal-body").html(markup);
      //     $("#finalNotifModal").modal("show");
      //     $('#finalNotifModal .btn-success').off('click').on('click', function(e){
      //       var objects = [];
      //       var qty_rtn = 0;
      //       $("#finalNotifModal .modal-body .row").each(function(i, e){
      //         qty_rtn += parseInt($(this).find('input[name="qty_rtn"]').val());
      //         objects.push({
      //           products_id : $(this).find('input[name="products_id"]').val(),
      //           order_item_id : $(this).find('input[name="order_item_id"]').val(),
      //           products_name : $(this).find('input[name="products_name"]').val(),
      //           qty_rtn : $(this).find('input[name="qty_rtn"]').val(),
      //           description : $(this).find('input[name="description"]').val()
      //         });
      //       });
      //       var data = {
      //         order_id : id,
      //         status : (qty_req != qty_rtn) ? '7' : '8',
      //         objects : objects
      //       }
            
      //       $.ajax({
      //         url: "<!?php echo base_url('orders/approve') ?>",
      //         type: "POST",
      //         data: data,
      //         dataType: 'json',
      //         success:function(res) {

      //           manageTable.ajax.reload(null, false);

      //           if(res.success === true) {
      //             $("#messages").html('<div class="alert alert-success alert-dismissible" role="alert">'+
      //               '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
      //               '<strong> <span class="glyphicon glyphicon-ok-sign"></span> </strong>'+res.messages+
      //             '</div>');

      //             // hide the modal
      //             $("#finalNotifModal").modal('hide');

      //           } else {

      //             $("#messages").html('<div class="alert alert-warning alert-dismissible" role="alert">'+
      //               '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
      //               '<strong> <span class="glyphicon glyphicon-exclamation-sign"></span> </strong>'+res.messages+
      //             '</div>');
      //           }
      //         }
      //       });

      //       return false;




      //     });
      //   }
      // });

      
  }
}

// approval functions
function approvalFunc(id,status)
{
  if(id) {
      // remove the text-danger
      $(".text-danger").remove();

      $.ajax({
        url: "<?php echo base_url('orders/approve') ?>",
        type: "POST",
        data: { order_id:id, status:status },
        dataType: 'json',
        success:function(response) {

          manageTable.ajax.reload(null, false);

          if(response.success === true) {
            $("#messages").html('<div class="alert alert-success alert-dismissible" role="alert">'+
              '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
              '<strong> <span class="glyphicon glyphicon-ok-sign"></span> </strong>'+response.messages+
            '</div>');

            // hide the modal
            $("#notifModal").modal('hide');

          } else {

            $("#messages").html('<div class="alert alert-warning alert-dismissible" role="alert">'+
              '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
              '<strong> <span class="glyphicon glyphicon-exclamation-sign"></span> </strong>'+response.messages+
            '</div>');
          }
        }
      });

      return false;
  }
}

</script>
