<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['admin_role']) || (strtolower($_SESSION['admin_role']) !== 'admin' && strtolower($_SESSION['admin_role']) !== 'super admin')) {
    http_response_code(403);
    die('Unauthorized: Admin access required');
}

// include DB connection helper
$db = require __DIR__ . '/db.php';

// Handle AJAX requests (POST for creating headers, GET with ?action=json for listing)
$isAjax = $_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'DELETE' || (isset($_GET['action']) && $_GET['action'] === 'json');

if ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET' || isset($_GET['action'])) {
        $stmt = $db->prepare('SELECT id, name, created_at FROM headers ORDER BY created_at DESC');
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
        echo json_encode(['ok' => true, 'data' => $rows]);
        exit;
    }

    if ($method === 'POST') {
        // Support both JSON and form-encoded POSTs
        $input = [];
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true) ?? [];
        } else {
            $input = $_POST;
        }

        $action = $input['action'] ?? 'create';
        
        // CREATE
        if ($action === 'create') {
            $name = trim($input['name'] ?? '');
            if ($name === '') {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Name is required']);
                exit;
            }

            $stmt = $db->prepare('INSERT INTO headers (name) VALUES (?)');
            if (!$stmt) {
                http_response_code(500);
                echo json_encode(['ok' => false, 'error' => 'Prepare failed']);
                exit;
            }
            $stmt->bind_param('s', $name);
            try {
                $stmt->execute();
                $id = $stmt->insert_id;
                echo json_encode(['ok' => true, 'data' => ['id' => $id, 'name' => $name]]);
            } catch (mysqli_sql_exception $ex) {
                error_log('Header insert error: ' . $ex->getMessage());
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Could not create header (it may already exist)']);
            }
            exit;
        }
        
        // UPDATE
        if ($action === 'update') {
            $id = intval($input['id'] ?? 0);
            $name = trim($input['name'] ?? '');
            if ($id === 0 || $name === '') {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'ID and name are required']);
                exit;
            }

            $stmt = $db->prepare('UPDATE headers SET name = ? WHERE id = ?');
            if (!$stmt) {
                http_response_code(500);
                echo json_encode(['ok' => false, 'error' => 'Prepare failed']);
                exit;
            }
            $stmt->bind_param('si', $name, $id);
            try {
                $stmt->execute();
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['ok' => true, 'data' => ['id' => $id, 'name' => $name]]);
                } else {
                    http_response_code(404);
                    echo json_encode(['ok' => false, 'error' => 'Header not found']);
                }
            } catch (mysqli_sql_exception $ex) {
                error_log('Header update error: ' . $ex->getMessage());
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Could not update header']);
            }
            exit;
        }
        
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Invalid action']);
        exit;
    }

    // DELETE
    if ($method === 'DELETE') {
        $input = [];
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true) ?? [];
        }

        $id = intval($input['id'] ?? 0);
        if ($id === 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'ID is required']);
            exit;
        }

        $stmt = $db->prepare('DELETE FROM headers WHERE id = ?');
        if (!$stmt) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Prepare failed']);
            exit;
        }
        $stmt->bind_param('i', $id);
        try {
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                echo json_encode(['ok' => true, 'message' => 'Header deleted successfully']);
            } else {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'Header not found']);
            }
        } catch (mysqli_sql_exception $ex) {
            error_log('Header delete error: ' . $ex->getMessage());
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Could not delete header']);
        }
        exit;
    }

    // Other methods
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// If not AJAX, render the HTML UI
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Header Management — NYAPUI Radio</title>
  <link rel="stylesheet" href="../styles/global.css">
  <link rel="stylesheet" href="../admin/styles/admin.css">
  <style>
    .header-container { display: flex; min-height: 100vh; }
    .header-sidebar { width: 240px; }
    .header-main { flex: 1; padding: 2rem; background: #f5f5f5; }
    .header-content { max-width: 800px; margin: 0 auto; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    h1 { color: #002a66; margin-top: 0; }
    .form-group { margin-bottom: 1.5rem; }
    label { display: block; margin-bottom: 0.5rem; font-weight: bold; color: #333; }
    input[type="text"], input[type="hidden"] { width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 6px; font-size: 1rem; box-sizing: border-box; }
    button { padding: 0.75rem 1.5rem; background: #FF8C00; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 1rem; }
    button:hover { background: #e67e00; }
    .message { padding: 1rem; margin-bottom: 1rem; border-radius: 6px; display: none; }
    .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .headers-list { margin-top: 2rem; }
    .headers-list h2 { color: #002a66; font-size: 1.3rem; }
    .header-item { display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f9f9f9; border-left: 4px solid #FF8C00; margin-bottom: 0.75rem; border-radius: 4px; }
    .header-item-content { flex: 1; }
    .header-item-content strong { color: #002a66; display: block; }
    .header-item-content small { color: #888; display: block; margin-top: 0.25rem; }
    .header-item-actions { display: flex; gap: 0.5rem; }
    .btn-edit, .btn-delete, .btn-primary, .btn-secondary { padding: 0.5rem 1rem; font-size: 0.9rem; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; }
    .btn-edit { background: #0066cc; color: white; }
    .btn-edit:hover { background: #0052a3; }
    .btn-delete { background: #cc3333; color: white; }
    .btn-delete:hover { background: #a62222; }
    .btn-primary { background: #FF8C00; color: white; }
    .btn-primary:hover { background: #e67e00; }
    .btn-secondary { background: #999; color: white; }
    .btn-secondary:hover { background: #777; }
    .muted { color: #999; font-style: italic; }
    a.back-link { color: #FF8C00; text-decoration: none; display: inline-block; margin-bottom: 1rem; }
    a.back-link:hover { text-decoration: underline; }
    .form-flex { display: flex; gap: 0.5rem; }
    .form-flex input { flex: 1; }
    
    /* Modal Styles */
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; }
    .modal-content { background: white; padding: 2rem; border-radius: 8px; max-width: 500px; width: 90%; box-shadow: 0 4px 16px rgba(0,0,0,0.2); }
    .modal-content h2 { color: #002a66; margin-top: 0; }
    .modal-content label { margin-top: 1rem; }
    .modal-actions { display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem; }
    .modal-actions button { margin: 0; }
  </style>
</head>
<body>
  <div class="header-container">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
      <div class="sidebar-logo">
        <img src="../assets/nyapui.jpeg" alt="Nyapui Radio Logo" class="sidebar-logo-img">
        <h1 class="sidebar-logo-text">NYAPUI RADIO</h1>
      </div>
      <div class="sidebar-header">
        <div class="admin-info">
          <p class="admin-role"><?php echo htmlspecialchars($_SESSION['admin_role'] ?? 'Role'); ?></p>
          <h2 class="admin-name"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></h2>
        </div>
      </div>
      <nav class="sidebar-nav">
        <ul class="sidebar-nav-list">
          <li><a href="../admin/pages/index.php" class="sidebar-nav-link">Dashboard</a></li>
          <li><a href="js/user-management.js" class="sidebar-nav-link">User Management</a></li>
          <li class="has-submenu open">
            <button class="sidebar-nav-link submenu-toggle" type="button">Web Management <span class="chev">▾</span></button>
            <ul class="submenu" style="display:flex;">
              <li><a href="pages/header.php" class="sidebar-nav-link" style="color:#FF8C00;">Header</a></li>
              <li><a href="#web-pages" class="sidebar-nav-link">Pages</a></li>
              <li><a href="#web-footer" class="sidebar-nav-link">Footer</a></li>
              <li><a href="#web-menus" class="sidebar-nav-link">Menus</a></li>
            </ul>
          </li>
          <li><a href="#programs" class="sidebar-nav-link">Programs</a></li>
          <li><a href="#news" class="sidebar-nav-link">News</a></li>
          <li><a href="#notifications" class="sidebar-nav-link">Notifications</a></li>
          <li><a href="#audit-logs" class="sidebar-nav-link">Audit Logs</a></li>
          <li><a href="#settings" class="sidebar-nav-link">Settings</a></li>
          <li><a href="../pages/login.html" class="sidebar-nav-link">Logout</a></li>
        </ul>
      </nav>
    </aside>

    <!-- Main Content -->
    <div class="header-main">
      <div class="header-content">
        <h1>Header Management</h1>

        <div id="message" class="message"></div>

        <div class="form-group">
          <label for="header-name">Add New Header</label>
          <form id="header-form" class="form-flex">
            <input id="header-name" name="name" type="text" placeholder="Enter header name" required>
            <button type="submit">Add Header</button>
          </form>
        </div>

        <div class="headers-list">
          <h2>Headers</h2>
          <div id="headers-list">
            <p class="muted">Loading headers…</p>
          </div>
        </div>
      </div>

      <!-- Edit Modal -->
      <div id="edit-modal" class="modal" style="display:none;">
        <div class="modal-content">
          <h2>Edit Header</h2>
          <form id="edit-form" class="form-group">
            <input id="edit-id" type="hidden">
            <label for="edit-name">Header Name</label>
            <input id="edit-name" type="text" placeholder="Enter header name" required>
            <div class="modal-actions">
              <button type="submit" class="btn-primary">Save</button>
              <button type="button" class="btn-secondary" onclick="closeEditModal()">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    const headersApi = window.location.href + (window.location.href.includes('?') ? '&action=json' : '?action=json');
    const headersListEl = document.getElementById('headers-list');
    const headerForm = document.getElementById('header-form');
    const headerNameInput = document.getElementById('header-name');
    const messageEl = document.getElementById('message');
    const editModal = document.getElementById('edit-modal');
    const editForm = document.getElementById('edit-form');
    const editIdInput = document.getElementById('edit-id');
    const editNameInput = document.getElementById('edit-name');

    function showMessage(text, type = 'success') {
      messageEl.textContent = text;
      messageEl.className = 'message ' + type;
      messageEl.style.display = 'block';
      setTimeout(() => { messageEl.style.display = 'none'; }, 4000);
    }

    function openEditModal(id, name) {
      editIdInput.value = id;
      editNameInput.value = name;
      editModal.style.display = 'flex';
    }

    function closeEditModal() {
      editModal.style.display = 'none';
    }

    async function loadHeaders(){
      headersListEl.innerHTML = '<p class="muted">Loading headers…</p>';
      try {
        const res = await fetch(headersApi);
        const json = await res.json();
        if(json.ok){
          if(json.data.length === 0){
            headersListEl.innerHTML = '<p class="muted">No headers yet. Add one above!</p>';
            return;
          }
          const div = document.createElement('div');
          json.data.forEach(h => {
            const item = document.createElement('div');
            item.className = 'header-item';
            item.id = 'header-' + h.id;
            item.innerHTML = `
              <div class="header-item-content">
                <strong>${h.name}</strong>
                <small>Created: ${h.created_at}</small>
              </div>
              <div class="header-item-actions">
                <button type="button" class="btn-edit" onclick="openEditModal(${h.id}, '${h.name.replace(/'/g, "\\'")}')">Edit</button>
                <button type="button" class="btn-delete" onclick="deleteHeader(${h.id})">Delete</button>
              </div>
            `;
            div.appendChild(item);
          });
          headersListEl.innerHTML = '';
          headersListEl.appendChild(div);
        } else {
          headersListEl.innerHTML = '<p class="muted">Failed to load headers.</p>';
        }
      } catch(err){
        headersListEl.innerHTML = '<p class="muted">Error loading headers.</p>';
        console.error(err);
      }
    }

    headerForm.addEventListener('submit', async function(e){
      e.preventDefault();
      const name = headerNameInput.value.trim();
      if(!name) return;
      try {
        const postUrl = window.location.href.split('?')[0];
        const res = await fetch(postUrl, {
          method: 'POST',
          headers: {'Content-Type':'application/json'},
          body: JSON.stringify({action: 'create', name})
        });
        const json = await res.json();
        if(json.ok){
          headerNameInput.value = '';
          showMessage('Header added successfully!', 'success');
          await loadHeaders();
        } else {
          showMessage(json.error || 'Could not create header', 'error');
        }
      } catch(err){
        showMessage('Network error', 'error');
        console.error(err);
      }
    });

    editForm.addEventListener('submit', async function(e){
      e.preventDefault();
      const id = parseInt(editIdInput.value);
      const name = editNameInput.value.trim();
      if(!id || !name) return;
      try {
        const postUrl = window.location.href.split('?')[0];
        const res = await fetch(postUrl, {
          method: 'POST',
          headers: {'Content-Type':'application/json'},
          body: JSON.stringify({action: 'update', id, name})
        });
        const json = await res.json();
        if(json.ok){
          closeEditModal();
          showMessage('Header updated successfully!', 'success');
          await loadHeaders();
        } else {
          showMessage(json.error || 'Could not update header', 'error');
        }
      } catch(err){
        showMessage('Network error', 'error');
        console.error(err);
      }
    });

    async function deleteHeader(id) {
      if (!confirm('Are you sure you want to delete this header?')) return;
      try {
        const postUrl = window.location.href.split('?')[0];
        const res = await fetch(postUrl, {
          method: 'DELETE',
          headers: {'Content-Type':'application/json'},
          body: JSON.stringify({id})
        });
        const json = await res.json();
        if(json.ok){
          showMessage('Header deleted successfully!', 'success');
          await loadHeaders();
        } else {
          showMessage(json.error || 'Could not delete header', 'error');
        }
      } catch(err){
        showMessage('Network error', 'error');
        console.error(err);
      }
    }

    // Sidebar submenu toggle
    document.querySelectorAll('.submenu-toggle').forEach(function(btn){
      btn.addEventListener('click', function(){
        var parent = btn.closest('.has-submenu');
        parent.classList.toggle('open');
      });
    });

    // Load headers on page load
    loadHeaders();
  </script>
</body>
</html>
