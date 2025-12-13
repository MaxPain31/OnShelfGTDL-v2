<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Due Date Approaching</title>
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
                            <h2 style="margin: 0 0 20px 0; color: #4b2036; font-size: 22px;">Book Due Date Reminder</h2>
                            
                            <p style="margin: 0 0 20px 0; color: #7c4c63; font-size: 16px; line-height: 1.6;">
                                Hello {{ $userName }},
                            </p>
                            
                            <p style="margin: 0 0 20px 0; color: #7c4c63; font-size: 16px; line-height: 1.6;">
                                This is a friendly reminder that your borrowed book is due soon:
                            </p>

                            @if($bookImage)
                            <div style="text-align: center; margin: 20px 0;">
                                <img src="{{ $bookImage }}" alt="{{ $bookName }}" style="max-width: 200px; height: auto; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            </div>
                            @endif

                            <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 20px; margin: 20px 0; border-radius: 8px;">
                                <h3 style="margin: 0 0 10px 0; color: #4b2036; font-size: 18px;">{{ $bookName }}</h3>
                                <p style="margin: 5px 0; color: #7c4c63; font-size: 14px;"><strong>Due Date:</strong> <span style="color: #ef4444; font-weight: bold; font-size: 16px;">{{ $dueDate }}</span></p>
                            </div>
                            
                            <div style="background-color: #fef3c7; border: 1px solid #f9c74f; padding: 15px; margin: 20px 0; border-radius: 8px;">
                                <p style="margin: 0; color: #92400e; font-size: 14px; line-height: 1.6;">
                                    <strong>Please remember to return the book by {{ $dueDate }}</strong> to avoid any late fees or penalties. Thank you for your cooperation!
                                </p>
                            </div>
                            
                            <p style="margin: 20px 0 0 0; color: #7c4c63; font-size: 14px; line-height: 1.6;">
                                If you have already returned the book, please disregard this message.
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

