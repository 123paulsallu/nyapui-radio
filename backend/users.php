<?php
// Simple RESTful user CRUD for admin dashboard
header('Content-Type: application/json');
require_once '../sql/db.php'; // You must create this file to connect to your DB

function respond($ok, $data = null) {
  echo json_encode($ok ? ($data ?? ["success" => true]) : ["success" => false, "error" => $data]);
  exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
  case 'read':
    $stmt = $pdo->query('SELECT id, username, role FROM users ORDER BY id DESC');
    respond(true, $stmt->fetchAll(PDO::FETCH_ASSOC));
    break;
  case 'readOne':
    $id = $_GET['id'] ?? 0;
    $stmt = $pdo->prepare('SELECT id, username, role FROM users WHERE id=?');
    $stmt->execute([$id]);
    respond(true, $stmt->fetch(PDO::FETCH_ASSOC));
    break;
  case 'create':
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    if (!$username || !$password) respond(false, 'Username and password required');
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
    $ok = $stmt->execute([$username, $hash, $role]);
    respond($ok);
    break;
  case 'update':
    $id = $_POST['id'] ?? 0;
    $username = $_POST['username'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $password = $_POST['password'] ?? '';
    if (!$id || !$username) respond(false, 'ID and username required');
    if ($password) {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare('UPDATE users SET username=?, password=?, role=? WHERE id=?');
      $ok = $stmt->execute([$username, $hash, $role, $id]);
    } else {
      $stmt = $pdo->prepare('UPDATE users SET username=?, role=? WHERE id=?');
      $ok = $stmt->execute([$username, $role, $id]);
    }
    respond($ok);
    break;
  case 'delete':
    $id = $_POST['id'] ?? 0;
    if (!$id) respond(false, 'ID required');
    $stmt = $pdo->prepare('DELETE FROM users WHERE id=?');
    $ok = $stmt->execute([$id]);
    respond($ok);
    break;
  default:
    respond(false, 'Invalid action');
}
