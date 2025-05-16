<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard | Task Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
  <a class="navbar-brand" href="#">Task Management</a>
  <div class="ms-auto">
    <button onclick="logout()" class="btn btn-outline-light btn-sm">Logout</button>
  </div>
</nav>

<div class="container mt-4">
  <h3>Welcome, <span id="userName"></span> (<span id="userRole"></span>)</h3>

  <div id="taskSection" class="mt-4">
    <h4>Tasks</h4>
    <button class="btn btn-success mb-3" onclick="openTaskModal()">+ Create Task</button>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Title</th>
          <th>Status</th>
          <th>Due Date</th>
          <th>Assigned To</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="taskTableBody">
        <!-- tasks will be rendered here -->
      </tbody>
    </table>
  </div>

  {{-- user --}}
  <div id="userSection" style="display: none;">
    <h4>Users</h4>

      <form id="userForm" class="mb-4" onsubmit="createUser(event)" style="display: none">
        <div class="row g-3">
          <div class="col-md-3">
            <input type="text" id="name" class="form-control" placeholder="Name" required />
          </div>
          <div class="col-md-3">
            <input type="email" id="email" class="form-control" placeholder="Email" required />
          </div>
          <div class="col-md-2">
            <select id="role" class="form-select" required>
              <option value="">Select Role</option>
              <option value="admin">Admin</option>
              <option value="manager">Manager</option>
              <option value="staff">Staff</option>
            </select>
          </div>
          <div class="col-md-2">
            <select id="status" class="form-select" required>
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
          <div class="col-md-2">
            <input type="password" id="password" class="form-control" placeholder="Password" required minlength="6" />
          </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Create User</button>
      </form>

        <table class="table table-bordered">
          <thead>
          <tr>
            <th>Name</th><th>Email</th><th>Role</th><th>Status</th>
          </tr>
          </thead>
          <tbody id="userTableBody">
              <!-- user list here -->
          </tbody>
        </table>
  </div>

  <div id="activityLog" style="display: none;">
      <h5 class="mt-5">Activity Logs</h5>
      <table class="table table-bordered">
      <thead>
          <tr>
          <th>Timestamp</th>
          <th>User</th>
          <th>Activity</th>
          </tr>
      </thead>
      <tbody id="activityLogTableBody">
          <!-- logs will load here -->
      </tbody>
      </table>
      <button class="btn btn-sm btn-secondary" onclick="loadActivityLogs()">Refresh Logs</button>
  </div>
</div>

