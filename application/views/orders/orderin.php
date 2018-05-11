

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Laundry Return
      <small>Lists</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Laundry Return List</li>
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

        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Laundry Return List</h3>
          </div>
          <!-- /.box-header -->
          <div class="box-body">
            <table id="manageTable" class="table table-bordered table-striped">
              <!-- <thead>
              <tr>
                <th>Bill no</th>
                <th>Date Time</th>
                <th>Total Products</th>
                <th>Status</th>
                <!?php if(in_array('updateOrder', $user_permission) || in_array('viewOrder', $user_permission) || in_array('deleteOrder', $user_permission)): ?>
                  <th>Action</th>
                <!?php endif; ?>
              </tr>
              </thead> -->

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
<?php endif; ?>



<script type="text/javascript">
var manageTable;
var base_url = "<?php echo base_url(); ?>";

$(document).ready(function() {

  $("#mainOrdersNav").addClass('active');
  $("#manageOrdersNav").addClass('active');
  var tempJSON = [];

  var dom = "";
  var action = <?=$action;?>;
  if(action == 1){
    dom = "<'row'>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5 p-t-12'B><'col-sm-7'>>";
  }else if(action == 0){
    dom =  "<'row'>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5 p-t-12'i><'col-sm-7'p>>"; 
  }
  



  // initialize the datatable
  manageTable = $('#manageTable').DataTable({
    // 'ajax': base_url + 'orders/fetchOrdersDataIn',
    dom: dom,
    buttons: [
      {
        text: 'Save',
        className: 'btn',
        enabled: true,
        action: function (e, dt, node, config) {
            // window.open(getBaseURL() + 'itqa/execution-project/'+value.execution_project_id+'/'+value.preparation_test_type_id, '_blank');

            var qty_req = 0;
            var qty_rtn = 0;

            $(tempJSON).each(function(i, v){
              qty_req += parseInt(v.qty);
              qty_rtn += (parseInt(v.qty_remains) + parseInt(v.qty_rtn));
            });

            var data = {
              order_id : <?=$order_id;?>,
              status : (qty_req != qty_rtn) ? '7' : '8',
              objects : tempJSON
            }

            console.log(data);
            
            $.ajax({
              url: "<?php echo base_url('orders/approve') ?>",
              type: "POST",
              data: data,
              dataType: 'json',
              success:function(res) {

                // manageTable.ajax.reload(null, false);

                if(res.success === true) {
                  $("#messages").html('<div class="alert alert-success alert-dismissible" role="alert">'+
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
                    '<strong> <span class="glyphicon glyphicon-ok-sign"></span> </strong>'+res.messages+
                  '</div>');

                  // hide the modal
                  // $("#finalNotifModal").modal('hide');
                  window.location = '<?=base_url('orders') ?>';
                } else {

                  $("#messages").html('<div class="alert alert-warning alert-dismissible" role="alert">'+
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
                    '<strong> <span class="glyphicon glyphicon-exclamation-sign"></span> </strong>'+res.messages+
                  '</div>');
                }
              }
            });


        }
      }
    ],
    processing: true,
    serverSide: true,
    ajax: function (data, callback, settings) {
          data.status = null;
          data.order_id = <?=$order_id;?>;
          // console.log(data)
        $.ajax({
          url: "<?php echo base_url('orders/getOrderItemByIdOrder/') ?>",
          type: "POST",
          data: data,
          dataType: 'json',
          success:function(response) {
            // console.log(response);

            if(tempJSON.length == 0){
              tempJSON = response.data;
            }
            response.data = tempJSON;



            setTimeout(function () {
              callback(response);
            }, 50);
          // manageTable.ajax.reload(null, false);
          }
        });

    },
    columnDefs: [
      {title: 'Item', className: "dt-head-center", "targets": 0, "width": "60%", "orderable": false},
      {title: 'Qty Request', className: "dt-head-center", "targets": 1, "width": "10%", "orderable": false},
      {title: 'Qty Returned', className: "dt-head-center", "targets": 2, "width": "10%", "orderable": false, visible: false},
      {title: 'Qty Remains', className: "dt-head-center", "targets": 3, "width": "10%", "orderable": false},
      {title: 'Qty Return', className: "dt-head-center", "targets": 4, "width": "10%", "orderable": false},
      {title: 'Order Id', className: "dt-head-center", "targets": 5, "width": "10%", "orderable": false, visible: false},
      {title: 'Return Id', className: "dt-head-center", "targets": 6, "width": "10%", "orderable": false, visible: false}
    ],
    columns: [
      {
        "data": "name"
      },
      {
        "data": "qty"
      },
      {
        "data": "qty_rtn"
      }, 
      {
        "data": "qty_remains"
      }, {
        "data": "qty_remains",
        "render": function (data, type, row) {
          if(action == 1){
              return '<input type="text" class="form-control" value="'+data+'" alt="'+(JSON.stringify(row)).replace(/"/g, "&quot;")+'"/>';        
          }else if(action == 0){
            return row.qty_rtn;
          }
        }
      },
      {
        "data": "order_id"
      },

      {
        "data": "id"
      }
    ],
    "rowCallback": function( row, data, index ) {
      console.log(data);
      $(row).find('.form-control').autoNumeric('init', {vMin: '0', vMax: data.qty_remains });
    },
    drawCallback: function (settings) {
       // $(selector).autoNumeric('init', {vMin: '0', vMax: '999999999' });
      $('#manageTable').find('.form-control').off('keyup change').on('keyup change', function(e){
        var value = $(this).val();
        var alt = JSON.parse($(this).attr('alt'));
        alt.qty_remains = ((value != '') ? value : "0");
        $(this).val(alt.qty_remains);

        // console.log(tempJSON);
        if(tempJSON.length > 0){
          var tempIndex = -1;
          $(tempJSON).each(function(i, v){
            if(v.name == alt.name){
              tempIndex = i;
            }
          });

          if(tempIndex > -1){
            tempJSON.splice(tempIndex, 1);  
          }
          
          tempJSON.push(alt);
        }else{
          tempJSON.push(alt);
        }
        
      })
    },
    "order": [[ 0, "asc" ]],
    // "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
    "lengthMenu": [[-1], ["All"]],
    "pageLength": -1
  });

});

function notifFunc(id,status,orderid)
{
  var v_notiftext = "";
  var v_nobtn = "";
  var v_yesbtn = "";
  switch(status){
    case '7':
      document.getElementById("notiftext").innerHTML = "Did you receive "+orderid+" from Laundry ?";
      document.getElementById("nobtn").innerHTML = "NO";
      document.getElementById("nobtn").setAttribute( "data-dismiss", "modal");
      document.getElementById("yesbtn").innerHTML = "YES";
      document.getElementById("yesbtn").setAttribute( "onClick", "approvalFunc("+id+",'8')" );
      $("#notifModal").modal("show");
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
            // $("#removeModal").modal('hide');

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

          if(response.success === true) {
            $("#messages").html('<div class="alert alert-success alert-dismissible" role="alert">'+
              '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
              '<strong> <span class="glyphicon glyphicon-ok-sign"></span> </strong>'+response.messages+
            '</div>');

            // hide the modal
            // $("#notifModal").modal('hide');

            

          } else {

            $("#messages").html('<div class="alert alert-warning alert-dismissible" role="alert">'+
              '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
              '<strong> <span class="glyphicon glyphicon-exclamation-sign"></span> </strong>'+response.messages+
            '</div>');
          }

          window.location = '<?=base_url('orders') ?>';
        }
      });

      return false;
  }
}

</script>
