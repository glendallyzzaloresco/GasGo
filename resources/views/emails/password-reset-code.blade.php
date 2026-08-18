<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Code</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f1f5f9; padding: 30px 15px;">
        <tr>
            <td align="center">
                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 580px; background-color: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
                    
                    <!-- Header Banner -->
                    <tr>
                        <td align="center" style="background: linear-gradient(135deg, #1a6db0 0%, #2196f3 70%, #f7941d 100%); padding: 36px 20px; text-align: center;">
                            <table border="0" cellspacing="0" cellpadding="0" align="center">
                                <tr>
                                    <td align="center">
                                        <div style="display: inline-block; background: rgba(255,255,255,0.18); border-radius: 12px; padding: 8px 16px; margin-bottom: 8px;">
                                            <span style="font-size: 24px; font-weight: 900; color: #ffffff; letter-spacing: 1px; font-family: 'Segoe UI', Roboto, sans-serif;">
                                                Gas<span style="color: #ffb74d;">Go</span>
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center">
                                        <p style="margin: 4px 0 0 0; color: rgba(255,255,255,0.92); font-size: 13px; font-weight: 500; letter-spacing: 0.5px;">
                                            LPG Delivery & Tracking Platform
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 36px 32px 28px 32px; background-color: #ffffff;">
                            
                            <h2 style="margin: 0 0 12px 0; font-size: 20px; font-weight: 700; color: #1e293b;">
                                Hello, {{ $user->name ?? 'Valued Customer' }}!
                            </h2>

                            <p style="margin: 0 0 24px 0; font-size: 15px; line-height: 1.6; color: #475569;">
                                We received a request to reset your password for your <strong>GasGo</strong> account. Use the 6-digit verification code below to continue:
                            </p>

                            <!-- Code Box -->
                            <div style="background-color: #f8fafc; border: 2px dashed #1a6db0; border-radius: 14px; padding: 24px 16px; text-align: center; margin: 24px 0;">
                                <span style="display: block; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 8px;">
                                    Your Verification Code
                                </span>
                                
                                <div style="font-family: 'Courier New', Courier, monospace; font-size: 38px; font-weight: 900; color: #1a6db0; letter-spacing: 8px; margin: 6px 0; line-height: 1.2;">
                                    {{ $code }}
                                </div>
                                
                                <div style="margin-top: 10px;">
                                    <span style="display: inline-block; background-color: #fee2e2; color: #b91c1c; font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 20px;">
                                        ⏱️ Expires in 5 minutes
                                    </span>
                                </div>
                            </div>

                            <p style="margin: 0 0 20px 0; font-size: 14px; line-height: 1.5; color: #64748b;">
                                Enter this code on the password reset page to create a new password for your account.
                            </p>

                            <!-- Security Advisory -->
                            <div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; border-radius: 8px; padding: 14px 16px; margin: 24px 0 10px 0;">
                                <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #92400e;">
                                    <strong>Security Notice:</strong> Never share this code with anyone. GasGo representatives will never ask for your verification code or password.
                                </p>
                            </div>

                            <p style="margin: 16px 0 0 0; font-size: 13px; color: #94a3b8;">
                                If you did not request this password reset, please ignore this email or contact support if you have concerns.
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="background-color: #f8fafc; padding: 22px 20px; border-top: 1px solid #f1f5f9;">
                            <p style="margin: 0 0 6px 0; font-size: 13px; font-weight: 700; color: #1a6db0;">
                                GasGo Delivery System
                            </p>
                            <p style="margin: 0; font-size: 11px; color: #94a3b8;">
                                &copy; {{ date('Y') }} GasGo. All rights reserved. &bull; Automated notification, please do not reply.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
