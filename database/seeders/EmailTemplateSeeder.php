<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Password Reset',
                'slug' => 'password-reset',
                'subject' => 'Reset Your Password - {{app_name}}',
                'body' => '<div class="content">
    <h2>Password Reset Request</h2>
    <p>Hello {{name}},</p>
    <p>You are receiving this email because we received a password reset request for your account.</p>
    <p>Click the button below to reset your password:</p>
    <div style="text-align: center;">
        <a href="{{reset_link}}" class="button">Reset Password</a>
    </div>
    <p>This password reset link will expire in 60 minutes.</p>
    <p>If you did not request a password reset, no further action is required.</p>
    <p><strong>For security reasons, we do not include your email address in this message.</strong></p>
    <p>Best regards,<br>The {{app_name}} Team</p>
    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #dee2e6;">
        <p><small>If you\'re having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:</small></p>
        <p style="word-break: break-all; font-size: 12px;">{{reset_link}}</p>
    </div>
</div>',
                'type' => 'password_reset',
                'variables' => ['name', 'reset_link', 'app_name'],
                'is_active' => true,
                'is_deletable' => false,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Welcome Email',
                'slug' => 'welcome-email',
                'subject' => 'Welcome to {{app_name}}!',
                'body' => '<div class="content">
    <h2 style="color: #245dac;">Welcome to {{app_name}}!</h2>
    <p>Hello {{name}}!</p>
    <p>Welcome to {{app_name}}! We\'re excited to have you on board.</p>
    <p>Your account has been successfully created with the email address: <strong>{{email}}</strong></p>
    <p>You can now access your dashboard and start exploring all the features we have to offer.</p>
    <div style="text-align: center;">
        <a href="{{dashboard_link}}" class="button">Go to Dashboard</a>
    </div>
    <p>If you have any questions or need assistance, feel free to contact our support team.</p>
    <p>Best regards,<br>The {{app_name}} Team</p>
