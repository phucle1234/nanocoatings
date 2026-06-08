<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html dir="ltr" xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="icon" type="image/png" sizes="16x16" href="favicon.ico">
    <title>Email - Casumina</title>
</head>

<body style="margin:0px; background-color: #f5f5f5; padding: 20px 0;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">

        <!-- Header với logo -->
        <table border="0" cellpadding="0" cellspacing="0" style="width: 100%; border-bottom: 1px solid #d32f2f;">
            <tbody>
                <tr>
                    <td style="padding: 30px; text-align: center;">
                        <a href="{{ route('login') }}" style="display: inline-block;">
                            <img src="{{ asset('langding/imgs/logo3.svg') }}" alt="Casumina" style="height: 60px; display: block;">
                        </a>
                        <h2 style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 15px 0 0 0; font-size: 20px; font-weight: 600;">
                            Casumina - Bạn đường tin cậy
                        </h2>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Nội dung chính -->
        <div style="padding: 40px 30px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333333;">

            <!-- Lời chào -->
            <h3 style="color: #d32f2f; font-size: 18px; margin: 0 0 20px 0; font-weight: 600;">
                Xin chào {{ $user->email }},
            </h3>

            <!-- Thông điệp chính -->
            <p style="margin: 0 0 20px 0; font-size: 15px; color: #555555;">
                Yêu cầu đặt lại mật khẩu của bạn đã được xử lý thành công. Dưới đây là thông tin mật khẩu mới của bạn:
            </p>

            <!-- Thông tin tài khoản -->
            <div style="background: #f8f9fa; border-left: 4px solid #d32f2f; padding: 20px; margin: 25px 0; border-radius: 4px;">
                <h4 style="margin: 0 0 15px 0; color: #333333; font-size: 16px; font-weight: 600;">
                    Thông tin tài khoản:
                </h4>
                <p style="margin: 0 0 10px 0; font-size: 15px; color: #555555;">
                    <strong style="color: #333333;">Tên đăng nhập:</strong> {{ $user->user_name }}
                </p>
                <p style="margin: 0 0 10px 0; font-size: 15px; color: #555555;">
                    <strong style="color: #333333;">Mật khẩu mới:</strong> <span style="background: #ffffff; padding: 5px 10px; border-radius: 4px; border: 1px solid #dee2e6; font-family: monospace; color: #d32f2f; font-weight: 600;">{{ $newPassword }}</span>
                </p>
                <p style="margin: 0; font-size: 15px; color: #555555;">
                    <strong style="color: #333333;">Ngày tạo:</strong> {{ now()->format('d/m/Y H:i:s') }}
                </p>
            </div>

            <!-- Lưu ý bảo mật -->
            <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 4px; margin: 25px 0;">
                <p style="margin: 0; font-size: 14px; color: #856404;">
                    <strong>🔒 Lưu ý bảo mật:</strong> Vì lý do an toàn, vui lòng thay đổi mật khẩu ngay sau khi đăng nhập lần đầu.
                </p>
            </div>

            <!-- Nút đăng nhập -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('login') }}" style="display: inline-block; background: linear-gradient(135deg, #d32f2f 0%, #b71c1c 100%); color: #ffffff; padding: 14px 40px; text-decoration: none; border-radius: 50px; font-size: 16px; font-weight: 600; box-shadow: 0 4px 15px rgba(211, 47, 47, 0.3); transition: all 0.3s ease;">
                    Đăng nhập ngay
                </a>
            </div>

            <!-- Lưu ý -->
            <div style="background: #e8f4fd; border: 1px solid #0288d1; padding: 15px; border-radius: 4px; margin: 25px 0;">
                <p style="margin: 0; font-size: 14px; color: #014361;">
                    <strong>ℹ️ Lưu ý:</strong> Nếu bạn không thực hiện yêu cầu này, vui lòng liên hệ với chúng tôi ngay lập tức để bảo vệ tài khoản của bạn.
                </p>
            </div>

            <!-- Lời kết -->
            <p style="margin: 25px 0 0 0; font-size: 15px; color: #555555;">
                <strong>Trân trọng,</strong><br>
                <span style="color: #d32f2f; font-weight: 600;">Đội ngũ Casumina</span>
            </p>
        </div>

        <!-- Footer -->
        <table border="0" cellpadding="0" cellspacing="0" style="width: 100%; background: #2c2c2c;">
            <tbody>
                <tr>
                    <td style="padding: 25px 30px; text-align: center;">
                        <p style="margin: 0 0 10px 0; font-size: 14px; color: #ffffff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                            <strong>CÔNG TY CỔ PHẦN CÔNG NGHIỆP CAO SU MIỀN NAM - CASUMINA</strong>
                        </p>
                        <p style="margin: 0 0 15px 0; font-size: 13px; color: #cccccc; line-height: 1.6;">
                            Trụ sở chính: 180 Nguyễn Thị Minh Khai, Phường Võ Thị Sáu, Quận 3, TP.HCM<br>
                            Hotline: (084) 2838 362 369 - (084) 2838 362 373
                        </p>
                        <div style="border-top: 1px solid #444444; padding-top: 15px; margin-top: 15px;">
                            <p style="margin: 0 0 5px 0; font-size: 13px; color: #cccccc;">
                                Bạn cần hỗ trợ? Liên hệ với chúng tôi:
                            </p>
                            <a href="mailto:support@casumina.com" style="color: #d32f2f; text-decoration: none; font-size: 14px; font-weight: 600;">
                                support@casumina.com
                            </a>
                        </div>
                        <p style="margin: 15px 0 0 0; font-size: 12px; color: #999999;">
                            © 2024 Casumina. All rights reserved.
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

    </div>
</body>

</html>