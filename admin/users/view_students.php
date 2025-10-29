<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$content = '
<div class="container mt-4">
    <h1>Registered Students</h1>
    <p class="text-muted">Viewing students for your ' . (isset($_SESSION['admin_role']) ? ($_SESSION['admin_role'] === 'hod' ? 'department' : ($_SESSION['admin_role'] === 'teacher' ? 'class' : 'all')) : 'all') . '</p>
    <div class="table-responsive">
        <table class="table table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Roll No</th>
                <th>Stream</th>
                <th>Semester</th>
                <th>Photo</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="student-list">
            <!-- Student data will be loaded here -->
        </tbody>
        </table>
    </div>
</div>
<script>
function editStudent(studentId) {
    window.location.href = "edit_student.php?id=" + studentId;
}

fetch("../view_students_api.php")
    .then(response => response.json())
    .then(data => {
        const tbody = document.getElementById("student-list");
        data.forEach(student => {
            const tr = document.createElement("tr");
            tr.innerHTML = `<td>${student.name}</td><td>${student.roll_no}</td><td>${student.stream_name}</td><td>${student.semester_number}</td><td><img src="${student.photoUrl}" width="50" height="50" alt="Student Photo"></td><td><button class="btn btn-sm btn-info" onclick="editStudent(${student.id})">Edit</button></td>`;
            tbody.appendChild(tr);
        });
    });
</script>
';

include '../layout.php';
?>