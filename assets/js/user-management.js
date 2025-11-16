// User Management CRUD logic
// Assumes backend at ../../backend/users.php

// Only initialize user management when the section is shown
function initUserManagement() {
  if (window.userManagementInitialized) return;
  window.userManagementInitialized = true;
  const usersTable = document.getElementById('usersTable').querySelector('tbody');
  const userModal = document.getElementById('userModal');
  const userForm = document.getElementById('userForm');
  const addUserBtn = document.getElementById('addUserBtn');
  const cancelModal = document.getElementById('cancelModal');
  let editingId = null;

  function openModal(edit = false, user = {}) {
    userModal.hidden = false;
    document.getElementById('modalTitle').textContent = edit ? 'Edit User' : 'Add User';
    userForm.reset();
    editingId = edit ? user.id : null;
    userForm.userId.value = user.id || '';
    userForm.username.value = user.username || '';
    userForm.password.value = '';
    userForm.role.value = user.role || 'user';
    if (edit) userForm.password.required = false; else userForm.password.required = true;
  }
  function closeModal() { userModal.hidden = true; editingId = null; }

  addUserBtn.onclick = () => openModal(false);
  cancelModal.onclick = closeModal;
  userModal.onclick = e => { if (e.target === userModal) closeModal(); };

  // Load users
  function loadUsers() {
    fetch('../../backend/users.php?action=read')
      .then(r => r.json())
      .then(data => {
        usersTable.innerHTML = '';
        data.forEach(user => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${user.id}</td>
            <td>${user.username}</td>
            <td>${user.role}</td>
            <td class="action-btns">
              <button class="btn btn-secondary edit-btn" data-id="${user.id}">Edit</button>
              <button class="btn btn-secondary delete-btn" data-id="${user.id}">Delete</button>
            </td>
          `;
          usersTable.appendChild(tr);
        });
      });
  }
  loadUsers();

  // Add/Edit user
  userForm.onsubmit = function(e) {
    e.preventDefault();
    const formData = new FormData(userForm);
    let action = editingId ? 'update' : 'create';
    fetch('../../backend/users.php?action=' + action, {
      method: 'POST',
      body: formData
    })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        closeModal();
        loadUsers();
      } else {
        alert(res.error || 'Error saving user');
      }
    });
  };

  // Edit/Delete buttons
  usersTable.onclick = function(e) {
    if (e.target.classList.contains('edit-btn')) {
      const id = e.target.dataset.id;
      fetch('../../backend/users.php?action=readOne&id=' + id)
        .then(r => r.json())
        .then(user => openModal(true, user));
    }
    if (e.target.classList.contains('delete-btn')) {
      if (confirm('Delete this user?')) {
        const id = e.target.dataset.id;
        fetch('../../backend/users.php?action=delete', {
          method: 'POST',
          body: new URLSearchParams({id})
        })
        .then(r => r.json())
        .then(res => {
          if (res.success) loadUsers();
          else alert(res.error || 'Delete failed');
        });
      }
    }
  };
}

// Listen for sidebar click to show user management
document.addEventListener('DOMContentLoaded', function() {
  const userPanel = document.getElementById('user-management');
  const sidebarLinks = document.querySelectorAll('.sidebar-nav-link');
  sidebarLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      if (this.getAttribute('href') === '#user-management') {
        e.preventDefault();
        // Hide all panels (if you add more in future)
        document.querySelectorAll('.admin-content > .panel').forEach(p => p.style.display = 'none');
        userPanel.style.display = '';
        initUserManagement();
      }
    });
  });
});
