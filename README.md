# The Onyx & Ivory — website đặt phòng một khách sạn

PHP thuần + HTML + CSS + JavaScript + MySQL/MariaDB. Chạy bằng VS Code và XAMPP trên Windows. Không cần Laravel, Composer, npm hay lệnh `php artisan`.

## 1. Cài đặt trong XAMPP

1. Giải nén gói ZIP. Chép thư mục **onyx-ivory** vào `C:\xampp\htdocs\`.
   Kiểm tra file trang chủ ở đúng `C:\xampp\htdocs\onyx-ivory\index.php` (không bị lồng hai thư mục cùng tên).
2. Mở **XAMPP Control Panel**, bấm **Start** ở Apache và MySQL.
3. Mở `http://localhost/phpmyadmin` → tab **Import / Nhập** → chọn `onyx-ivory/database/schema.sql` → **Go / Thực hiện**. File tự tạo database `onyx_ivory`, bảng và 3 loại phòng mẫu; không cần tạo database thủ công.
4. VS Code → **File → Open Folder** → chọn `C:\xampp\htdocs\onyx-ivory`.
5. Mở `config/config.php`. Mặc định XAMPP: host `127.0.0.1`, port `3306`, user `root`, mật khẩu trống. Nếu MySQL của bạn có mật khẩu/cổng khác, sửa đúng giá trị.
6. Mở `http://localhost/onyx-ivory/install.php`. Tạo email và mật khẩu quản trị của riêng bạn (12–72 ký tự). Trang này chỉ chạy từ máy localhost và tự khóa tạo tài khoản khi đã có quản trị viên.
7. Trang khách hàng: `http://localhost/onyx-ivory/`.
8. Trang quản trị: `http://localhost/onyx-ivory/admin/`.

**Không mở bằng Live Server hoặc bấm đúp file PHP**. Phải mở URL localhost qua Apache để PHP được thực thi.

Yêu cầu: PHP 8.1 trở lên, các extension `pdo_mysql`, `mbstring`, `ctype`, `session`; MySQL 8 hoặc MariaDB 10.4 trở lên. XAMPP có PHP 8.x và MariaDB đáp ứng bộ mã này. SQL dùng InnoDB để bảo đảm giao dịch và khóa dữ liệu.

## 2. Chức năng đã có

### Khách hàng
- Trang chủ responsive theo nhận diện đen Onyx / trắng Ivory / vàng champagne.
- Giới thiệu đúng một khách sạn; phòng & suite; tiện nghi nhà hàng và spa.
- Xem chi tiết phòng, diện tích, giường, sức chứa, giá và tiện nghi.
- Chọn ngày nhận/trả, số phòng, tổng số khách; kiểm tra tồn mỗi đêm.
- Nhập thông tin đặt phòng, xem tổng tiền, gửi yêu cầu vào MySQL.
- Màn hình xác nhận, lưu/in đơn thành PDF bằng chức năng Print của trình duyệt.
- Tra cứu bằng mã đơn ngẫu nhiên 32 ký tự + email; hủy đơn trước ngày nhận phòng.
- Chính sách lưu trú và giải thích cách xử lý dữ liệu.

### Quản trị
- Tạo tài khoản lần đầu, đăng nhập, đăng xuất, đổi mật khẩu.
- Thống kê tổng đơn, đơn chờ, đơn đang ở và giá trị đơn đã xác nhận.
- Tìm đơn theo mã/tên/email, lọc trạng thái, phân trang 20 đơn/trang.
- Xem thông tin liên hệ, ghi chú; xác nhận, hủy, nhận phòng, trả phòng.
- Sửa tên, giới thiệu, mô tả, đơn giá, tổng số phòng và đóng/mở bán cho 3 loại phòng mẫu.
- Không cho giảm tổng số phòng xuống dưới số đã được giữ trong bất kỳ đêm tương lai nào.

## 3. Quy tắc nghiệp vụ

