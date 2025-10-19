<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h1>Facely - Admin Panel</h1>

        <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] === 'success'): ?>
                <div class="alert alert-success">Student registered successfully!</div>
            <?php elseif ($_GET['status'] === 'error'): ?>
                <div class="alert alert-danger">Error registering student. <?php echo isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Please check permissions and file contents.'; ?></div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title">Register New Student</h2>
                <form action="register.php" method="post" enctype="multipart/form-data" id="register-form">
                    <div class="mb-3">
                        <label for="name" class="form-label">Student Name:</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="roll_no" class="form-label">Roll No:</label>
                        <input type="text" name="roll_no" id="roll_no" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="stream" class="form-label">Stream:</label>
                        <select name="stream" id="stream" class="form-select" required>
                            <option value="">Select Stream</option>
                            <option value="MCA">MCA</option>
                            <option value="MBA">MBA</option>
                            <option value="B.Tech">B.Tech</option>
                            <option value="BCA">BCA</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="photo" class="form-label">Photo:</label>
                        <input type="file" name="photo" id="photo" class="form-control" accept="image/*" required>
                        <div class="form-text">Max file size: 2MB</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Register Student</button>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title">Registered Students</h2>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Roll No</th>
                            <th>Stream</th>
                            <th>Photo</th>
                        </tr>
                    </thead>
                    <tbody id="student-list">
                        <!-- Student data will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h2 class="card-title">Attendance Log</h2>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Roll No</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="attendance-log">
                        <!-- Attendance data will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('register-form').addEventListener('submit', function(event) {
            const photoInput = document.getElementById('photo');
            if (photoInput.files.length > 0) {
                const file = photoInput.files[0];
                const maxSize = 2 * 1024 * 1024; // 2MB
                if (file.size > maxSize) {
                    alert('The selected file is too large. Please choose a file smaller than 2MB.');
                    event.preventDefault(); // Stop form submission
                }
            }
        });

        // Fetch and display student list
        fetch('students.php')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('student-list');
                data.forEach(student => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td>${student.name}</td><td>${student.roll_no}</td><td>${student.stream}</td><td><img src="${student.photoUrl}" width="50" height="50"></td>`;
                    tbody.appendChild(tr);
                });
            });

        // Fetch and display attendance log
        fetch('attendance.php')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('attendance-log');
                data.forEach(record => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td>${record.name}</td><td>${record.roll_no}</td><td>${new Date(record.timestamp).toLocaleString()}</td><td>${record.status}</td>`;
                    tbody.appendChild(tr);
                });
            });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>