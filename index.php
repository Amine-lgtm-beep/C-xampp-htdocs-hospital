<?php
// الاتصال بقاعدة البيانات
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'hospital';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

$message = "";
$editing = false;

$edit_id = 0;
$edit_data = ['name' => '', 'age' => '', 'gender' => '', 'phone' => '', 'note' => ''];

// حذف مريض
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $check = $conn->prepare("SELECT id FROM patients WHERE id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $stmt = $conn->prepare("DELETE FROM patients WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: index.php?deleted=1");
    } else {
        header("Location: index.php?deleted=0");
    }
    exit;
}

// تحميل بيانات التعديل
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $editing = true;
        $edit_data = $result->fetch_assoc();
    }
}

// حفظ أو تعديل مريض
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = trim($_POST['name']);
    $age    = (int)$_POST['age'];
    $gender = trim($_POST['gender']);
    $phone  = trim($_POST['phone']);
    $note   = trim($_POST['note']);
    $id     = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if (!empty($name) && $age > 0 && !empty($gender)) {
        if ($id > 0) {
            // تعديل
            $stmt = $conn->prepare("UPDATE patients SET name = ?, age = ?, gender = ?, phone = ?, note = ? WHERE id = ?");
            $stmt->bind_param("sisssi", $name, $age, $gender, $phone, $note, $id);
            if ($stmt->execute()) {
                $message = "✅ تم تعديل بيانات المريض بنجاح.";
            } else {
                $message = "❌ خطأ أثناء التعديل: " . $stmt->error;
            }
        } else {
            // إضافة
            $stmt = $conn->prepare("INSERT INTO patients (name, age, gender, phone, note) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sisss", $name, $age, $gender, $phone, $note);
            if ($stmt->execute()) {
                $message = "✅ تم حفظ بيانات المريض بنجاح.";
            } else {
                $message = "❌ خطأ أثناء الحفظ: " . $stmt->error;
            }
        }
    } else {
        $message = "⚠️ يرجى إدخال الاسم، العمر، والجنس على الأقل.";
    }
}

// جلب المرضى
$result = $conn->query("SELECT * FROM patients ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة المرضى</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 20px; border-radius: 10px; }
        h2 { color: #007bff; text-align: center; }
        form input, form select, form textarea { width: 100%; margin-bottom: 10px; padding: 8px; border-radius: 4px; border: 1px solid #ccc; }
        form button { background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background: #007bff; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 10px; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 10px; }
        .delete-link { color: red; text-decoration: none; font-weight: bold; }
        .edit-link { color: #007bff; text-decoration: none; font-weight: bold; margin-left: 5px; }
    </style>
</head>
<body>
<div class="container">
    <h2><?= $editing ? 'تعديل بيانات مريض' : 'تسجيل مريض جديد' ?></h2>

    <?php if (!empty($message)): ?>
        <div class="<?= strpos($message, '✅') !== false ? 'success' : 'error' ?>"><?= $message ?></div>
    <?php elseif (isset($_GET['deleted'])): ?>
        <?php if ($_GET['deleted'] == 1): ?>
            <div class="success">✅ تم حذف المريض بنجاح.</div>
        <?php else: ?>
            <div class="error">⚠️ المريض غير موجود أو تم حذفه مسبقًا.</div>
        <?php endif; ?>
    <?php endif; ?>

    <form method="POST" action="">
        <?php if ($editing): ?>
            <input type="hidden" name="id" value="<?= $edit_id ?>">
        <?php endif; ?>

        <label>الاسم الكامل</label>
        <input type="text" name="name" required value="<?= htmlspecialchars($edit_data['name']) ?>">

        <label>العمر</label>
        <input type="number" name="age" required value="<?= htmlspecialchars($edit_data['age']) ?>">

        <label>الجنس</label>
        <select name="gender" required>
            <option value="">-- اختر --</option>
            <option value="ذكر" <?= $edit_data['gender'] == 'ذكر' ? 'selected' : '' ?>>ذكر</option>
            <option value="أنثى" <?= $edit_data['gender'] == 'أنثى' ? 'selected' : '' ?>>أنثى</option>
        </select>

        <label>رقم الهاتف</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($edit_data['phone']) ?>">

        <label>ملاحظات</label>
        <textarea name="note"><?= htmlspecialchars($edit_data['note']) ?></textarea>

        <button type="submit"><?= $editing ? 'تحديث' : 'إرسال' ?></button>
        <?php if ($editing): ?>
            <a href="index.php" style="margin-right:10px;">إلغاء التعديل</a>
        <?php endif; ?>
    </form>

    <h2>قائمة المرضى</h2>
    <table>
        <thead>
        <tr>
            <th>الرقم</th>
            <th>الاسم</th>
            <th>العمر</th>
            <th>الجنس</th>
            <th>الهاتف</th>
            <th>ملاحظات</th>
            <th>إجراءات</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= $row['age'] ?></td>
                    <td><?= $row['gender'] ?></td>
                    <td><?= $row['phone'] ?></td>
                    <td><?= nl2br(htmlspecialchars($row['note'])) ?></td>
                    <td>
                        <a href="?edit=<?= $row['id'] ?>" class="edit-link">تعديل</a>
                        <a href="?delete=<?= $row['id'] ?>" class="delete-link" onclick="return confirm('هل تريد حذف هذا المريض؟');">حذف</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">لا توجد بيانات</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>


