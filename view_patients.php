<?php
include 'db.php';

// جلب جميع المرضى من قاعدة البيانات
$sql = "SELECT id, name, age, gender, phone, note FROM patients ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <title>قائمة المرضى</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f4f4f4;
            padding: 30px;
            direction: rtl;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 25px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }
        th, td {
            padding: 12px 15px;
            border: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        a.button {
            display: inline-block;
            padding: 6px 14px;
            margin: 2px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }
        a.button.delete {
            background-color: #dc3545;
        }
        a.button:hover {
            opacity: 0.85;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>قائمة المرضى</h2>
        <a href="index.php" class="button">إضافة مريض جديد</a>
        <br><br>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>الرقم</th>
                        <th>الاسم</th>
                        <th>العمر</th>
                        <th>الجنس</th>
                        <th>رقم الهاتف</th>
                        <th>ملاحظات</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['age']) ?></td>
                        <td><?= htmlspecialchars($row['gender']) ?></td>
                        <td><?= htmlspecialchars($row['phone']) ?></td>
                        <td><?= nl2br(htmlspecialchars($row['note'])) ?></td>
                        <td>
                            <a href="edit_patient.php?id=<?= $row['id'] ?>" class="button">تعديل</a>
                            <a href="delete_patient.php?id=<?= $row['id'] ?>" class="button delete" onclick="return confirm('هل أنت متأكد من حذف هذا المريض؟');">حذف</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>لا توجد سجلات للمرضى.</p>
        <?php endif; ?>
    </div>
</body>
</html>