- Một đơn đặt một loại phòng, từ 1–5 phòng, 1–20 khách; lưu trú 1–30 đêm. Muốn nhiều loại phòng, tạo nhiều đơn.
- Ngày nhận từ hôm nay đến 1 năm tới; trả sau ngày nhận. Tính theo múi giờ Việt Nam.
- Trẻ em tính vào tổng số khách. Chưa hỗ trợ giường phụ hay phụ thu theo tuổi.
- Tổng tiền = giá/phòng/đêm × số phòng × số đêm. Giá mẫu đã gồm thuế và bữa sáng. Spa/phát sinh tính riêng.
- Tồn phòng = tổng số phòng trừ số phòng bị chiếm nhiều nhất trong từng đêm được chọn. Khoảng ngày là `[nhận phòng, trả phòng)`; trả ngày nào thì khách khác được đặt từ ngày đó.
- Đơn `pending`, `confirmed`, `checked_in` giữ tồn. Đơn chờ không tự hết hạn; quản trị viên phải xử lý/hủy các yêu cầu không hợp lệ.
- `pending → confirmed / cancelled`; `confirmed → checked_in / cancelled`; `checked_in → completed`.
- Chỉ nhận phòng trong thời gian lưu trú; chỉ trả phòng từ ngày trả phòng; chưa có nghiệp vụ nhận sớm/trả sớm/gia hạn.
- Đã hủy/đã hoàn tất không khôi phục lại. Đơn mới phải kiểm tra tồn lại.
- Khách hủy trực tuyến đơn chờ/đã xác nhận trước ngày nhận phòng. Từ ngày nhận phòng liên hệ lễ tân.
- Giá được chốt lúc gửi đơn, lưu riêng trên đơn; sửa giá loại phòng không đổi giá đơn cũ.
- Tiền thanh toán tại khách sạn. Hệ thống chưa theo dõi giao dịch thu tiền, đặt cọc hay hoàn tiền.

## 4. Cấu trúc để học và sửa

```text
onyx-ivory/
  index.php                 Trang chủ
  rooms.php                 Tìm phòng trống
  room.php                  Chi tiết loại phòng
  booking.php               Kiểm tra + tạo đơn
  confirmation.php          Kết quả đặt phòng
  lookup.php                Tra cứu + hủy đơn
  policies.php              Chính sách
  install.php               Tạo quản trị viên lần đầu
  admin/login.php           Đăng nhập
  admin/index.php           Quản trị đơn, loại phòng, tài khoản
  config/config.php         Kết nối MySQL, thông tin khách sạn
  includes/bootstrap.php    PDO, session, CSRF, tính tồn, kiểm tra dữ liệu
  includes/header.php       Thanh điều hướng
  includes/footer.php       Chân trang
  includes/search.php       Form tìm phòng
  includes/card.php         Thẻ phòng
  assets/style.css          Giao diện và responsive
  assets/app.js             Menu mobile, ngày, chống bấm gửi lặp
  assets/images/            Ảnh có sẵn, không phụ thuộc mạng lúc chạy
  database/schema.sql       Bảng + dữ liệu phòng mẫu
  tests/                    Kịch bản kiểm thử
```

Database gồm `admins`, `room_types`, `bookings`, `login_attempts`. Quan hệ chính: `room_types.id` → `bookings.room_type_id` (1:N). Không có bảng hotels vì chỉ phục vụ duy nhất một khách sạn.

Màu sắc ở đầu `assets/style.css`. Thay ảnh bằng file cùng tên trong `assets/images`. Sửa thông tin khu vực/email tại `config/config.php`; nội dung thương hiệu trong header/footer/index. Quản trị viên sửa giá/nội dung phòng tại trang Quản lý phòng. Sức chứa, diện tích, giường và ảnh của 3 loại phòng được định nghĩa trong SQL; muốn sửa dữ liệu đã nhập, dùng phpMyAdmin (không giảm sức chứa khi có đơn tương lai chưa xử lý).

## 5. Cách chạy thử nhanh

1. Mở website, chọn ngày ngày mai đến ngày kia, 1 phòng / 2 khách.
2. Chọn Ivory Deluxe, nhập tên/email/số điện thoại hợp lệ, đồng ý chính sách rồi gửi.
3. Lưu mã đơn. Đơn phải có trạng thái Chờ xác nhận và tổng tiền 1.800.000 ₫ nếu chưa đổi giá.
4. Mở quản trị, xác nhận đơn; tra cứu bên khách phải thấy Đã xác nhận.
5. Hủy đơn ở trang tra cứu trước ngày nhận. Trạng thái phải đổi Đã hủy và số phòng trống tăng lại.
6. Xem các tình huống mở rộng tại `tests/TEST_CASES.md`.

