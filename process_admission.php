<?php
// Set response header
header('Content-Type: application/json');

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Database configuration (replace with your actual credentials)
define('DB_HOST', 'http://sql5.freesqldatabase.com/');
define('DB_USER', 'sql5783876');
define('DB_PASS', 'nv7uPg74IB');
define('DB_NAME', 'sql5783876');

// Upload directory
define('UPLOAD_DIR', 'uploads/admissions/');

// Create upload directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

// Process form data
try {
    // Connect to database
    $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare student data
    $studentData = [
        'full_name' => $_POST['fullName'],
        'dob' => $_POST['dob'],
        'gender' => $_POST['gender'],
        'class_applying' => $_POST['classApplying'],
        'previous_school' => $_POST['previousSchool'] ?? null,
        'parent_name' => $_POST['parentName'],
        'relationship' => $_POST['relationship'],
        'parent_email' => $_POST['parentEmail'],
        'parent_phone' => $_POST['parentPhone'],
        'parent_address' => $_POST['parentAddress'],
        'submission_date' => date('Y-m-d H:i:s')
    ];

    // Handle file uploads
    $uploadedFiles = [];
    $fileFields = [
        'studentPhoto' => 'photo',
        'rulesDocument' => 'rules',
        'recommendationLetter' => 'recommendation',
        'schoolReport' => 'report',
        'resultsSlip' => 'results'
    ];

    foreach ($fileFields as $field => $prefix) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $fileExt = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
            $fileName = $prefix . '_' . uniqid() . '.' . $fileExt;
            $filePath = UPLOAD_DIR . $fileName;
            
            if (move_uploaded_file($_FILES[$field]['tmp_name'], $filePath)) {
                $uploadedFiles[$field] = $filePath;
                $studentData[$field] = $fileName;
            }
        }
    }

    // Insert into database
    $stmt = $db->prepare("INSERT INTO admissions (
        full_name, dob, gender, class_applying, previous_school, 
        parent_name, relationship, parent_email, parent_phone, parent_address,
        student_photo, rules_document, recommendation_letter, school_report, results_slip,
        submission_date
    ) VALUES (
        :full_name, :dob, :gender, :class_applying, :previous_school,
        :parent_name, :relationship, :parent_email, :parent_phone, :parent_address,
        :studentPhoto, :rulesDocument, :recommendationLetter, :schoolReport, :resultsSlip,
        :submission_date
    )");

    $stmt->execute($studentData);
    $admissionId = $db->lastInsertId();

    // Send confirmation email (optional)
    $to = $_POST['parentEmail'];
    $subject = 'Makindye SS Admission Application Received';
    $message = "Dear ".$_POST['parentName'].",\n\n";
    $message .= "Thank you for submitting your application to Makindye Secondary School.\n";
    $message .= "Application Reference: MSS-".str_pad($admissionId, 6, '0', STR_PAD_LEFT)."\n\n";
    $message .= "We will review your application and contact you soon.\n\n";
    $message .= "Regards,\nMakindye Secondary School Admissions Office";
    $headers = 'From: admissions@makindyesec.ac.ug';

    mail($to, $subject, $message, $headers);

    echo json_encode([
        'success' => true,
        'message' => 'Application submitted successfully! Reference: MSS-'.str_pad($admissionId, 6, '0', STR_PAD_LEFT)
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: '.$e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: '.$e->getMessage()
    ]);
}