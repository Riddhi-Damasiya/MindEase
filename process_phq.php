<?php
session_start();

// ▼ Database connection
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "mindease";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ▼ Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to view this page.";
    exit();
}
$user_id = $_SESSION['user_id'];

// ▼ Sum up the PHQ‑9 responses
$totalScore = 0;
for ($i = 1; $i <= 9; $i++) {
    if (isset($_POST["q$i"])) {
        $totalScore += (int) $_POST["q$i"];
    }
}

// ▼ Store the result linked to the user
$stmt = $conn->prepare("
    INSERT INTO phq_results (user_id, score)
    VALUES (?, ?)
");
$stmt->bind_param("ii", $user_id, $totalScore);
$stmt->execute();
$stmt->close();

// ▼ Determine category and suggestions
if ($totalScore <= 4) {
    $category = "Minimal or No Depression";
    $suggestion = "Great! Your responses show minimal or no signs of depression. Keep up these healthy habits:";
    $tips = [
        "Maintain a regular sleep schedule.",
        "Stay physically active—take walks or try light exercise.",
        "Keep social connections strong: call or meet friends/family.",
        "Practice gratitude journaling each day.",
        "Continue doing things you enjoy."
    ];
} elseif ($totalScore <= 9) {
    $category = "Mild Depression";
    $suggestion = "Your score suggests mild symptoms. You might find these self-care strategies helpful:";
    $tips = [
        "Try relaxation techniques: deep breathing or meditation.",
        "Incorporate a short daily walk into your routine.",
        "Dedicate time to a hobby you love—painting, music, gardening.",
        "Limit screen time and practice digital detox.",
        "Reach out to a close friend just to talk."
    ];
} elseif ($totalScore <= 14) {
    $category = "Moderate Depression";
    $suggestion = "You’re experiencing moderate symptoms. Consider these next steps:";
    $tips = [
        "Schedule a check-in with a counselor or therapist.",
        "Establish a structured daily routine.",
        "Keep a mood journal to track patterns.",
        "Try guided mindfulness apps (e.g., Headspace, Calm).",
        "Look into local support groups or online communities."
    ];
} elseif ($totalScore <= 19) {
    $category = "Moderately Severe Depression";
    $suggestion = "Your score indicates moderately severe symptoms. It’s important to seek help:";
    $tips = [
        "Book an appointment with a mental health professional.",
        "Share your feelings with someone you trust.",
        "Practice gentle physical activity: yoga, stretching.",
        "Consider lifestyle changes: balanced diet, regular sleep.",
        "If you feel unsafe, contact a crisis hotline immediately."
    ];
} else {
    $category = "Severe Depression";
    $suggestion = "Your score indicates severe symptoms. Please reach out for professional support right away:";
    $tips = [
        "Contact your primary care provider or psychiatrist.",
        "Call emergency services (e.g., 911 in the US) if you feel unsafe.",
        "Reach a suicide prevention hotline: e.g., 988 in the US, or local equivalent.",
        "Stay connected—tell a friend or family member you need help.",
        "Explore inpatient or intensive outpatient programs if recommended."
    ];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>PHQ‑9 Result & Tips | MindEase</title>
  <style>
    body {
      background: #111;
      color: #fff;
      font-family: 'Arial', sans-serif;
      text-align: center;
      padding: 60px;
      animation: fadeIn 1s ease-in-out;
    }

    @keyframes fadeIn {
      0% { opacity: 0; }
      100% { opacity: 1; }
    }

    .container {
      display: inline-block;
      background: #222;
      padding: 40px;
      border-radius: 15px;
      border: 1px solid #ff7200;
      box-shadow: 0px 0px 20px rgba(255, 114, 0, 0.3);
      max-width: 700px;
      width: 100%;
      margin: auto;
    }

    h2 {
      color: #ff7200;
      font-size: 28px;
      margin-bottom: 20px;
    }

    .score {
      font-size: 30px;
      font-weight: bold;
      margin: 20px 0;
    }

    .category {
      font-size: 24px;
      font-weight: bold;
      margin: 10px 0;
    }

    .suggestion {
      font-size: 18px;
      margin: 20px 0;
    }

    ul.tips {
      list-style: disc inside;
      margin-left: 20px;
      line-height: 1.8;
    }

    .btn {
      padding: 12px 30px;
      background-color: #ff7200;
      color: white;
      text-decoration: none;
      border-radius: 8px;
      font-size: 16px;
      transition: background-color 0.3s ease;
      display: inline-block;
      margin-top: 30px;
    }

    .btn:hover {
      background-color: #e65c00;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>PHQ‑9 Assessment Result</h2>
    <div class="score">
      <strong>Total Score:</strong> <?= $totalScore ?>
    </div>
    <div class="category">
      <strong>Category:</strong> <?= $category ?>
    </div>
    <div class="suggestion">
      <?= $suggestion ?>
    </div>
    <ul class="tips">
      <?php foreach ($tips as $tip): ?>
        <li><?= htmlspecialchars($tip) ?></li>
      <?php endforeach; ?>
    </ul>
    <a href="index.html" class="btn">Back to Home</a>
  </div>
</body>
</html>
