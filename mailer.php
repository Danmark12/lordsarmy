<?php
// ============================================
// 📧 MAILER.PHP - Email Configuration
// ============================================

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer autoloader
require_once 'vendor/autoload.php';

/**
 * Send email using PHPMailer with Gmail SMTP
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $body HTML email body
 * @return bool True if sent successfully, false otherwise
 */
function sendEmailNotification($to, $subject, $body) {
    // ============================================
    // 🔧 CONFIGURATION - UPDATE THESE VALUES
    // ============================================
    $config = [
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_secure' => PHPMailer::ENCRYPTION_STARTTLS,
        'smtp_auth' => true,
        'smtp_username' => 'danmarkpetalcurin@gmail.com',  // ✅ YOUR EMAIL
        'smtp_password' => 'ngqugfixbdopzqxn',             // ✅ YOUR APP PASSWORD (no spaces)
        'from_name' => 'Lord\'s ARMY Pathfinder Club'
    ];

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $config['smtp_host'];
        $mail->SMTPAuth = $config['smtp_auth'];
        $mail->Username = $config['smtp_username'];
        $mail->Password = $config['smtp_password'];
        $mail->SMTPSecure = $config['smtp_secure'];
        $mail->Port = $config['smtp_port'];
        
        // Enable debugging (comment out in production)
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        
        // Recipients
        $mail->setFrom($config['smtp_username'], $config['from_name']);
        $mail->addAddress($to);
        $mail->addReplyTo($to);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);
        
        return $mail->send();
        
    } catch (Exception $e) {
        // Log error (optional)
        error_log("Email failed: " . $mail->ErrorInfo);
        return false;
    }
}

// ============================================
// 📝 HANDLE FORM SUBMISSIONS (if called directly)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    // Handle Contact Form
    if (isset($_POST['contact_submit'])) {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $reason = trim($_POST['reason'] ?? 'General Inquiry');
        $message = trim($_POST['message'] ?? '');
        
        if (!empty($firstName) && !empty($lastName) && !empty($email) && !empty($message)) {
            $fullName = $firstName . ' ' . $lastName;
            
            $subject = "📩 New Contact Form Submission - $fullName";
            $body = "
                <h2 style='color:#8B1A1A;'>New Message from Lord's ARMY Website</h2>
                <hr style='border-color:#C9A03D;'>
                <p><strong>Name:</strong> " . htmlspecialchars($fullName) . "</p>
                <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                <p><strong>Phone:</strong> " . htmlspecialchars($phone ?: 'Not provided') . "</p>
                <p><strong>Reason:</strong> " . htmlspecialchars($reason) . "</p>
                <p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>
                <hr style='border-color:#C9A03D;'>
                <p style='color:#7A6A55; font-size:0.85rem;'>This message was sent from the Lord's ARMY Pathfinder Club contact page.</p>
            ";
            
            if (sendEmailNotification('danmarkpetalcurin@gmail.com', $subject, $body)) {
                $response['success'] = true;
                $response['message'] = "✅ Your message has been sent successfully! We'll get back to you soon.";
            } else {
                $response['message'] = "❌ Failed to send message. Please try again or contact us directly.";
            }
        } else {
            $response['message'] = "⚠️ Please fill in all required fields.";
        }
    }
    
    // Handle Prayer Form
    if (isset($_POST['prayer_submit'])) {
        $name = trim($_POST['prayer_name'] ?? 'Anonymous');
        $prayer = trim($_POST['prayer_message'] ?? '');
        
        if (!empty($prayer)) {
            $subject = "🙏 New Prayer Request - " . ($name !== 'Anonymous' ? $name : 'Anonymous');
            $body = "
                <h2 style='color:#8B1A1A;'>🙏 New Prayer Request</h2>
                <hr style='border-color:#C9A03D;'>
                <p><strong>From:</strong> " . htmlspecialchars($name) . "</p>
                <p><strong>Prayer Request:</strong><br>" . nl2br(htmlspecialchars($prayer)) . "</p>
                <hr style='border-color:#C9A03D;'>
                <p style='color:#7A6A55; font-size:0.85rem;'>This prayer request was submitted from the Lord's ARMY Pathfinder Club website.</p>
                <p style='color:#7A6A55; font-size:0.85rem;'>May God bless this request and the one who submitted it.</p>
            ";
            
            if (sendEmailNotification('danmarkpetalcurin@gmail.com', $subject, $body)) {
                $response['success'] = true;
                $response['message'] = "✅ Your prayer request has been submitted. We're praying for you! 🙏";
            } else {
                $response['message'] = "❌ Failed to submit prayer. Please try again.";
            }
        } else {
            $response['message'] = "⚠️ Please share your prayer request.";
        }
    }
    
    // Return JSON response if AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // Redirect back with messages (for regular form submission)
    if ($response['success']) {
        header('Location: contact.php?success=' . urlencode($response['message']));
    } else {
        header('Location: contact.php?error=' . urlencode($response['message']));
    }
    exit;
}
?>