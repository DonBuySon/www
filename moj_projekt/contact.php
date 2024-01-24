<?php
include 'cfg.php';
if (isset($_POST['submit_contact'])) {
    unset($_POST['submit_contact']);
}
if (isset($_POST['submit_password_reminder'])) {
    unset($_POST['submit_password_reminder']);
}

// ------------------------
// Function: Display Contact Form
// ------------------------
function PokazKontakt()
{
    echo '
        <form method="post" action="">

            <label for="email">Email:</label>
            <input type="email" name="email" required /><br />

            <label for="subject">Temat:</label>
            <input type="text" name="subject" required /><br />

            <label for="message">Wiadomość:</label>
            <textarea name="message" rows="4" required></textarea><br />

            <input type="submit" name="submit_contact" value="Send Message" />
            
             <input type="submit" name="submit_password_reminder" value="Remember Password" />
        </form>
    ';
}

// ------------------------
// Function: Password Reminder
// ------------------------
function PrzypomnijHaslo()
{
    if (isset($_POST['submit_password_reminder'])) {
        $email = $_POST['email'];

        $newPassword = generateRandomPassword();

        $mailSubject = 'Przypominanie hasła';
        $mailBody = 'Twoje nowe hasło to: ' . $newPassword;

        $header = "From: Password Reminder <noreply@example.com>\n";
        $header .= "MIME-Version: 1.0\nContent-Type: text/plain; charset=utf-8\nContent-Transfer-Encoding:";
        $header .= "X-Mailer: PRapwww mail 1.2\n";
        $header .= "X-Priority: 3\n";
        $header .= "Return-Path: <noreply@example.com>\n";

        mail($email, $mailSubject, $mailBody, $header);

        echo '[password_reminder_sent]';
        $pass = $newPassword;
    }
}

// ------------------------
// Function: Send Contact Email
// ------------------------
function WyslijMailKontakt($odbiorca)
{
    if (empty($_POST['subject']) || empty($_POST['message'] || empty($_POST['email']))) {
        echo '[nie wypelniles pola]';
        PokazKontakt();
    } else {
        $mail['subject'] = $_POST['subject'];
        $mail['body'] = $_POST['message'];
        $mail['sender'] = $_POST['email'];
        $mail['recipient'] = $odbiorca;
        $header = "From: Formularz kontaktowy <" . $mail['sender'] . ">\n";
        $header .= "MIME-Version: 1.0\ncontent-Type: text/plain; charset=utf-8\nContent-Transfer-Encoding:";
        $header .= "x-Sender: <" . $mail['sender'] . ">\n";
        $header .= "X-Mailer: PRapwww mail 1.2\n";
        $header .= "x-Priority: 3\n";
        $header .= "Return-Path: <" . $mail['sender'] . ">\n";
        mail($mail["reciptient"], $mail['subject'], $mail["body"], $header);
        echo '[wiadomosc_wyslana]';
    }
}

// ------------------------
// Function: Generate Random Password
// ------------------------
function generateRandomPassword($length = 8)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';
    $charLength = strlen($characters) - 1;

    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[mt_rand(0, $charLength)];
    }

    return $password;
}

PokazKontakt();
if (isset($_POST['submit_contact'])) {
    WyslijMailKontakt('jakub.balisnki@matman.uwm.edu.pl');
}
PrzypomnijHaslo();
