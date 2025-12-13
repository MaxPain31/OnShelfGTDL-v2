<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Account Created</title>
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
                            <h2 style="margin: 0 0 20px 0; color: #4b2036; font-size: 22px;">Welcome to OnShelf GTDL!</h2>
                            
                            <p style="margin: 0 0 20px 0; color: #7c4c63; font-size: 16px; line-height: 1.6;">
                                Hello {{ $teacherName }},
                            </p>
                            
                            <p style="margin: 0 0 20px 0; color: #7c4c63; font-size: 16px; line-height: 1.6;">
                                Your teacher account has been successfully created in the OnShelf GTDL Library Management System. You can now access the library system and manage students!
                            </p>

                            <div style="background-color: #fff7fb; border-left: 4px solid #a03464; padding: 20px; margin: 20px 0; border-radius: 8px;">
                                <h3 style="margin: 0 0 15px 0; color: #4b2036; font-size: 18px;">Your Login Credentials</h3>
                                <table role="presentation" style="width: 100%; border-collapse: collapse;">
                                    <tr>
                                        <td style="padding: 8px 0; color: #7c4c63; font-size: 14px;"><strong>Email:</strong></td>
                                        <td style="padding: 8px 0; color: #4b2036; font-size: 14px; font-weight: bold;">{{ $email }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #7c4c63; font-size: 14px;"><strong>Password:</strong></td>
                                        <td style="padding: 8px 0; color: #4b2036; font-size: 14px; font-weight: bold; font-family: monospace; background-color: #f3cbe0; padding: 4px 8px; border-radius: 4px;">{{ $password }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #7c4c63; font-size: 14px;"><strong>Employee Number:</strong></td>
                                        <td style="padding: 8px 0; color: #4b2036; font-size: 14px; font-weight: bold;">{{ $employeeNumber }}</td>
                                    </tr>
                                </table>
                            </div>

                            <div style="background-color: #fef3c7; border: 1px solid #f9c74f; padding: 15px; margin: 20px 0; border-radius: 8px;">
                                <p style="margin: 0; color: #92400e; font-size: 14px; line-height: 1.6;">
                                    <strong>Important:</strong> Please keep your login credentials secure. We recommend changing your password after your first login for security purposes.
                                </p>
                            </div>

                            <div style="text-align: center; margin: 30px 0;">
                                <a href="{{ config('app.url') }}/login" style="display: inline-block; background: linear-gradient(135deg, #e07aac 0%, #a03464 100%); color: #ffffff; text-decoration: none; padding: 12px 30px; border-radius: 8px; font-weight: bold; font-size: 16px;">Login to Your Account</a>
                            </div>
                            
                            <p style="margin: 20px 0 0 0; color: #7c4c63; font-size: 14px; line-height: 1.6;">
                                As a teacher, you can manage students, borrow books, and access various library resources. If you have any questions or need assistance, please contact the library administrator.
                            </p>
                            
                            <p style="margin: 20px 0 0 0; color: #7c4c63; font-size: 14px; line-height: 1.6;">
                                Welcome aboard!
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

