<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Attendance - ABIT</title>
    <link rel="icon" href="../img/abit-logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php
    session_start();
    if (!isset($_SESSION['student_id'])) {
        header('Location: ../auth/login.php');
        exit;
    }
    include '../includes/navbar.php'; ?>

    <div class="container mt-4">
        <h1>My Attendance</h1>
        <div class="table-responsive">
            <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="attendance-data">
                <!-- Data will be loaded here by JavaScript -->
            </tbody>
            </table>
        </div>
    </div>

    <script>
        fetch('attendance.php')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('attendance-data');
                let rows = '';
                const studentId = <?php echo $_SESSION['student_id']; ?>;
                for (const record of data) {
                    if (record.student_id === studentId) {
                        const date = new Date(record.timestamp);
                        rows += `<tr><td>${date.toLocaleDateString()}</td><td>${date.toLocaleTimeString()}</td><td>${record.status}</td></tr>`;
                    }
                }
                tbody.innerHTML = rows;
            });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>