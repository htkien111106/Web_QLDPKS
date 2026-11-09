<?php require 'includes/bootstrap.php'; $title='Đặt phòng'; $error='';$room=null;$nights=0;
$in=input('check_in',date('Y-m-d')); $out=input('check_out',date('Y-m-d',strtotime('+1 day'))); $quantity=1;$guests=2;
try {
 $nights=valid_dates($in,$out);$quantity=integer('quantity',1,5);$guests=integer('guests',1,20,2);
 $room=query('SELECT * FROM room_types WHERE id=? AND active=1',[input('id')])->fetch();
 if(!$room) throw new DomainException('Phòng không tồn tại hoặc đang ngừng nhận đặt.');
 if($guests>$room['capacity']*$quantity) throw new DomainException('Số khách vượt quá sức chứa của phòng đã chọn.');
 if(post()) {
  check_csrf();
  $name=input('guest_name');$email=input('email');$phone=input('phone');$note=input('note');$key=input('request_key');
  if(mb_strlen($name)<2 || mb_strlen($name)>100) throw new DomainException('Họ tên cần từ 2 đến 100 ký tự.');
  if(!filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>190) throw new DomainException('Email không hợp lệ.');
  if(!preg_match('/^\+?[0-9][0-9 ()-]{7,23}$/',$phone) || strlen(preg_replace('/\D/','',$phone))<9 || strlen(preg_replace('/\D/','',$phone))>15) throw new DomainException('Số điện thoại cần có từ 9 đến 15 chữ số.');
  if(mb_strlen($note)>1000) throw new DomainException('Ghi chú tối đa 1.000 ký tự.');
  if(input('agree')!=='1') throw new DomainException('Vui lòng đồng ý chính sách đặt phòng.');
  if(!preg_match('/^[a-f0-9]{32}$/',$key) || empty($_SESSION['booking_keys'][$key])) throw new DomainException('Phiên đặt phòng hết hạn. Vui lòng mở lại trang đặt phòng.');
  // Tất cả thao tác thay đổi tồn đều khóa dòng loại phòng trước.
  db()->beginTransaction();
  $room=query('SELECT * FROM room_types WHERE id=? FOR UPDATE',[$room['id']])->fetch();
  $old=query('SELECT code FROM bookings WHERE request_key=?',[$key])->fetchColumn();
  if($old) {db()->commit();$_SESSION['booking_receipt']=$old;go('confirmation.php');}
  if(!$room['active'] || available($room,$in,$out)<$quantity) throw new DomainException('Phòng vừa hết hoặc không còn đủ. Vui lòng chọn ngày hoặc loại phòng khác.');
  if((int)input('quoted_price')!==(int)$room['price']) throw new DomainException('Giá phòng vừa thay đổi. Vui lòng kiểm tra tổng tiền mới và gửi lại.');
  $code=bin2hex(random_bytes(16));
  query('INSERT INTO bookings(code,request_key,room_type_id,guest_name,email,phone,check_in,check_out,quantity,guests,nightly_price,total_price,note) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)',[$code,$key,$room['id'],$name,$email,$phone,$in,$out,$quantity,$guests,$room['price'],$room['price']*$quantity*$nights,$note]);
  db()->commit(); $_SESSION['booking_receipt']=$code;go('confirmation.php');
 }
 if(available($room,$in,$out)<$quantity) throw new DomainException('Không còn đủ phòng trong khoảng ngày này. Vui lòng tìm ngày khác.');
} catch(DomainException $ex) {if(db()->inTransaction()) db()->rollBack();$error=$ex->getMessage();}
$key=input('request_key'); if(!preg_match('/^[a-f0-9]{32}$/',$key) || empty($_SESSION['booking_keys'][$key])) {$key=bin2hex(random_bytes(16));$_SESSION['booking_keys'][$key]=time(); if(count($_SESSION['booking_keys'])>20) array_shift($_SESSION['booking_keys']);}
require 'includes/header.php'; ?>
<section class="page-heading"><div class="eyebrow">01 CHỌN PHÒNG &nbsp; — &nbsp; <strong>02 THÔNG TIN</strong> &nbsp; — &nbsp; 03 HOÀN TẤT</div><h1>Kỳ nghỉ <em>của bạn.</em></h1></section><section class="section compact"><?php if($error): ?><div class="notice error" role="alert"><?=e($error)?> <a href="<?=e(url('rooms.php'))?>">Tìm phòng khác</a></div><?php endif; ?>
<?php if($room && $nights): ?><div class="detail-grid"><form method="post" class="panel" data-booking-form><?=csrf()?><input type="hidden" name="id" value="<?=$room['id']?>"><input type="hidden" name="check_in" value="<?=e($in)?>"><input type="hidden" name="check_out" value="<?=e($out)?>"><input type="hidden" name="quantity" value="<?=$quantity?>"><input type="hidden" name="guests" value="<?=$guests?>"><input type="hidden" name="quoted_price" value="<?=$room['price']?>"><input type="hidden" name="request_key" value="<?=e($key)?>"><h2>Thông tin khách hàng</h2><p>Điền thông tin người đại diện nhận phòng.</p><label>Họ và tên *<input name="guest_name" value="<?=e(input('guest_name'))?>" minlength="2" maxlength="100" autocomplete="name" required placeholder="Nguyễn Văn An"></label><div class="form-grid"><label>Email *<input name="email" type="email" value="<?=e(input('email'))?>" maxlength="190" autocomplete="email" required placeholder="ban@email.com"></label><label>Số điện thoại *<input name="phone" type="tel" value="<?=e(input('phone'))?>" maxlength="25" autocomplete="tel" required placeholder="09xx xxx xxx"></label></div><label>Yêu cầu đặc biệt<textarea name="note" rows="4" maxlength="1000" placeholder="Giờ đến dự kiến, loại giường mong muốn…"><?=e(input('note'))?></textarea></label><small>Yêu cầu đặc biệt tùy thuộc tình trạng thực tế của khách sạn.</small><div class="notice"><strong>Thanh toán tại khách sạn</strong><br>Chưa thu tiền trực tuyến. Đơn được ghi nhận ở trạng thái chờ xác nhận; bạn có thể tra cứu bằng mã đơn và email.</div><label class="checkbox"><input type="checkbox" name="agree" value="1" required <?=input('agree')==='1'?'checked':''?>><span>Tôi đồng ý với <a href="<?=e(url('policies.php'))?>" target="_blank" rel="noopener">chính sách lưu trú và xử lý dữ liệu</a>.</span></label><button class="button full" type="submit">GỬI YÊU CẦU ĐẶT PHÒNG ↗</button></form><aside class="panel booking-summary"><img src="<?=e(url('assets/images/'.$room['image']))?>" alt="<?=e($room['name'])?>"><div class="eyebrow">THE ONYX & IVORY</div><h2><?=e($room['name'])?></h2><dl><dt>Nhận phòng</dt><dd><?=e(date('d/m/Y',strtotime($in)))?> · 14:00</dd><dt>Trả phòng</dt><dd><?=e(date('d/m/Y',strtotime($out)))?> · 12:00</dd><dt>Thời gian</dt><dd><?=$nights?> đêm</dd><dt>Số lượng</dt><dd><?=$quantity?> phòng · <?=$guests?> khách</dd><dt>Đơn giá / phòng / đêm</dt><dd><?=money($room['price'])?></dd></dl><div class="total"><span>Tổng thanh toán</span><strong><?=money($room['price']*$quantity*$nights)?></strong></div><p>Đã gồm thuế và bữa sáng. Dịch vụ phát sinh tính riêng.</p></aside></div><?php endif; ?></section><?php require 'includes/footer.php'; ?>
