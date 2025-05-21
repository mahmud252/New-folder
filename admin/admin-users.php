<?php
$users = getAllUsers();
foreach ($users as $user) {
    echo "<tr>";
    echo "<td>{$user['username']}</td>";
    echo "<td>{$user['status']}</td>";
    echo "<td>
        <form method='POST' action='toggle-status.php' style='display:inline-block'>
            <input type='hidden' name='user_id' value='{$user['id']}'>
            <input type='hidden' name='new_status' value='" . ($user['status'] === 'active' ? 'inactive' : 'active') . "'>
            <button type='submit'>" . ($user['status'] === 'active' ? 'Disable' : 'Enable') . "</button>
        </form>
    </td>";
    echo "</tr>";
}


if (isset($_GET['success'])) {
    echo "<p style='color:green;'>User status updated successfully.</p>";
}

?>
