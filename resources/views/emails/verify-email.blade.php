<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #1a1f2e;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #1a1f2e;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #242938; border-radius: 12px; overflow: hidden;">
                    <!-- Header with Logo -->
                    <tr>
                        <td align="center" style="padding: 32px 40px; background: linear-gradient(135deg, #2d3548 0%, #242938 100%); border-bottom: 1px solid #3d4556;">
                            <div style="background-color: #ffffff; border-radius: 12px; padding: 16px 24px; display: inline-block;">
                                <img src="{{ config('app.url') }}/images/logo.png" alt="OhioChatter" style="max-width: 180px; height: auto; display: block;">
                            </div>
                        </td>
                    </tr>

                    <!-- Main Content -->
                    <tr>
                        <td style="padding: 40px;">
                            <h1 style="margin: 0 0 24px 0; font-size: 24px; font-weight: 600; color: #ffffff;">
                                Welcome, {{ $user->username }}!
                            </h1>

                            <p style="margin: 0 0 24px 0; font-size: 16px; line-height: 1.6; color: #a0aec0;">
                                Thanks for joining OhioChatter! Please verify your email address to start participating in discussions.
                            </p>

                            <!-- Button -->
                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin: 32px 0;">
                                <tr>
                                    <td align="center" style="border-radius: 8px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                                        <a href="{{ $verificationUrl }}" target="_blank" style="display: inline-block; padding: 14px 32px; font-size: 16px; font-weight: 600; color: #ffffff; text-decoration: none;">
                                            Verify Email Address
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 24px 0 0 0; font-size: 14px; line-height: 1.6; color: #718096;">
                                If you did not create an account, no further action is required.
                            </p>

                            <p style="margin: 24px 0 0 0; font-size: 14px; line-height: 1.6; color: #718096;">
                                If you're having trouble clicking the button, copy and paste this URL into your browser:
                            </p>
                            <p style="margin: 8px 0 0 0; font-size: 12px; line-height: 1.6; color: #f59e0b; word-break: break-all;">
                                {{ $verificationUrl }}
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 40px; background-color: #1a1f2e; border-top: 1px solid #3d4556;">
                            <p style="margin: 0; font-size: 12px; color: #718096; text-align: center;">
                                &copy; {{ date('Y') }} OhioChatter. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