</div>',
                'type' => 'welcome',
                'variables' => ['name', 'email', 'dashboard_link', 'app_name'],
                'is_active' => true,
                'is_deletable' => false,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Email Verification',
                'slug' => 'email-verification',
                'subject' => 'Verify Your Email Address - {{app_name}}',
                'body' => '<div class="content">
                    <h2 style="color: #245dac;">Verify Your Email Address</h2>
                    <p>Hello {{name}},</p>
                    <p>Thank you for registering with {{app_name}}! To complete your registration and activate your account, please verify your email address.</p>
                    <p>Click the button below to verify your email address:</p>
                    <div style="text-align: center;">
                        <a href="{{verification_link}}" class="button">Verify Email Address</a>
                    </div>
                    <p>This verification link will expire in 24 hours for security reasons.</p>
                    <p>Once your email is verified, you will receive a welcome email and can start using all the features of {{app_name}}.</p>
                    <p>If you did not create an account with us, please ignore this email.</p>
                    <p>Best regards,<br>The {{app_name}} Team</p>
                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                        <p><small>If you\'re having trouble clicking the "Verify Email Address" button, copy and paste the URL below into your web browser:</small></p>
                        <p style="word-break: break-all; font-size: 12px;">{{verification_link}}</p>
                    </div>
                </div>',
                'type' => 'email_verification',
                'variables' => ['name', 'email', 'verification_link', 'app_name'],
                'is_active' => true,
                'is_deletable' => false,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Two-Factor Authentication Code',
                'slug' => 'two-factor-auth-code',
                'subject' => 'Your Two-Factor Authentication Code - {{app_name}}',
                'body' => '<div class="content">
                    <h2 style="color: #245dac;">Two-Factor Authentication Code</h2>
                    <p>Hello {{name}},</p>
                    <p>You have requested a two-factor authentication code for your account. Please use the code below to complete your login:</p>
                    <div style="background-color: #f8f9fa; border: 2px solid #245dac; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0;">
                        <p><strong>Your verification code is:</strong></p>
                        <div style="font-size: 32px; font-weight: bold; color: #245dac; letter-spacing: 5px; margin: 10px 0;">{{code}}</div>
                        <p><small>This code will expire in {{expires_in}} minutes</small></p>
                    </div>
                    <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px; margin: 20px 0; color: #856404;">
                        <strong>Security Notice:</strong>
                        <ul style="margin: 10px 0; padding-left: 20px;">
                            <li>This code is valid for {{expires_in}} minutes only</li>
                            <li>Do not share this code with anyone</li>
                            <li>If you didn\'t request this code, please secure your account immediately</li>
                        </ul>
                    </div>
                    <p>If you\'re having trouble logging in, you can also use one of your recovery codes as an alternative.</p>
                    <p>If you didn\'t request this verification code, please ignore this email or contact our support team if you have concerns about your account security.</p>
                    <p>Best regards,<br>The {{app_name}} Team</p>
                </div>',
                'type' => 'two_factor_auth',
                'variables' => ['name', 'code', 'expires_in', 'app_name'],
                'is_active' => true,
                'is_deletable' => false,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Email Verification OTP',
                'slug' => 'email-verification-otp',
                'subject' => 'Verify Your Email Address - {{app_name}}',
                'body' => '<div class="content">
                    <h2 style="color: #245dac;">Verify Your Email Address</h2>
                    <p>Hello {{name}},</p>
                    <p>Thank you for registering with {{app_name}}! To complete your registration and activate your account, please verify your email address using the code below:</p>
                    <div style="background-color: #f8f9fa; border: 2px solid #245dac; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0;">
                        <p><strong>Your verification code is:</strong></p>
                        <div style="font-size: 32px; font-weight: bold; color: #245dac; letter-spacing: 5px; margin: 10px 0;">{{otp}}</div>
                        <p><small>This code will expire in {{expires_in}} minutes</small></p>
                    </div>
                    <div style="background-color: #d1ecf1; border: 1px solid #bee5eb; border-radius: 5px; padding: 15px; margin: 20px 0; color: #0c5460;">
                        <strong>Important:</strong>
                        <ul style="margin: 10px 0; padding-left: 20px;">
                            <li>Enter this code in the app to verify your email</li>
                            <li>This code is valid for {{expires_in}} minutes only</li>
                            <li>Do not share this code with anyone</li>
                        </ul>
                    </div>
                    <p>Once your email is verified, you can start using all the features of {{app_name}}.</p>
                    <p>If you did not create an account with us, please ignore this email.</p>
                    <p>Best regards,<br>The {{app_name}} Team</p>
                </div>',
                'type' => 'email_verification',
                'variables' => ['name', 'email', 'otp', 'expires_in', 'app_name'],
                'is_active' => true,
                'is_deletable' => false,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Google Account Disconnect OTP',
                'slug' => 'google-disconnect-otp',
                'subject' => 'Verify Google Account Disconnection - {{app_name}}',
                'body' => '<div class="content">
                    <h2 style="color: #f39c12;">Google Account Disconnection Verification</h2>
                    <p>Hello {{name}},</p>
                    <p>You have requested to disconnect your Google account from {{app_name}}. To confirm this action, please use the following verification code:</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <div style="background-color: #fff3cd; border: 2px dashed #f39c12; padding: 20px; border-radius: 8px; display: inline-block;">
                            <h1 style="color: #f39c12; margin: 0; font-size: 32px; letter-spacing: 5px; font-family: monospace;">{{otp_code}}</h1>
                        </div>
                    </div>
                    <p><strong>Important:</strong> After disconnecting, you will need to use your password to sign in to {{app_name}}.</p>
                    <p>This verification code will expire in <strong>10 minutes</strong> for security reasons.</p>
                    <p>If you did not request this disconnection, please ignore this email and ensure your account is secure.</p>
                    <p>Best regards,<br>The {{app_name}} Team</p>
                </div>',
                'type' => 'account_management',
                'variables' => ['name', 'otp_code', 'app_name'],
                'is_active' => true,
                'is_deletable' => false,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Google Account Disconnect Confirmation',
                'slug' => 'google-disconnect-confirmation',
                'subject' => 'Google Account Disconnected - {{app_name}}',
                'body' => '<div class="content">
                    <h2 style="color: #28a745;">Google Account Successfully Disconnected</h2>
                    <p>Hello {{name}},</p>
                    <p>Your Google account has been successfully disconnected from your {{app_name}} account.</p>
                    <p><strong>What this means:</strong></p>
                    <ul>
                        <li>You can no longer sign in using "Continue with Google"</li>
                        <li>You must use your email and password to sign in</li>
                        <li>Your account data and settings remain unchanged</li>
                    </ul>
                    <p>If you want to reconnect your Google account in the future, you can do so from your profile settings after signing in.</p>
                    <p>If you did not request this disconnection, please contact our support team immediately.</p>
                    <p>Best regards,<br>The {{app_name}} Team</p>
                </div>',
                'type' => 'account_management',
                'variables' => ['name', 'app_name'],
                'is_active' => true,
                'is_deletable' => false,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Account Deletion OTP',
                'slug' => 'account-deletion-otp',
                'subject' => 'Verify Account Deletion - {{app_name}}',
                'body' => '<div class="content">
                    <h2 style="color: #dc3545;">Account Deletion Verification</h2>
                    <p>Hello {{name}},</p>
                    <p>You have requested to permanently delete your {{app_name}} account. To confirm this action, please use the following verification code:</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <div style="background-color: #f8d7da; border: 2px dashed #dc3545; padding: 20px; border-radius: 8px; display: inline-block;">
                            <h1 style="color: #dc3545; margin: 0; font-size: 32px; letter-spacing: 5px; font-family: monospace;">{{otp_code}}</h1>
                        </div>
                    </div>
                    <p><strong>⚠️ WARNING:</strong> This action will permanently delete your account and all associated data. This cannot be undone.</p>
                    <p>This verification code will expire in <strong>10 minutes</strong> for security reasons.</p>
                    <p>If you did not request this deletion, please ignore this email and ensure your account is secure.</p>
                    <p>Best regards,<br>The {{app_name}} Team</p>
                </div>',
                'type' => 'account_management',
                'variables' => ['name', 'otp_code', 'app_name'],
                'is_active' => true,
                'is_deletable' => false,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Account Deletion Confirmation',
                'slug' => 'account-deletion-confirmation',
                'subject' => 'Account Deleted - {{app_name}}',
                'body' => '<div class="content">
                    <h2 style="color: #dc3545;">Account Successfully Deleted</h2>
                    <p>Hello {{name}},</p>
                    <p>Your {{app_name}} account has been permanently deleted as requested.</p>
                    <p><strong>What has been deleted:</strong></p>
                    <ul>
                        <li>Your profile information and settings</li>
                        <li>All account data and preferences</li>
                        <li>Access to {{app_name}} services</li>
                    </ul>
                    <p>We\'re sorry to see you go. If you change your mind in the future, you\'re always welcome to create a new account.</p>
                    <p>Thank you for using {{app_name}}.</p>
                    <p>Best regards,<br>The {{app_name}} Team</p>
                </div>',
                'type' => 'account_management',
                'variables' => ['name', 'app_name'],
                'is_active' => true,
                'is_deletable' => false,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Newsletter Welcome',
                'slug' => 'newsletter-welcome',
                'subject' => 'Welcome to {{app_name}} Newsletter!',
                'body' => '<div class="content">
                    <h2 style="color: #245dac;">Welcome to Our Newsletter!</h2>
                    <p>Thank you for subscribing to the {{app_name}} newsletter!</p>
                    <p>You\'ll now receive:</p>
                    <ul>
                        <li>Updates on new legal document templates</li>
                        <li>Legal compliance tips and insights</li>
                        <li>Product updates and new features</li>
                        <li>Industry news and regulatory changes</li>
                    </ul>
                    <p>We respect your privacy and will never share your email address with third parties.</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{{app_url}}" class="button">Visit {{app_name}}</a>
                    </div>
                    <p>Best regards,<br>The {{app_name}} Team</p>
                    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; text-align: center;">
                        <p style="font-size: 12px; color: #6c757d;">
                            You received this email because you subscribed to our newsletter.<br>
                            <a href="{{unsubscribe_url}}" style="color: #6c757d;">Unsubscribe</a> |
                            <a href="{{app_url}}" style="color: #6c757d;">Visit Website</a>
                        </p>
                    </div>
                </div>',
                'type' => 'newsletter',
                'variables' => ['app_name', 'app_url', 'unsubscribe_url'],
                'is_active' => true,
                'is_deletable' => false,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Newsletter Unsubscribe Confirmation',
                'slug' => 'newsletter-unsubscribe',
                'subject' => 'You\'ve been unsubscribed - {{app_name}}',
                'body' => '<div class="content">
                    <h2 style="color: #6c757d;">Unsubscribed Successfully</h2>
                    <p>You have been successfully unsubscribed from the {{app_name}} newsletter.</p>
                    <p>We\'re sorry to see you go! Your email address has been removed from our mailing list and you will no longer receive newsletter emails from us.</p>
                    <p>If you change your mind, you can always subscribe again by visiting our website.</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{{app_url}}" class="button">Visit {{app_name}}</a>
                    </div>
                    <p>Thank you for your time with us.</p>
                    <p>Best regards,<br>The {{app_name}} Team</p>
                </div>',
                'type' => 'newsletter',
                'variables' => ['app_name', 'app_url'],
                'is_active' => true,
                'is_deletable' => false,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Contact Thank You',
                'slug' => 'contact-thank-you',
                'subject' => 'Thank you for contacting us - {{app_name}}',
                'body' => '<div class="content">
                    <h2 style="color: #245dac;">Thank you for contacting us!</h2>
                    <p>Hello {{name}},</p>
                    <p>Thank you for reaching out to us through our contact form. We have received your message and appreciate you taking the time to contact us.</p>
                    <div style="background-color: #f8f9fa; padding: 15px; border-left: 4px solid #245dac; margin: 20px 0;">
                        <p><strong>Your Message Details:</strong></p>
                        <p><strong>Subject:</strong> {{subject}}</p>
                        <p><strong>Message:</strong></p>
                        <p style="margin-left: 15px;">{{message}}</p>
                    </div>
                    <p>Our team will review your inquiry and get back to you as soon as possible. We typically respond within 24-48 hours during business days.</p>
                    <p>If your inquiry is urgent, please feel free to contact us directly at our support email or phone number listed on our website.</p>
                    <p>Thank you for your interest in {{app_name}}!</p>
                    <p>Best regards,<br>The {{app_name}} Support Team</p>
                </div>',
                'type' => 'contact',
                'variables' => ['name', 'subject', 'message', 'app_name'],
                'is_active' => true,
                'is_deletable' => false,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Contact Received (Admin Notification)',
                'slug' => 'contact-received-admin',
                'subject' => 'New Contact Message Received - {{subject}}',
                'body' => '<div class="content">
                    <h2 style="color: #0d6efd;">📧 New Contact Message</h2>
                    <p>Hello Admin,</p>
                    <p>You have received a new contact message from your website.</p>
                    
                    <div style="background-color: #f8f9fa; padding: 20px; border-left: 4px solid #0d6efd; margin: 20px 0; border-radius: 4px;">
                        <h3 style="margin-top: 0; color: #0d6efd;">Contact Information</h3>
                        <p><strong>Name:</strong> {{name}}</p>
                        <p><strong>Email:</strong> <a href="mailto:{{email}}">{{email}}</a></p>
                        <p><strong>Received:</strong> {{received_date}}</p>
                    </div>
                    
                    <div style="background-color: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 4px;">
                        <h3 style="margin-top: 0; color: #0d6efd;">Subject</h3>
                        <p style="font-size: 16px; font-weight: 500;">{{subject}}</p>
                    </div>
                    
                    <div style="background-color: white; padding: 20px; margin: 20px 0; border: 1px solid #dee2e6; border-radius: 4px;">
                        <h3 style="margin-top: 0; color: #0d6efd;">Message</h3>
                        <p style="white-space: pre-wrap;">{{message}}</p>
                    </div>
                    
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{{view_link}}" class="button" style="display: inline-block; padding: 12px 24px; background: #0d6efd; color: white; text-decoration: none; border-radius: 4px;">View & Reply to Message</a>
                    </div>
                    
                    <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; color: #6c757d; font-size: 14px; text-align: center;">
                        This is an automated notification from {{app_name}}<br>
                        You can manage all contact messages in your <a href="{{admin_link}}">admin panel</a>
                    </p>
                </div>',
                'type' => 'contact',
                'variables' => ['name', 'email', 'subject', 'message', 'received_date', 'view_link', 'admin_link', 'app_name'],
                'is_active' => true,
                'is_deletable' => false,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Contact Replied (User Notification)',
                'slug' => 'contact-replied-user',
                'subject' => 'Re: {{subject}}',
                'body' => '<div class="content">
                    <h2 style="color: #198754;">✉️ Reply to Your Message</h2>
                    <p>Hello {{name}},</p>
                    <p>Thank you for contacting us. We have reviewed your message and here is our response:</p>
                    
                    <div style="background-color: #d1e7dd; padding: 20px; border-left: 4px solid #198754; margin: 20px 0; border-radius: 4px;">
                        <h3 style="margin-top: 0; color: #198754;">Our Reply</h3>
                        <p style="white-space: pre-wrap; font-size: 15px;">{{reply_message}}</p>
                    </div>
                    
                    <div style="background-color: white; padding: 20px; margin: 20px 0; border: 1px solid #dee2e6; border-radius: 4px;">
                        <h3 style="margin-top: 0; color: #6c757d;">Your Original Message</h3>
                        <p><strong>Subject:</strong> {{subject}}</p>
                        <p><strong>Sent:</strong> {{sent_date}}</p>
                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e9ecef;">
                            <p style="white-space: pre-wrap; color: #6c757d;">{{message}}</p>
                        </div>
                    </div>
                    
                    <p style="margin-top: 20px;">If you have any further questions, please feel free to reply to this email or contact us again through our website.</p>
                    
                    <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; color: #6c757d; font-size: 14px; text-align: center;">
                        <strong>{{app_name}}</strong><br>
                        This email was sent in response to your contact form submission.<br>
                        You can reply directly to this email at: <a href="mailto:{{reply_to_email}}">{{reply_to_email}}</a>
                    </p>
                </div>',
                'type' => 'contact',
                'variables' => ['name', 'subject', 'message', 'reply_message', 'sent_date', 'reply_to_email', 'app_name'],
                'is_active' => true,
                'is_deletable' => false,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Test Email',
                'slug' => 'test-email',
                'subject' => '{{subject}}',
                'body' => '<div class="content">
                    <h2 style="color: #667eea;">{{subject}}</h2>
                    <p>{{message}}</p>
                    
                    <div style="background-color: #f8f9fa; border-left: 4px solid #667eea; padding: 15px 20px; margin: 20px 0; border-radius: 4px;">
                        <p style="margin: 0; color: #666; font-size: 14px;">
                            <strong>ℹ️ Note:</strong> This is a test email to verify that your email configuration is working correctly.
                        </p>
                    </div>
                    
                    <p style="color: #666;">
                        If you received this email, your email settings are configured properly and working as expected.
                    </p>
                    
                    <div style="text-align: center; margin: 10px 0;">
                        <a href="{{app_url}}" class="button">Visit {{app_name}}</a>
                    </div>
                </div>',
                'type' => 'test',
                'variables' => ['subject', 'message', 'app_name', 'app_url'],
                'is_active' => true,
                'is_deletable' => false,
                'created_by' => 1,
                'updated_by' => 1,
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['name' => $template['name']],
                $template
            );
        }
    }
}
