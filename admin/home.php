<?php include 'includes/session.php'; ?>
<?php 
  include '../timezone.php'; 
  $today = date('Y-m-d');
  $year = date('Y');

  if(isset($_GET['year'])){
    $year = $_GET['year'];
  }
?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <div class="content-wrapper">

    <section class="content-header">
      <h1>Dashboard</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Dashboard</li>
      </ol>
    </section>

    <section class="content">

      <?php
        if(isset($_SESSION['error'])){
          echo "<div class='alert alert-danger alert-dismissible'>
            <button type='button' class='close' data-dismiss='alert'>&times;</button>
            <h4><i class='icon fa fa-warning'></i> Error!</h4>
            ".$_SESSION['error']."
          </div>";
          unset($_SESSION['error']);
        }

        if(isset($_SESSION['success'])){
          echo "<div class='alert alert-success alert-dismissible'>
            <button type='button' class='close' data-dismiss='alert'>&times;</button>
            <h4><i class='icon fa fa-check'></i> Success!</h4>
            ".$_SESSION['success']."
          </div>";
          unset($_SESSION['success']);
        }
      ?>

      <div class="row">

        <!-- TOTAL EMPLOYEES -->
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-aqua">
            <div class="inner">
              <?php
                $sql = "SELECT * FROM employees";
                $query = $conn->query($sql);
                echo "<h3>".$query->num_rows."</h3>";
              ?>
              <p>Total Employees</p>
            </div>
            <div class="icon"><i class="ion ion-person-stalker"></i></div>
            <a href="employee.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>

        <!-- ON TIME % (FIXED DIVISION ERROR HERE) -->
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-green">
            <div class="inner">
              <?php
                $sql = "SELECT * FROM attendance";
                $query = $conn->query($sql);
                $total = $query->num_rows;

                $sql = "SELECT * FROM attendance WHERE status = 1";
                $query = $conn->query($sql);
                $early = $query->num_rows;

                // ✅ FIX: prevent division by zero
                $percentage = ($total > 0) ? ($early / $total) * 100 : 0;

                echo "<h3>".number_format($percentage, 2)."<sup style='font-size:20px'>%</sup></h3>";
              ?>
              <p>On Time Percentage</p>
            </div>
            <div class="icon"><i class="ion ion-pie-graph"></i></div>
            <a href="attendance.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>

        <!-- ON TIME TODAY -->
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-yellow">
            <div class="inner">
              <?php
                $sql = "SELECT * FROM attendance WHERE date = '$today' AND status = 1";
                $query = $conn->query($sql);
                echo "<h3>".$query->num_rows."</h3>";
              ?>
              <p>On Time Today</p>
            </div>
            <div class="icon"><i class="ion ion-clock"></i></div>
            <a href="attendance.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>

        <!-- LATE TODAY -->
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-red">
            <div class="inner">
              <?php
                $sql = "SELECT * FROM attendance WHERE date = '$today' AND status = 0";
                $query = $conn->query($sql);
                echo "<h3>".$query->num_rows."</h3>";
              ?>
              <p>Late Today</p>
            </div>
            <div class="icon"><i class="ion ion-alert-circled"></i></div>
            <a href="attendance.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>

      </div>

      <!-- CHART -->
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title">Monthly Attendance Report</h3>

              <div class="box-tools pull-right">
                <form class="form-inline">
                  <div class="form-group">
                    <label>Select Year: </label>
                    <select class="form-control input-sm" id="select_year">
                      <?php
                        for($i=2015; $i<=2065; $i++){
                          $selected = ($i==$year)?'selected':'';
                          echo "<option value='$i' $selected>$i</option>";
                        }
                      ?>
                    </select>
                  </div>
                </form>
              </div>

            </div>

            <div class="box-body">
              <canvas id="barChart" style="height:350px"></canvas>
              <div id="legend" class="text-center"></div>
            </div>

          </div>
        </div>
      </div>

    </section>
  </div>

  <?php include 'includes/footer.php'; ?>
</div>

<?php
  $and = "AND YEAR(date) = $year";

  $months = [];
  $ontime = [];
  $late = [];

  for($m = 1; $m <= 12; $m++){

    $sql = "SELECT * FROM attendance WHERE MONTH(date) = '$m' AND status = 1 $and";
    $oquery = $conn->query($sql);
    array_push($ontime, $oquery->num_rows);

    $sql = "SELECT * FROM attendance WHERE MONTH(date) = '$m' AND status = 0 $and";
    $lquery = $conn->query($sql);
    array_push($late, $lquery->num_rows);

    array_push($months, date('M', mktime(0,0,0,$m,1)));
  }

  $months = json_encode($months);
  $late = json_encode($late);
  $ontime = json_encode($ontime);
?>

<?php include 'includes/scripts.php'; ?>

<script>
$(function(){

  var barChartCanvas = $('#barChart').get(0).getContext('2d');

  var barChartData = {
    labels: <?php echo $months; ?>,
    datasets: [
      {
        label: 'Late',
        fillColor: 'rgba(210,214,222,1)',
        data: <?php echo $late; ?>
      },
      {
        label: 'Ontime',
        fillColor: '#00a65a',
        data: <?php echo $ontime; ?>
      }
    ]
  };

  var barChartOptions = {
    scaleBeginAtZero: true,
    responsive: true,
    maintainAspectRatio: true
  };

  var barChart = new Chart(barChartCanvas);
  var myChart = barChart.Bar(barChartData, barChartOptions);

  document.getElementById('legend').innerHTML = myChart.generateLegend();
});

$('#select_year').change(function(){
  window.location.href = 'home.php?year=' + $(this).val();
});
</script>

</body>
</html>