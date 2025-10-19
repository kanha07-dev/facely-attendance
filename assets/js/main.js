const video = document.getElementById('video');
const statusDiv = document.getElementById('status');
const studentList = document.getElementById('student-list');
const cameraModal = new bootstrap.Modal(document.getElementById('cameraModal'));
const modalStatus = document.getElementById('modal-status');

let faceMatcher;
let labeledFaceDescriptors;
let recognitionInterval;
let recognitionTimeout;

// Load models and student data on page load
Promise.all([
    faceapi.nets.ssdMobilenetv1.loadFromUri('/models'),
    faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
    faceapi.nets.faceRecognitionNet.loadFromUri('/models')
]).then(async () => {
    statusDiv.textContent = 'Loading student data...';
    try {
        labeledFaceDescriptors = await loadLabeledImages();
        if (labeledFaceDescriptors && labeledFaceDescriptors.length > 0) {
            faceMatcher = new faceapi.FaceMatcher(labeledFaceDescriptors, 0.6);
            statusDiv.textContent = 'Ready.';
            loadStudentList();
        } else {
            statusDiv.textContent = 'No registered students with valid images found. Please register students in the admin panel.';
        }
    } catch (err) {
        statusDiv.textContent = 'Error loading student data: ' + err.message;
        console.error(err);
    }
}).catch(err => {
    statusDiv.textContent = 'Error loading models: ' + err;
    console.error(err);
});

function loadStudentList() {
    fetch('/students.php?t=' + new Date().getTime())
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(students => {
            if (!students || students.length === 0) {
                statusDiv.textContent = 'No students found in the data file.';
                return;
            }
            let rows = '';
            students.forEach(student => {
                const buttonId = `btn-student-${student.id}`;
                rows += `
                    <tr>
                        <td>${student.name}</td>
                        <td>${student.roll_no || 'N/A'}</td>
                        <td>${student.stream || 'N/A'}</td>
                        <td>
                            <button class="btn btn-primary" onclick="startRecognition('${student.name}')" id="${buttonId}">
                                Mark Attendance
                            </button>
                        </td>
                    </tr>
                `;
            });
            studentList.innerHTML = rows;
        })
        .catch(error => {
            statusDiv.textContent = 'Error loading student list: ' + error.message;
            console.error('Error loading student list:', error);
        });
}

// ... (rest of the file)

async function loadLabeledImages() {
    const response = await fetch('/students.php?t=' + new Date().getTime());
    if (!response.ok) {
        throw new Error(`Failed to fetch students: ${response.statusText}`);
    }
    const students = await response.json();
    if (students.length === 0) return null;

    // ... (rest of the function)
}

function markAttendance(name) {
    fetch('/checkin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ name: name })
    })
    // ... (rest of the function)
}

async function startRecognition(studentName) {
    cameraModal.show();
    modalStatus.textContent = `Attempting to recognize ${studentName}...`;

    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: {} });
        video.srcObject = stream;
    } catch (err) {
        modalStatus.textContent = 'Error starting video: ' + err;
        console.error(err);
        return;
    }

    // Wait for the video to start playing
    await new Promise(resolve => video.onplaying = resolve);

    const canvas = faceapi.createCanvasFromMedia(video);
    document.getElementById('video-container').append(canvas);
    const displaySize = { width: video.width, height: video.height };
    faceapi.matchDimensions(canvas, displaySize);

    let recognized = false;

    recognitionInterval = setInterval(async () => {
        const detections = await faceapi.detectAllFaces(video).withFaceLandmarks().withFaceDescriptors();
        const resizedDetections = faceapi.resizeResults(detections, displaySize);
        canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);

        const results = resizedDetections.map(d => faceMatcher.findBestMatch(d.descriptor));

        results.forEach((result, i) => {
            const box = resizedDetections[i].detection.box;
            let boxColor = 'red';
            let label = 'Unknown';

            if (result.label === studentName) {
                boxColor = 'green';
                label = studentName;

                if (!recognized) {
                    recognized = true;
                    markAttendance(studentName);
                    modalStatus.textContent = `Successfully recognized ${studentName}. Attendance marked.`;
                    const button = document.querySelector(`[id^='btn-student-'][onclick="startRecognition('${studentName}')"]`);
                    if(button){
                        button.disabled = true;
                        button.textContent = 'Attendance Marked';
                    }
                    stopRecognition();
                }
            } else if (result.label !== 'unknown') {
                label = 'Wrong Person';
            }

            const drawBox = new faceapi.draw.DrawBox(box, { label: label, boxColor: boxColor });
            drawBox.draw(canvas);
        });
    }, 100);

    recognitionTimeout = setTimeout(() => {
        if (!recognized) {
            modalStatus.textContent = `Could not recognize ${studentName}. Please try again.`;
            stopRecognition();
        }
    }, 5000);
}

function stopRecognition() {
    clearInterval(recognitionInterval);
    clearTimeout(recognitionTimeout);
    if (video.srcObject) {
        video.srcObject.getTracks().forEach(track => track.stop());
        video.srcObject = null;
    }
    const canvas = document.querySelector('#video-container canvas');
    if (canvas) {
        canvas.remove();
    }
    setTimeout(() => cameraModal.hide(), 2000);
}

async function loadLabeledImages() {
    const response = await fetch('/students.php?t=' + new Date().getTime());
    if (!response.ok) {
        throw new Error(`Failed to fetch students: ${response.statusText}`);
    }
    const students = await response.json();
    if (students.length === 0) return null;

    const labeledDescriptors = await Promise.all(
        students.map(async student => {
            const descriptions = [];
            try {
                // URL encode the path to handle spaces and other characters
                const imageUrl = `/${encodeURI(student.photoUrl)}`;
                const img = await faceapi.fetchImage(imageUrl);
                const detections = await faceapi.detectSingleFace(img).withFaceLandmarks().withFaceDescriptor();
                if (detections) {
                    descriptions.push(detections.descriptor);
                }
            } catch (e) {
                console.error('Error loading image for', student.name, e);
            }
            if (descriptions.length > 0) {
                return new faceapi.LabeledFaceDescriptors(student.name, descriptions);
            }
            return null;
        })
    );
    // Filter out null values (students whose images failed to load)
    return labeledDescriptors.filter(ld => ld !== null);
}

function markAttendance(name) {
    fetch('/checkin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ name: name })
    })
    .then(response => response.json())
    .then(data => {
        console.log(data.message);
        statusDiv.textContent = data.message;
        setTimeout(() => {
            if(statusDiv.textContent === data.message) {
                statusDiv.textContent = 'Ready.';
            }
        }, 5000);
    });
}

// Stop video stream when modal is closed
document.getElementById('cameraModal').addEventListener('hidden.bs.modal', function () {
    stopRecognition();
});