<!-- Modal for Create/Edit Task -->
<div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="taskForm" class="modal-content" onsubmit="saveTask(event)" action="/api/tasks">
      <div class="modal-header">
        <h5 class="modal-title" id="taskModalLabel">Create Task</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="taskId" />
        <div class="mb-3">
          <label for="taskTitle" class="form-label">Title</label>
          <input type="text" id="taskTitle" class="form-control" required />
        </div>
        <div class="mb-3">
          <label for="taskDescription" class="form-label">Description</label>
          <textarea id="taskDescription" class="form-control" rows="3" required></textarea>
        </div>
        <div class="mb-3">
          <label for="taskAssignedTo" class="form-label">Assign To</label>
          <select id="taskAssignedTo" class="form-select" required></select>
        </div>
        <div class="mb-3">
          <label for="taskStatus" class="form-label">Status</label>
          <select id="taskStatus" class="form-select" required>
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="done">Done</option>
          </select>
        </div>
        <div class="mb-3">
          <label for="taskDueDate" class="form-label">Due Date</label>
          <input type="date" id="taskDueDate" class="form-control" required />
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Task</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const token = localStorage.getItem('token');
  const user = JSON.parse(localStorage.getItem('user'));

  if (!token || !user) window.location.href = '/';

  document.getElementById('userName').textContent = user.name;
  document.getElementById('userRole').textContent = user.role;

  if (user.role !== 'staff'){
    document.getElementById('userSection').style.display = 'block';
  }

  // Show admin section if user is admin
  if (user.role === 'admin') {
    document.getElementById('activityLog').style.display = 'block';
    document.getElementById('userForm').style.display = 'block';
    loadUsers();
    loadActivityLogs();
  }

  if (user.role === 'manager') {
    loadUsers();
  }

  let taskModal = new bootstrap.Modal(document.getElementById('taskModal'));

  async function fetchAPI(url, options = {}) {
    options.headers = { ...(options.headers || {}), Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' };
    const res = await fetch(url, options);
    if (!res.ok) {
      const err = await res.json();
      throw new Error(err.message || 'Request failed');
    }
    return await res.json();
  }

  async function loadTasks() {
    try {
      const tasks = await fetchAPI('http://localhost:8000/api/tasks');

      let filteredTasks = tasks;

      if(user.role === 'staff') {
        filteredTasks = tasks.filter(task => task.assigned_to === user.id)
      }

      renderTasks(filteredTasks);
    } catch (e) {
      alert(e.message);
    }
  }

  function renderTasks(tasks) {
    const tbody = document.getElementById('taskTableBody');
    tbody.innerHTML = '';
    tasks.forEach(task => {
      const assignedTo = task.assigned_to_name || task.assigned_to;
      const canEdit = user.role === 'admin' || user.id === task.created_by || user.id === task.created_by;
      const canDelete = canEdit;

      const statusBadge = {
        pending: 'badge bg-warning text-dark',
        in_progress: 'badge bg-primary',
        done: 'badge bg-success'
      }[task.status] || 'badge bg-secondary';

      tbody.insertAdjacentHTML('beforeend', `
        <tr>
          <td>${task.title}</td>
          <td><span class="${statusBadge}">${task.status.replace('_', ' ')}</span></td>
          <td>${task.due_date}</td>
          <td>${assignedTo}</td>
          <td>
            <button class="btn btn-sm btn-secondary me-1" onclick="viewTask('${task.id}')">View</button>
            ${canEdit ? `<button class="btn btn-sm btn-info me-1" onclick="openTaskModal('${task.id}')">Edit</button>` : ''}
            ${canDelete ? `<button class="btn btn-sm btn-danger" onclick="deleteTask('${task.id}')">Delete</button>` : ''}
          </td>
        </tr>
      `);
    });
  }

  async function openTaskModal(id = '') {
    clearTaskForm();
    await loadAssignableUsers();

    if (id) {
      const tasks = await fetchAPI('http://localhost:8000/api/tasks');
      const task = tasks.find(t => t.id === id);

      if (!task) return alert('Task not found');
      document.getElementById('taskId').value = task.id;
      document.getElementById('taskTitle').value = task.title;
      document.getElementById('taskDescription').value = task.description;
      document.getElementById('taskAssignedTo').value = task.assigned_to;
      document.getElementById('taskStatus').value = task.status;
      document.getElementById('taskDueDate').value = task.due_date;
      document.getElementById('taskModalLabel').textContent = 'Edit Task';
    } else {
      document.getElementById('taskModalLabel').textContent = 'Create Task';
    }
    taskModal.show();
  }

  function clearTaskForm() {
    document.getElementById('taskId').value = '';
    document.getElementById('taskTitle').value = '';
    document.getElementById('taskDescription').value = '';
    document.getElementById('taskAssignedTo').innerHTML = '';
    document.getElementById('taskStatus').value = 'pending';
    document.getElementById('taskDueDate').value = '';
  }

    async function loadAssignableUsers() {
        try {
            const users = await fetchAPI('http://localhost:8000/api/users');
            const select = document.getElementById('taskAssignedTo');
            select.innerHTML = '';
            users.forEach(u => {
                if (user.role === 'staff') {
                    if (u.id === user.id) {
                        select.insertAdjacentHTML('beforeend', `<option value="${u.id}">${u.name} (${u.role})</option>`);
                    }
                } else if (u.status === 1 && (user.role !== 'manager' || u.role === 'staff')) {
                    select.insertAdjacentHTML('beforeend', `<option value="${u.id}">${u.name} (${u.role})</option>`);
                }
            });
        } catch (e) {
            alert('Failed to load users');
        }
    }

  async function saveTask(e) {
    e.preventDefault();
    const id = document.getElementById('taskId').value;
    const payload = {
      title: document.getElementById('taskTitle').value.trim(),
      description: document.getElementById('taskDescription').value.trim(),
      assigned_to: document.getElementById('taskAssignedTo').value,
      status: document.getElementById('taskStatus').value,
      due_date: document.getElementById('taskDueDate').value,
    };

    if (!payload.title || !payload.description || !payload.assigned_to || !payload.due_date) {
      return alert('Please fill all fields');
    }

    try {
      if (id) {
        await fetchAPI(`http://localhost:8000/api/tasks/${id}`, {
          method: 'PUT',
          body: JSON.stringify(payload),
        });
      } else {
        await fetchAPI(`http://localhost:8000/api/tasks`, {
          method: 'POST',
          body: JSON.stringify(payload),
        });
      }
      taskModal.hide();
      loadTasks();
    } catch (e) {
      alert(e.message);
    }
  }

  async function viewTask(id) {
    const tasks = await fetchAPI('http://localhost:8000/api/tasks');
    const task = tasks.find(t => t.id === id);

    if (!task) return alert('Task not found');

    const detail = `
        <b>Title:</b> ${task.title}<br>
        <b>Description:</b> ${task.description}<br>
        <b>Status:</b> ${task.status.replace('_', ' ')}<br>
        <b>Due Date:</b> ${task.due_date}<br>
        <b>Assigned To:</b> ${task.assigned_to_name || task.assigned_to}
    `;

    const modal = new bootstrap.Modal(document.createElement('div'));
    const container = document.createElement('div');
    container.innerHTML = `
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Task Details</h5>
                <button type="button" class="btn-close" onclick="this.closest('.modal').remove();"></button>
            </div>
            <div class="modal-body">${detail}</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="this.closest('.modal').remove();">Close</button>
            </div>
            </div>
        </div>
        </div>
    `;
    document.body.appendChild(container);
    }


  async function deleteTask(id) {
    if (!confirm('Are you sure to delete this task?')) return;
    try {
      await fetchAPI(`http://localhost:8000/api/tasks/${id}`, {
        method: 'DELETE',
      });
      loadTasks();
    } catch (e) {
      alert(e.message);
    }
  }

  // USER ADMIN SECTION

  async function loadUsers() {
    try {
      const users = await fetchAPI('http://localhost:8000/api/users');
      const tbody = document.getElementById('userTableBody');
      tbody.innerHTML = '';
      users.forEach(u => {
        tbody.insertAdjacentHTML('beforeend', `
          <tr>
            <td>${u.name}</td>
            <td>${u.email}</td>
            <td>${u.role}</td>
            <td>${u.status == 1 ? 'Active' : 'Inactive'}</td>
          </tr>
        `);
      });
    } catch (e) {
      alert('Failed to load users');
    }
  }

  async function createUser(e) {
    e.preventDefault();

    const payload = {
      name: document.getElementById('name').value.trim(),
      email: document.getElementById('email').value.trim(),
      role: document.getElementById('role').value,
      status: parseInt(document.getElementById('status').value),
      password: document.getElementById('password').value,
    };

    console.log(payload)

    if (!payload.name || !payload.email || !payload.role || !payload.password) {
      return alert('Please fill all required fields');
    }

    try {
      await fetchAPI('http://localhost:8000/api/users', {
        method: 'POST',
        body: JSON.stringify(payload),
      });
      alert('User created successfully');
      document.getElementById('userForm').reset();
      loadUsers();
    } catch (e) {
      alert(e.message);
    }
  }

  async function loadActivityLogs() {
  try {
    const logs = await fetchAPI('http://localhost:8000/api/logs');
    const tbody = document.getElementById('activityLogTableBody');
    tbody.innerHTML = '';

    logs.forEach(log => {
      const time = new Date(log.created_at).toLocaleString();
      tbody.insertAdjacentHTML('beforeend', `
        <tr>
          <td>${time}</td>
          <td>${log.user_name || log.user_id}</td>
          <td>${log.activity}</td>
        </tr>
      `);
    });

  } catch (e) {
    alert('Failed to load activity logs');
  }
}


  function logout() {
    localStorage.clear();
    window.location.href = '/';
  }

  // Load initial data
  loadTasks();

</script>
</body>
</html>
