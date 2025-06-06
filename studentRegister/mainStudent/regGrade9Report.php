<?php
require '../../connect.php';

$gradeLevelId = 9;

// Query for gender counts
$queryGender = "
    SELECT stud_gender, COUNT(*) AS count 
    FROM STUDENT 
    WHERE gradelvl_id = $1 
    GROUP BY stud_gender
";
$resultGender = pg_query_params($conn, $queryGender, [$gradeLevelId]);

$maleCount = 0;
$femaleCount = 0;

while ($row = pg_fetch_assoc($resultGender)) {
    if ($row['stud_gender'] === 'Male') {
        $maleCount = $row['count'];
    } elseif ($row['stud_gender'] === 'Female') {
        $femaleCount = $row['count'];
    }
}

$total = $maleCount + $femaleCount;

// Query for students enrolled in the past 7 days
$queryWeek = "
    SELECT COUNT(*) AS weekly_total 
    FROM STUDENT 
    WHERE gradelvl_id = $1 
      AND enrollment_date >= NOW() - INTERVAL '7 days'
";
$resultWeek = pg_query_params($conn, $queryWeek, [$gradeLevelId]);
$weeklyTotal = pg_fetch_result($resultWeek, 0, 0);

// Weekly breakdown by day
$dayCounts = [];
$labels = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$weekStart = new DateTime(); // today
$dayOfWeek = $weekStart->format('w'); // 0 (Sunday) to 6 (Saturday)
$weekStart->modify('-' . ($dayOfWeek == 0 ? 6 : $dayOfWeek - 1) . ' days'); // go to Monday

for ($i = 0; $i < 7; $i++) {
    $start = clone $weekStart;
    $start->modify("+$i day");
    $end = clone $start;
    $end->modify('+1 day');

    $query = "
        SELECT COUNT(*) 
        FROM STUDENT 
        WHERE gradelvl_id = $1 
          AND enrollment_date >= $2 
          AND enrollment_date < $3
    ";
    $result = pg_query_params($conn, $query, [
        $gradeLevelId,
        $start->format('Y-m-d'),
        $end->format('Y-m-d')
    ]);
    $count = pg_fetch_result($result, 0, 0);
    $dayCounts[] = $count;
    $datesFormatted[] = $start->format('F d, Y');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Grade 9 Enrollment Report</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
</head>
<body>
<div class="container mt-5">
  <h2>Grade 9 Enrollment Report</h2>

  <!-- Gender Table -->
  <table class="table table-bordered mt-3">
    <thead class="table-dark">
      <tr>
        <th>Gender</th>
        <th>Count</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Male</td>
        <td><?= $maleCount ?></td>
      </tr>
      <tr>
        <td>Female</td>
        <td><?= $femaleCount ?></td>
      </tr>
      <tr class="table-secondary">
        <td><strong>Total</strong></td>
        <td><strong><?= $total ?></strong></td>
      </tr>
      <tr class="table-info">
        <td><strong>Enrolled (Past 7 Days)</strong></td>
        <td><strong><?= $weeklyTotal ?></strong></td>
      </tr>
    </tbody>
  </table>

  <!-- Weekly Breakdown Table -->
  <h4 class="mt-5">Enrollment Breakdown This Week</h4>
  <table class="table table-bordered">
    <thead class="table-primary">
      <tr>
        <?php for ($i = 0; $i < 7; $i++): ?>
          <th>
            <?= $labels[$i] ?><br>
            <small>(<?= $datesFormatted[$i] ?>)</small>
          </th>
        <?php endfor; ?>
      </tr>
    </thead>
    <tbody>
      <tr>
        <?php foreach ($dayCounts as $count): ?>
          <td><?= $count ?></td>
        <?php endforeach; ?>
      </tr>
    </tbody>
  </table>
</div>
</body>
</html>
