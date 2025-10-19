<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - Facely</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h1>Attendance</h1>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Roll No</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="attendance-data">
                <!-- Data will be loaded here by JavaScript -->
            </tbody>
        </table>
    </div>

    <script>
        fetch('attendance.php')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('attendance-data');
                let rows = '';
                for (const record of data) {
                    rows += `<tr><td>${record.name}</td><td>${record.roll_no}</td><td>${new Date(record.timestamp).toLocaleString()}</td><td>${record.status}</td></tr>`;
                }
                tbody.innerHTML = rows;
            });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>