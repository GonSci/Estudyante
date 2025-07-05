<?php

ob_start();

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

echo "<!-- Debug: Session data = " . print_r($_SESSION, true) . " -->";

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../login.php");
    exit;
}

include 'header.php';
include '../includes/db.php';
?>

<link rel="stylesheet" href="css/calendar.css">

<?php

if(isset($_POST['task_submit'])) {
    $task_title = mysqli_real_escape_string($conn, $_POST['task_title']);
    $task_description = mysqli_real_escape_string($conn, $_POST['task_description']);
    $task_date = mysqli_real_escape_string($conn, $_POST['task_date']);
    $task_time = !empty($_POST['task_time']) ? mysqli_real_escape_string($conn, $_POST['task_time']) : NULL;
    $priority = mysqli_real_escape_string($conn, $_POST['priority']);
    $admin_id = isset($_SESSION['id']) ? $_SESSION['id'] : 1;
    
    if(isset($_POST['task_id']) && !empty($_POST['task_id'])) {
        $task_id = (int)$_POST['task_id'];
        $query = "UPDATE tasks SET title='$task_title', description='$task_description', 
                  task_date='$task_date', time=" . ($task_time ? "'$task_time'" : "NULL") . ", 
                  priority='$priority' WHERE id=$task_id AND created_by=$admin_id";
        $success_msg = "Task updated successfully!";
    } else {
        $query = "INSERT INTO tasks (title, description, task_date, time, priority, created_by) 
                  VALUES ('$task_title', '$task_description', '$task_date', " . 
                  ($task_time ? "'$task_time'" : "NULL") . ", '$priority', $admin_id)";
        $success_msg = "Task added successfully!";
    }
    
    if($conn->query($query)) {
        $_SESSION['task_success'] = $success_msg;
        header("Location: calendar.php");
        exit;
    } else {
        $error_message = "Error with task: " . $conn->error;
    }
}

if(isset($_POST['delete_task'])) {
    $task_id = (int)$_POST['task_id'];
    $admin_id = isset($_SESSION['id']) ? $_SESSION['id'] : 1;
    
    $query = "DELETE FROM tasks WHERE id=$task_id AND created_by=$admin_id";
    if($conn->query($query)) {
        $_SESSION['task_success'] = "Task deleted successfully!";
        header("Location: calendar.php");
        exit;
    } else {
        $error_message = "Error deleting task: " . $conn->error;
    }
}

if(isset($_POST['update_task_status'])) {
    $task_id = (int)$_POST['task_id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $admin_id = isset($_SESSION['id']) ? $_SESSION['id'] : 1;
    
    $query = "UPDATE tasks SET status='$status' WHERE id=$task_id AND created_by=$admin_id";
    if($conn->query($query)) {
        $_SESSION['task_success'] = "Task status updated!";
        header("Location: calendar.php");
        exit;
    } else {
        $error_message = "Error updating task status: " . $conn->error;
    }
}
$tasks_for_calendar = [];
$admin_id = isset($_SESSION['id']) ? $_SESSION['id'] : 1;

$tasks_query = "SELECT * FROM tasks WHERE created_by = $admin_id ORDER BY task_date, time";
$tasks_result = $conn->query($tasks_query);

if ($tasks_result && $tasks_result->num_rows > 0) {
    while ($task = $tasks_result->fetch_assoc()) {
        $date_key = $task['task_date'];
        if (!isset($tasks_for_calendar[$date_key])) {
            $tasks_for_calendar[$date_key] = [];
        }
        $tasks_for_calendar[$date_key][] = $task;
    }
}

$success_message = '';
if (isset($_SESSION['task_success'])) {
    $success_message = $_SESSION['task_success'];
    unset($_SESSION['task_success']);
}
?>

<div class="container mt-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Task Calendar</h1>
            <p class="text-muted">Manage your tasks and schedule efficiently</p>
        </div>
        <div>
            <button class="btn btn-primary" onclick="addTask(new Date().toISOString().split('T')[0])">
                <i class="fas fa-plus me-2"></i>Add Task
            </button>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if(isset($success_message) && !empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $success_message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if(isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $error_message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <!-- Task Calendar Section -->
    <div class="card shadow-sm">
        <div class="card-header" style="background-color: hsl(217, 65.90%, 25.30%); color: white;">
            <h3 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Calendar View</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-8">
                    <div id="calendar-container">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <button class="btn btn-outline-primary" onclick="previousMonth()">
                                <i class="fas fa-chevron-left"></i> Previous
                            </button>
                            <h4 id="current-month-year" class="mb-0"></h4>
                            <button class="btn btn-outline-primary" onclick="nextMonth()">
                                Next <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        <div id="calendar" class="calendar"></div>
                        
                        <!-- Calendar Legend -->
                        <div class="mt-3">
                            <small class="text-muted">
                                <strong>Legend:</strong>
                                <span class="badge bg-danger ms-2">High Priority</span>
                                <span class="badge bg-warning text-dark ms-1">Medium Priority</span>
                                <span class="badge bg-success ms-1">Low Priority</span>
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div id="task-panel" class="sticky-top" style="top: 20px;">
                        <h5><i class="fas fa-tasks me-2 text-primary"></i>Tasks for <span id="selected-date">Today</span></h5>
                        <div id="tasks-for-date" class="mt-3">
                            <p class="text-muted">Click on a date to view tasks</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Task Statistics -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-primary">Total Tasks</h5>
                    <h3 class="text-primary" id="total-tasks">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-warning">Pending</h5>
                    <h3 class="text-warning" id="pending-tasks">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-success">Completed</h5>
                    <h3 class="text-success" id="completed-tasks">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-danger">High Priority</h5>
                    <h3 class="text-danger" id="high-priority-tasks">0</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Task Modal -->
<div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskModalLabel">Add Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" id="task_id" name="task_id">
                    <div class="mb-3">
                        <label for="task_title" class="form-label">Task Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="task_title" name="task_title" required>
                    </div>
                    <div class="mb-3">
                        <label for="task_description" class="form-label">Description</label>
                        <textarea class="form-control" id="task_description" name="task_description" rows="3" placeholder="Enter task description..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="task_date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="task_date" name="task_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="task_time" class="form-label">Time</label>
                            <input type="time" class="form-control" id="task_time" name="task_time">
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                        <select class="form-select" id="priority" name="priority" required>
                            <option value="low">Low Priority</option>
                            <option value="medium" selected>Medium Priority</option>
                            <option value="high">High Priority</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="task_submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Save Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Calendar functionality
let currentDate = new Date();
let selectedDate = null;
let tasksData = <?php echo json_encode($tasks_for_calendar); ?>;

document.addEventListener('DOMContentLoaded', function() {
    generateCalendar();
    updateTaskStatistics();
    
    // Auto-select today's date
    const today = new Date().toISOString().split('T')[0];
    const todayElement = document.querySelector(`[data-date="${today}"]`);
    if (todayElement) {
        selectDate(today, todayElement);
    }
});

function generateCalendar() {
    const calendar = document.getElementById('calendar');
    const monthYear = document.getElementById('current-month-year');
    
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    
    monthYear.textContent = new Date(year, month).toLocaleDateString('en-US', { 
        month: 'long', 
        year: 'numeric' 
    });
    
    // Clear calendar
    calendar.innerHTML = '';
    
    // Add day headers
    const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    days.forEach(day => {
        const dayHeader = document.createElement('div');
        dayHeader.className = 'calendar-header';
        dayHeader.textContent = day;
        calendar.appendChild(dayHeader);
    });
    
    // Get first day of month and number of days
    const firstDay = new Date(year, month, 1).getDay();
    const lastDate = new Date(year, month + 1, 0).getDate();
    const prevLastDate = new Date(year, month, 0).getDate();
    
    // Add previous month's trailing days
    for (let i = firstDay - 1; i >= 0; i--) {
        const dayDiv = createDayElement(prevLastDate - i, true, year, month - 1);
        calendar.appendChild(dayDiv);
    }
    
    // Add current month's days
    for (let day = 1; day <= lastDate; day++) {
        const dayDiv = createDayElement(day, false, year, month);
        calendar.appendChild(dayDiv);
    }
    
    // Add next month's leading days
    const totalCells = calendar.children.length - 7; // Subtract headers
    const remainingCells = 42 - totalCells; // 6 rows * 7 days - headers
    for (let day = 1; day <= remainingCells; day++) {
        const dayDiv = createDayElement(day, true, year, month + 1);
        calendar.appendChild(dayDiv);
    }
}

function createDayElement(day, otherMonth, year, month) {
    const dayDiv = document.createElement('div');
    dayDiv.className = 'calendar-day';
    
    if (otherMonth) {
        dayDiv.classList.add('other-month');
    }
    
    // Check if today
    const today = new Date();
    if (!otherMonth && year === today.getFullYear() && 
        month === today.getMonth() && day === today.getDate()) {
        dayDiv.classList.add('today');
    }
    
    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    dayDiv.setAttribute('data-date', dateStr);
    
    const dateNumber = document.createElement('div');
    dateNumber.className = 'date-number';
    dateNumber.textContent = day;
    dayDiv.appendChild(dateNumber);
    
    // Add task indicators
    if (tasksData[dateStr]) {
        const taskCount = tasksData[dateStr].length;
        const countDiv = document.createElement('div');
        countDiv.className = 'task-count';
        countDiv.textContent = taskCount;
        dayDiv.appendChild(countDiv);
        
        // Add priority indicators
        tasksData[dateStr].slice(0, 5).forEach(task => {
            const indicator = document.createElement('span');
            indicator.className = `task-indicator ${task.priority}`;
            indicator.title = `${task.title} (${task.priority} priority)`;
            dayDiv.appendChild(indicator);
        });
    }
    
    dayDiv.addEventListener('click', () => selectDate(dateStr, dayDiv));
    
    return dayDiv;
}

function selectDate(dateStr, dayElement) {
    // Remove previous selection
    document.querySelectorAll('.calendar-day.selected').forEach(el => {
        el.classList.remove('selected');
    });
    
    // Add selection to clicked day
    dayElement.classList.add('selected');
    selectedDate = dateStr;
    
    // Update selected date display
    document.getElementById('selected-date').textContent = 
        new Date(dateStr).toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    
    // Show tasks for selected date
    showTasksForDate(dateStr);
}

function showTasksForDate(dateStr) {
    const tasksContainer = document.getElementById('tasks-for-date');
    const tasks = tasksData[dateStr] || [];
    
    if (tasks.length === 0) {
        tasksContainer.innerHTML = `
            <div class="text-center p-4">
                <i class="fas fa-calendar-plus fa-3x text-muted mb-3"></i>
                <p class="text-muted">No tasks for this date</p>
                <button class="btn btn-primary btn-sm" onclick="addTask('${dateStr}')">
                    <i class="fas fa-plus"></i> Add Task
                </button>
            </div>
        `;
    } else {
        let tasksHtml = '';
        tasks.forEach(task => {
            const timeStr = task.time ? new Date(`2000-01-01 ${task.time}`).toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            }) : '';
            
            tasksHtml += `
                <div class="task-item">
                    <div class="task-header">
                        <h6 class="task-title">${task.title}</h6>
                        <div>
                            <span class="badge priority-${task.priority} priority-badge">${task.priority.toUpperCase()}</span>
                            <span class="badge bg-${task.status === 'completed' ? 'success' : 
                                task.status === 'cancelled' ? 'danger' : 'warning'} status-badge ms-1">
                                ${task.status.toUpperCase()}
                            </span>
                        </div>
                    </div>
                    ${task.description ? `<p class="mb-2 text-muted">${task.description}</p>` : ''}
                    ${timeStr ? `<p class="task-time mb-2"><i class="fas fa-clock"></i> ${timeStr}</p>` : ''}
                    <div class="task-actions">
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editTask(${task.id}, '${task.title}', '${task.description || ''}', '${task.task_date}', '${task.time || ''}', '${task.priority}')">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        ${task.status !== 'completed' ? `
                            <button class="btn btn-sm btn-outline-success me-1" onclick="updateTaskStatus(${task.id}, 'completed')">
                                <i class="fas fa-check"></i> Complete
                            </button>
                        ` : ''}
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteTask(${task.id})">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            `;
        });
        
        tasksHtml += `
            <div class="text-center mt-3">
                <button class="btn btn-primary btn-sm" onclick="addTask('${dateStr}')">
                    <i class="fas fa-plus"></i> Add Another Task
                </button>
            </div>
        `;
        
        tasksContainer.innerHTML = tasksHtml;
    }
}

function updateTaskStatistics() {
    let totalTasks = 0;
    let pendingTasks = 0;
    let completedTasks = 0;
    let highPriorityTasks = 0;
    
    Object.values(tasksData).forEach(dateTasks => {
        dateTasks.forEach(task => {
            totalTasks++;
            if (task.status === 'pending') pendingTasks++;
            if (task.status === 'completed') completedTasks++;
            if (task.priority === 'high') highPriorityTasks++;
        });
    });
    
    document.getElementById('total-tasks').textContent = totalTasks;
    document.getElementById('pending-tasks').textContent = pendingTasks;
    document.getElementById('completed-tasks').textContent = completedTasks;
    document.getElementById('high-priority-tasks').textContent = highPriorityTasks;
}

function addTask(date) {
    document.getElementById('taskModalLabel').textContent = 'Add New Task';
    document.getElementById('task_id').value = '';
    document.getElementById('task_title').value = '';
    document.getElementById('task_description').value = '';
    document.getElementById('task_date').value = date;
    document.getElementById('task_time').value = '';
    document.getElementById('priority').value = 'medium';
    
    const modal = new bootstrap.Modal(document.getElementById('taskModal'));
    modal.show();
}

function editTask(id, title, description, date, time, priority) {
    document.getElementById('taskModalLabel').textContent = 'Edit Task';
    document.getElementById('task_id').value = id;
    document.getElementById('task_title').value = title;
    document.getElementById('task_description').value = description;
    document.getElementById('task_date').value = date;
    document.getElementById('task_time').value = time;
    document.getElementById('priority').value = priority;
    
    const modal = new bootstrap.Modal(document.getElementById('taskModal'));
    modal.show();
}

function deleteTask(taskId) {
    if (confirm('Are you sure you want to delete this task? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="delete_task" value="1">
            <input type="hidden" name="task_id" value="${taskId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function updateTaskStatus(taskId, status) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="update_task_status" value="1">
        <input type="hidden" name="task_id" value="${taskId}">
        <input type="hidden" name="status" value="${status}">
    `;
    document.body.appendChild(form);
    form.submit();
}

function previousMonth() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    generateCalendar();
}

function nextMonth() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    generateCalendar();
}
</script>

<?php include 'footer.php'; ?>
