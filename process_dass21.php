<?php
session_start();

// ▼ DB connection
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "mindease";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// ▼ Ensure user logged in
if (!isset($_SESSION['user_id'])) {
    echo "Please log in first.";
    exit();
}
$user_id = $_SESSION['user_id'];

// ▼ Collect and sum responses
$responses = [];
for ($i = 1; $i <= 21; $i++) {
    $responses[$i] = isset($_POST["q$i"]) ? (int)$_POST["q$i"] : 0;
}
// DASS‑21 uses subscales (sum of 7 items each ×2)
$depr_idxs  = [3,5,10,13,16,17,21];
$anx_idxs   = [2,4,7,9,15,19,20];
$stress_idxs= [1,6,8,11,12,14,18];

$depr_score  = array_sum(array_map(fn($i)=>$responses[$i], $depr_idxs)) * 2;
$anx_score   = array_sum(array_map(fn($i)=>$responses[$i], $anx_idxs)) * 2;
$stress_score= array_sum(array_map(fn($i)=>$responses[$i], $stress_idxs)) * 2;

// ▼ Store in database
$stmt = $conn->prepare("
  INSERT INTO dass_results (user_id, depression_score, anxiety_score, stress_score)
  VALUES (?, ?, ?, ?)
");
$stmt->bind_param("iiii", $user_id, $depr_score, $anx_score, $stress_score);
$stmt->execute();
$stmt->close();
$conn->close();

// ▼ Determine severity categories & tips
function getSeverity($score, $thresholds, $labels) {
    foreach ($thresholds as $idx => $max) {
        if ($score <= $max) return $labels[$idx];
    }
    return end($labels);
}
// Thresholds from DASS manual
$th_depr  = [9,13,20,27];   $lbl_depr  = ["Normal","Mild","Moderate","Severe","Extremely Severe"];
$th_anx   = [7,9,14,19];    $lbl_anx   = ["Normal","Mild","Moderate","Severe","Extremely Severe"];
$th_stress= [14,18,25,33];  $lbl_stress= ["Normal","Mild","Moderate","Severe","Extremely Severe"];

$cat_depr   = getSeverity($depr_score,  $th_depr,  $lbl_depr);
$cat_anx    = getSeverity($anx_score,   $th_anx,   $lbl_anx);
$cat_stress = getSeverity($stress_score,$th_stress,$lbl_stress);

// ▼ Tips per category
$tipSets = [
  "depression" => [
    "Normal" => ["Maintain your routine","Stay socially active","Keep exercising"],
    "Mild"   => ["Try journaling","Practice mindfulness","Set small daily goals"],
    "Moderate"=>["Consider counseling","Keep a mood diary","Maintain sleep hygiene"],
    "Severe" =>["Seek professional help","Talk to a trusted person","Consider therapy"],
    "Extremely Severe"=>["Immediate professional support","Crisis helpline","Emergency services"]
  ],
  "anxiety" => [
    "Normal" => ["Practice deep breathing","Stay physically active","Limit caffeine"],
    "Mild"   => ["Try progressive muscle relaxation","Use grounding techniques","Short walks"],
    "Moderate"=>["Consider therapy","Join support group","Guided meditation apps"],
    "Severe" =>["Seek professional help","Share feelings with someone","Therapy options"],
    "Extremely Severe"=>["Immediate help recommended","Crisis helplines","Medical intervention"]
  ],
  "stress" => [
    "Normal" => ["Keep up hobbies","Maintain social time","Regular exercise"],
    "Mild"   => ["Time‑management techniques","Mindful breaks","Relaxation exercises"],
    "Moderate"=>["Talk to counselor","Structured scheduling","Stress‑reduction apps"],
    "Severe" =>["Professional support","Support network","Therapy sessions"],
    "Extremely Severe"=>["Immediate medical advice","Crisis lines","Emergency help"]
  ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>DASS‑21 Results & Tips | MindEase</title>
  <style>
    body { background:#111; color:#fff; font-family:Arial; padding:40px; text-align:center; }
    .box {  
      background:#222; display:inline-block; padding:30px; border-radius:12px;
      border:1px solid #ff7200; box-shadow:0 0 20px rgba(255,114,0,0.3);
      max-width:700px; text-align:left;
    }
    h2 { color:#ff7200; }
    .score { font-size:20px; margin:15px 0; }
    .cat { font-weight:bold; margin:10px 0; }
    ul { margin-left:20px; line-height:1.6; }
    .btn { display:inline-block; margin-top:25px; padding:10px 25px;
      background:#ff7200; color:#fff; text-decoration:none; border-radius:8px;
      transition:0.3s;
    }
    .btn:hover { background:#e65c00; }
  </style>
</head>
<body>
  <div class="box">
    <h2>Your DASS‑21 Results</h2>
    <div class="score">Depression: <?= $depr_score ?></div>
    <div class="cat">Category: <?= $cat_depr ?></div>
    <ul>
      <?php foreach ($tipSets['depression'][$cat_depr] as $tip): ?>
        <li><?= htmlspecialchars($tip) ?></li>
      <?php endforeach; ?>
    </ul>

    <div class="score">Anxiety: <?= $anx_score ?></div>
    <div class="cat">Category: <?= $cat_anx ?></div>
    <ul>
      <?php foreach ($tipSets['anxiety'][$cat_anx] as $tip): ?>
        <li><?= htmlspecialchars($tip) ?></li>
      <?php endforeach; ?>
    </ul>

    <div class="score">Stress: <?= $stress_score ?></div>
    <div class="cat">Category: <?= $cat_stress ?></div>
    <ul>
      <?php foreach ($tipSets['stress'][$cat_stress] as $tip): ?>
        <li><?= htmlspecialchars($tip) ?></li>
      <?php endforeach; ?>
    </ul>

    <a href="index.html" class="btn">Back to Home</a>
  </div>
</body>
</html>