## 6. Xử lý lỗi thường gặp

- **404 Not Found**: kiểm tra thư mục đúng `htdocs/onyx-ivory`, Apache đang chạy; không lồng thêm thư mục ZIP.
- **Chưa thể kết nối dữ liệu**: bật MySQL, nhập SQL, kiểm tra tên database/mật khẩu/cổng tại config.
- **Access denied for user root**: mật khẩu MySQL khác mặc định, sửa `db_pass`.
- **could not find driver**: bật `extension=pdo_mysql` trong `C:\xampp\php\php.ini`, khởi động lại Apache.
- **undefined function mb_strlen**: bật `extension=mbstring` trong php.ini rồi khởi động lại Apache.
- **Apache dùng cổng 8080**: dùng `http://localhost:8080/onyx-ivory/`; các liên kết nội bộ tự nhận thư mục hiện tại.
- **Đã có tài khoản quản trị**: dùng tài khoản đã tạo. Không có tài khoản/mật khẩu mặc định. Có thể đổi mật khẩu sau khi đăng nhập.
- **Form báo phiên không hợp lệ**: bật cookie và tải lại form; tránh gửi lại tab cũ sau khi đăng xuất.
- **Ảnh không hiện**: kiểm tra đã giải nén cả thư mục `assets/images`.
- Nhật ký lỗi: XAMPP → Apache → Logs → error.log.

## 7. Phạm vi và bảo vệ dữ liệu

Đây là bộ mã đồ án/local hoàn chỉnh cho luồng đặt phòng đã mô tả. Không tích hợp tour, nhiều khách sạn, đăng ký tài khoản khách, email SMTP, cổng thanh toán, tải ảnh quản trị hoặc sửa lịch đơn đã đặt. Địa chỉ chính xác, hotline, giá, tiện nghi và thông tin pháp nhân cần cung cấp lại trước khi dùng kinh doanh.

Đã có PDO prepared statements; escaping HTML; kiểm tra phía PHP; CSRF cho POST; hash mật khẩu; cookie HttpOnly/SameSite; đổi session ID khi đăng nhập; giới hạn đăng nhập sai theo IP; mã đơn ngẫu nhiên; khóa giao dịch InnoDB khi đặt/hủy/chỉnh tồn; khóa request để gửi lại cùng form không tạo đơn thứ hai. File cấu hình/SQL được chặn truy cập trực tiếp bằng Apache `.htaccess`.

Nếu đưa lên hosting thật: cấu hình HTTPS, tài khoản database riêng thay root, sao lưu, chính sách xử lý dữ liệu thực tế, giám sát và giới hạn tạo đơn chống spam; xóa `install.php` sau cài đặt. Chưa có cơ chế tự hết hạn giữ chỗ hoặc chống spam đặt phòng nâng cao.

## 8. Tham khảo và hình ảnh

Tham khảo cấu trúc trang giới thiệu khách sạn, phòng & suite, tiện nghi và nút đặt phòng từ https://lasinfoniavietnam.com/delrey/ . Không sao chép mã nguồn, logo, tên phòng, nội dung hay thương hiệu La Sinfonía. Thiết kế The Onyx & Ivory được viết riêng theo yêu cầu.

Ảnh minh họa tải từ Unsplash, lưu cục bộ; không phải ảnh của khách sạn có thật mang tên The Onyx & Ivory:
- Hero / Deluxe: https://images.unsplash.com/photo-1611892440504-42a792e24d32
- Suite: https://images.unsplash.com/photo-1566665797739-1674de7a421a
- Family: https://images.unsplash.com/photo-1590490360182-c33d57733427
- Nhà hàng: https://images.unsplash.com/photo-1517248135467-4c7edcad34c4
- Spa: https://images.unsplash.com/photo-1540555700478-4be289fbecef
- Thông tin giấy phép ảnh: https://unsplash.com/license
