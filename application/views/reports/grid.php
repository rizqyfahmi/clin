

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Reports
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Reports</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- Small boxes (Stat box) -->
      <div class="row">

        <div class="col-md-12 col-xs-12">
          <form class="form-inline" action="<?php echo base_url('reports/grid') ?>" method="get">
            <div class="form-group">
              <label for="date">Month</label>
              <select class="form-control" name="q" id="select_year">
                <?php foreach ($date_group as $key => $value): ?>
                  <?=$value['month'];?>
                <?php endforeach ?>
              </select>
            </div>
            <button type="submit" class="btn btn-default">Submit</button>
          </form>
        </div>

        <br /> <br />


        <div class="col-md-12 col-xs-12">

          
          <!-- /.box -->
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Total Paid Orders - Report Data</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body" style="overflow-y: auto">
              <table id="datatables" class="table table-bordered table-striped">
                <tr>
                  <th rowspan="2">Products</th>
                  <?php foreach ($days as $k => $v): ?>
                    <?php if ($v['is_weekend'] == 1) : ?>
                        <th colspan="3" class="bg-danger text-center"><?=$v['date']; ?></th>
                      <?php else : ?>
                        <th colspan="3" class="text-center"><?=$v['date']; ?></th>
                      <?php endif; ?>
                  <?php endforeach ?> 
                </tr>
                <tr>
                  <?php foreach ($days as $k => $v): ?>
                    <th>Req</th> 
                    <th>Rtn</th> 
                    <th>Rmn</th> 
                  <?php endforeach ?> 
                </tr>
                
                <?php foreach ($products as $l => $w): ?>
                <tr>
                  <th><?=$w['name']; ?></th>
                  <?php foreach ($days as $k => $v): ?>
                    <?php if(count($v['product']) > 0) : ?>
                      <?php foreach ($v['product'] as $m => $x): ?>
                        <?php if ($w['id'] == $x['item_id']) : ?>
                          <td class="bg-success"><center><?=$x['qty_req']; ?></center></td>
                          <td class="bg-success"><center><?=$x['qty_rtn']; ?></center></td>
                          <td class="bg-success"><center><?=$x['qty_remains']; ?></center></td>
                        <?php endif ?>
                      <?php endforeach ?> 
                    <?php else : ?>
                        <td><center>0</center></td>
                        <td><center>0</center></td>
                        <td><center>0</center></td>
                    <?php endif; ?>
                  <?php endforeach ?> 
                    
                  
                </tr>
                <?php endforeach ?> 
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

  <script type="text/javascript">

    $(document).ready(function() {
      $("#reportNav").addClass('active');
    }); 

    var report_data = <?php echo '[' . implode(',', $results) . ']'; ?>;
    

    $(function () {
    /* ChartJS
     * -------
     * Here we will create a few charts using ChartJS
     */
     var areaChartData = {
      labels  : ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
      datasets: [
        {
          label               : 'Electronics',
          fillColor           : 'rgba(210, 214, 222, 1)',
          strokeColor         : 'rgba(210, 214, 222, 1)',
          pointColor          : 'rgba(210, 214, 222, 1)',
          pointStrokeColor    : '#c1c7d1',
          pointHighlightFill  : '#fff',
          pointHighlightStroke: 'rgba(220,220,220,1)',
          data                : report_data
        }
      ]
    }

    //-------------
    //- BAR CHART -
    //-------------
    var barChartCanvas                   = $('#barChart').get(0).getContext('2d')
    var barChart                         = new Chart(barChartCanvas)
    var barChartData                     = areaChartData
    barChartData.datasets[0].fillColor   = '#00a65a';
    barChartData.datasets[0].strokeColor = '#00a65a';
    barChartData.datasets[0].pointColor  = '#00a65a';
    var barChartOptions                  = {
      //Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
      scaleBeginAtZero        : true,
      //Boolean - Whether grid lines are shown across the chart
      scaleShowGridLines      : true,
      //String - Colour of the grid lines
      scaleGridLineColor      : 'rgba(0,0,0,.05)',
      //Number - Width of the grid lines
      scaleGridLineWidth      : 1,
      //Boolean - Whether to show horizontal lines (except X axis)
      scaleShowHorizontalLines: true,
      //Boolean - Whether to show vertical lines (except Y axis)
      scaleShowVerticalLines  : true,
      //Boolean - If there is a stroke on each bar
      barShowStroke           : true,
      //Number - Pixel width of the bar stroke
      barStrokeWidth          : 2,
      //Number - Spacing between each of the X value sets
      barValueSpacing         : 5,
      //Number - Spacing between data sets within X values
      barDatasetSpacing       : 1,
      //String - A legend template
      legendTemplate          : '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].fillColor%>"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>',
      //Boolean - whether to make the chart responsive
      responsive              : true,
      maintainAspectRatio     : true
    }

    barChartOptions.datasetFill = false
    barChart.Bar(barChartData, barChartOptions)
  })
  </script>
