<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

/**
 * Configure and return a PHPMailer instance.
 */
function getMailer() {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'Khiratkarriya@gmail.com';
        $mail->Password   = 'gglo jcuz hteh gkne';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Default sender
        $mail->setFrom('Khiratkarriya@gmail.com', 'Helping Hands');
        
        return $mail;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Returns a styled HTML body for the OTP email.
 */
function getOtpEmailBody($otp) {
    return "
    <div style='font-family: \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #333;'>
        <div style='text-align: center; padding: 20px 0;'>
            <div style='font-size: 48px; margin-bottom: 10px;'>🤝</div>
            <h2 style='color: #2c3e50; margin: 0; font-weight: 700; letter-spacing: -0.5px;'>Helping Hands</h2>
            <p style='color: #7f8c8d; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;'>Donate with Heart</p>
        </div>
        
        <div style='background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%); padding: 40px; border-radius: 16px; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.05);'>
            <h1 style='color: #2c3e50; font-size: 24px; margin-bottom: 10px;'>Verification Code</h1>
            <p style='color: #576574; font-size: 16px; line-height: 1.6; margin-bottom: 30px;'>
                Thank you for joining Helping Hands! To complete your registration and activate your account, please use the 6-digit verification code below.
            </p>
            
            <div style='background: #3498db; color: #ffffff; display: inline-block; padding: 12px 30px; border-radius: 8px; font-weight: bold;'>
                <span style='font-size: 36px; letter-spacing: 8px; font-family: monospace;'>$otp</span>
            </div>
            
            <p style='color: #8395a7; font-size: 14px; margin-top: 30px;'>
                This code will expire in <strong>10 minutes</strong> for your security.
            </p>
        </div>
        
        <div style='margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #95a5a6; font-size: 12px;'>
            <p>If you did not create an account on Helping Hands, please ignore this email.</p>
            <p>&copy; 2024 Helping Hands Project. Nagpur, India.</p>
        </div>
    </div>";
}
?>
