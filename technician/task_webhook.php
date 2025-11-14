elseif ($action === 'update_status') {
    $task_id = $data['task_id'] ?? 0;
    $new_status = $data['new_status'] ?? '';
    $remarks = $data['remarks'] ?? '';

    if ($task_id && $new_status) {
        if ($new_status === 'completed') {
            $stmt = $conn->prepare("UPDATE tasks SET status = ?, remarks = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("ssi", $new_status, $remarks, $task_id);
        } else {
            $stmt = $conn->prepare("UPDATE tasks SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("si", $new_status, $task_id);
        }

        if ($stmt->execute()) {
            $response['success'] = true;
        } else {
            $response['success'] = false;
            $response['message'] = "Database update failed.";
        }
        $stmt->close();
    } else {
        $response['success'] = false;
        $response['message'] = "Invalid input.";
    }
}
