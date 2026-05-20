<?php

/* =========================================================
   DATE RANGE
========================================================= */

if(!isset($from) || !isset($to)){
    die('Date range not defined.');
}

/* =========================================================
   GLOBAL DEDUCTIONS
========================================================= */

$deductions_query = $conn->query("
    SELECT description, amount
    FROM deductions
");

$global_deductions = [];
$total_global_deduction = 0;

while($row = $deductions_query->fetch_assoc()){
    $global_deductions[] = $row;
    $total_global_deduction += $row['amount'];
}

/* =========================================================
   COLA
========================================================= */

$cola_q = $conn->query("
    SELECT amount, status
    FROM cola
    WHERE id = 1
");

$cola = $cola_q->fetch_assoc();

$cola_enabled = $cola['status'] ?? 0;
$cola_amount  = $cola['amount'] ?? 0;

/* =========================================================
   ATTENDANCE QUERY
========================================================= */

$sql = "
    SELECT attendance.*,
           employees.id AS empid,
           employees.employee_id AS emp_code,
           employees.firstname,
           employees.lastname,
           position.rate

    FROM attendance

    LEFT JOIN employees
        ON employees.id = attendance.employee_id

    LEFT JOIN position
        ON position.id = employees.position_id

    WHERE attendance.date BETWEEN '$from' AND '$to'
";

$query = $conn->query($sql);

/* =========================================================
   BUILD EMPLOYEE + DAILY STRUCTURE
========================================================= */

$employees = [];
$daily = [];

while($row = $query->fetch_assoc()){

    $empid = $row['empid'];
    $date  = $row['date'];

    if(!isset($employees[$empid])){

        $employees[$empid] = [
            'firstname' => $row['firstname'],
            'lastname'  => $row['lastname'],
            'emp_code'  => $row['emp_code'],
            'rate'      => $row['rate'],
            'total_hr'  => 0
        ];
    }

    if(!isset($daily[$empid][$date])){
        $daily[$empid][$date] = 0;
    }

    $daily[$empid][$date] += (float)$row['num_hr'];
}

/* =========================================================
   APPLY DAILY RULES
========================================================= */

foreach($employees as $empid => $emp){

    $period = new DatePeriod(
        new DateTime($from),
        new DateInterval('P1D'),
        (new DateTime($to))->modify('+1 day')
    );

    foreach($period as $dateObj){

        $date = $dateObj->format('Y-m-d');
        $day  = $dateObj->format('l');

        $hours = $daily[$empid][$date] ?? 0;

        /* SUNDAY RULE */
        if($day === 'Sunday'){
            $hours = 8;
        }

        /* CAP */
        if($hours > 8){
            $hours = 8;
        }

        /* SATURDAY RULE */
        if($day === 'Saturday'){

            if($hours > 0 && $hours <= 3){

                $hours = 8;

            } else {

                $hours = ($hours / 3) * 8;

                if($hours > 8){
                    $hours = 8;
                }
            }
        }

        $employees[$empid]['total_hr'] += $hours;
    }
}

/* =========================================================
   SORT EMPLOYEES
========================================================= */

uasort($employees, function($a, $b){

    $c = strcmp($a['lastname'], $b['lastname']);

    return $c !== 0
        ? $c
        : strcmp($a['firstname'], $b['firstname']);
});

/* =========================================================
   FINAL COMPUTATIONS
========================================================= */

$payroll_rows = [];

foreach($employees as $empid => $emp){

    $emp_code = $emp['emp_code'];

    /* REGULAR */

    $regular = $emp['rate'] * $emp['total_hr'];

    /* OVERTIME */

    $ot = (float)(
        $conn->query("
            SELECT SUM(hours * rate) AS total
            FROM overtime
            WHERE employee_id='$empid'
            AND date_overtime BETWEEN '$from' AND '$to'
        ")->fetch_assoc()['total'] ?? 0
    );

    /* HOLIDAY PAY */

    $hp = (float)(
        $conn->query("
            SELECT SUM(
                hours * rate *
                (
                    CASE
                        /* BELOW 100 = DIRECT EXTRA % */
                        WHEN percentage < 100
                            THEN (percentage / 100)

                        /* 100 OR HIGHER = ONLY EXCESS ABOVE 100 */
                        ELSE ((percentage - 100) / 100)
                    END
                )
            ) AS total
            FROM holiday_pay
            WHERE employee_id='$empid'
            AND date_holiday BETWEEN '$from' AND '$to'
        ")->fetch_assoc()['total'] ?? 0
    );

    /* CASH ADVANCE */

    $ca = (float)(
        $conn->query("
            SELECT SUM(amount) AS total
            FROM cashadvance
            WHERE employee_id='$empid'
            AND date_advance BETWEEN '$from' AND '$to'
        ")->fetch_assoc()['total'] ?? 0
    );

    /* PERSONAL DEDUCTIONS */

    $pd_query = $conn->query("
        SELECT description, amount
        FROM personal_deductions
        WHERE employee_id='$emp_code'
    ");

    $pd_items = [];
    $pd_total = 0;

    while($pd = $pd_query->fetch_assoc()){

        $pd_items[] = $pd;
        $pd_total += $pd['amount'];
    }

    /* COLA */

    $cola_value = ($cola_enabled == 1)
        ? $cola_amount
        : 0;

    /* TOTALS */

    $gross = $regular + $ot + $hp + $cola_value;

    $total_deduction =
        $total_global_deduction +
        $pd_total +
        $ca;

    $net = $gross - $total_deduction;

    /* SAVE */

    $payroll_rows[] = [

        'empid' => $empid,

        'emp' => $emp,

        'regular' => $regular,

        'ot' => $ot,

        'hp' => $hp,

        'cola_value' => $cola_value,

        'gross' => $gross,

        'global_ded' => $global_deductions,

        'pd_items' => $pd_items,

        'pd_total' => $pd_total,

        'ca' => $ca,

        'total_deduction' => $total_deduction,

        'net' => $net
    ];
}
?>