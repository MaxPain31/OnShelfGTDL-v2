<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f6e5ef;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 40px 20px; text-align: center;">
                <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 30px 20px; text-align: center; background: linear-gradient(135deg, #e07aac 0%, #a03464 100%); border-radius: 10px 10px 0 0;">
                            <img src="{{ config('app.url') }}/img/logo.png" alt="OnShelf GTDL" style="width: 80px; height: 80px; margin-bottom: 10px; display: block; margin-left: auto; margin-right: auto;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: bold;">OnShelf GTDL</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="margin: 0 0 20px 0; color: #4b2036; font-size: 22px;">Reset Your Password</h2>
                            
                            <p style="margin: 0 0 20px 0; color: #7c4c63; font-size: 16px; line-height: 1.6;">
                                Hello {{ $userName }},
                            </p>
                            
                            <p style="margin: 0 0 20px 0; color: #7c4c63; font-size: 16px; line-height: 1.6;">
                                We received a request to reset your password for your OnShelf GTDL account. Click the button below to reset your password:
                            </p>
                            
                            <table role="presentation" style="width: 100%; margin: 30px 0;">
                                <tr>
                                    <td style="text-align: center;">
                                        <a href="{{ $resetUrl }}" style="display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #e07aac 0%, #a03464 100%); color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;">
                                            Reset Password
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin: 20px 0 0 0; color: #7c4c63; font-size: 14px; line-height: 1.6;">
                                Or copy and paste this link into your browser:
                            </p>
                            <p style="margin: 10px 0 20px 0; color: #a03464; font-size: 14px; word-break: break-all;">
                                {{ $resetUrl }}
                            </p>
                            
                            <p style="margin: 20px 0 0 0; color: #7c4c63; font-size: 14px; line-height: 1.6;">
                                This password reset link will expire in 60 minutes.
                            </p>
                            
                            <p style="margin: 20px 0 0 0; color: #7c4c63; font-size: 14px; line-height: 1.6;">
                                If you did not request a password reset, please ignore this email or contact support if you have concerns.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 20px 30px; text-align: center; background-color: #fff7fb; border-radius: 0 0 10px 10px; border-top: 1px solid #f3cbe0;">
                            <p style="margin: 0; color: #7c4c63; font-size: 12px;">
                                © {{ date('Y') }} OnShelf GTDL. All rights reserved.
                            </p>
                            <p style="margin: 10px 0 0 0; color: #7c4c63; font-size: 12px;">
                                Gen. Tiburcio de Leon NHS Library Youth Club
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

