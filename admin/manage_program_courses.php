<?php
include 'header.php';
include '../includes/db.php';
?>

<h3>Manage Courses per Program</h3>

<select id="program-select" onchange="showCourses(this.value)">
    <option value="">-- Select a Program --</option>
    <?php
    $result = $conn->query("SELECT * FROM programs");
    while ($row = $result->fetch_assoc()):
    ?>
        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
    <?php endwhile; ?>
</select>

<button onclick="addCourse()">+ Add Course</button>

<h4>Assigned Courses:</h4>
<ul id="course-list"></ul>

<script>
function showCourses(programId) {
    if (!programId) return;
    fetch('get_courses.php?program_id=' + programId)
        .then(res => res.json())
        .then(data => {
            let list = document.getElementById('course-list');
            list.innerHTML = '';
            data.forEach(course => {
                list.innerHTML += `<li>${course.title}</li>`;
            });
        });
}

function addCourse() {
    const programId = document.getElementById('program-select').value;
    if (!programId) return alert('Please select a program first.');

    fetch('get_available_courses.php?program_id=' + programId)
        .then(res => res.json())
        .then(data => {
            let courseOptions = data.map(c => `<option value="${c.id}">${c.title}</option>`).join('');
            let courseSelectHTML = `<select id="new-course">${courseOptions}</select>`;
            let div = document.createElement('div');
            div.innerHTML = courseSelectHTML;
            document.body.appendChild(div);

            const confirmBtn = document.createElement('button');
            confirmBtn.innerText = 'Confirm';
            confirmBtn.onclick = () => {
                const courseId = document.getElementById('new-course').value;
                fetch('assign_course.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ program_id: programId, course_id: courseId })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Course assigned!');
                        showCourses(programId);
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            };
            div.appendChild(confirmBtn);
        });
}
</script>

<?php include 'footer.php'; ?>
