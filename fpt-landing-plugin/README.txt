FPT LANDING PAGE — 1.9.20
- Banner nằm trên tiêu đề. Cài đặt chung: banner đầu tiên và slide 2–6.
- Chuyển ảnh bằng nút, chấm chọn, bàn phím hoặc vuốt trên điện thoại.
- Một ảnh: ẩn nút chuyển. Không tự chạy. Xóa cache sau khi cập nhật.

FPT LANDING PAGE — 1.9.19
========================================
- Cài đặt chung > Form WPForms phía trên khu vực lắp đặt.
- Dán shortcode thật, ví dụ [wpforms id="123" title="false" description="false"].
- Đổi ID để thay form; để trống để ẩn. Cần kích hoạt WPForms.
- Tiêu đề và mô tả khối form chỉnh được tại đây.
- Các trường, bố cục cột, email thông báo và xác nhận chỉnh trong WPForms.
- Form này sử dụng xử lý của WPForms; dữ liệu không tự lưu vào Khách đăng ký của plugin.
- Đo chuyển đổi WPForms cần cấu hình riêng theo sự kiện gửi thành công.

FPT LANDING PAGE — 1.9.17
========================================
- Vào FPT Landing Page > Cài đặt chung > Khu vực Combo V.VIP.
- Chỉnh nhãn nhỏ, tiêu đề chính, mô tả; để trống để ẩn từng dòng.
- Bật/tắt ghi chú giá chung sau mô tả. Ghi chú giá vẫn được chỉnh ở ô
  Ghi chú giá/ưu đãi trong Cài đặt chung.
- Chọn màu nền đầu/cuối, viền, nhãn, tiêu đề, mô tả. Dùng cùng hai màu
  nền để có nền một màu. Màu áp dụng khung khu V.VIP, không phải từng card.
- Mã màu được kiểm tra định dạng; màu sai giữ giá trị cũ khi lưu.
- Sau khi lưu hãy xóa cache LiteSpeed/CDN nếu giao diện chưa cập nhật.

FPT LANDING PAGE — 1.9.16
========================================
- Tách Combo Wi-Fi – Truyền hình V.VIP thành khu gói nổi bật phía trên.
  Gói V.VIP luôn hiển thị, có thanh tiêu đề riêng với nền cam nhạt.
- Ba tab bên dưới: Internet cá nhân, Combo truyền hình FPT, Internet Game thủ.
  Khi cấu hình cũ chọn V.VIP làm mặc định, thanh dưới mở Internet cá nhân.
- Vẫn sửa gói V.VIP tại Quản lý gói cước. Bộ chọn khu vực dùng chung;
  giá, Xem nhanh và đăng ký dùng dữ liệu gói cũ. Các URL quảng cáo cũ
  vẫn hoạt động; URL V.VIP hiển thị nhóm nổi bật phía trên.
- Nếu bật Ẩn danh mục trống, khu V.VIP tự ẩn khi không có gói.
- Sau cập nhật thay plugin, xóa cache LiteSpeed/CDN để nhận bố cục mới.

FPT LANDING PAGE — 1.9.15
========================================
- Bài viết Internet FPT > Các phần của bài viết: mỗi đoạn có nút Chọn ảnh
  từ thư viện WordPress, ảnh xem trước, mô tả alt và chú thích dưới ảnh.
- Chọn Sau nội dung để ảnh nằm giữa đoạn hiện tại và tiêu đề kế tiếp;
  chọn Trước nội dung để ảnh nằm ngay dưới tiêu đề đoạn hiện tại.
- Lên/Xuống di chuyển cả đoạn và ảnh. Gỡ ảnh chỉ bỏ ảnh khỏi đoạn,
  không xóa tệp trong thư viện. Nội dung cũ không bị tự thay thế.
- Ảnh hiển thị đủ tỷ lệ, bo góc, co theo điện thoại. Nội dung bài vẫn thu gọn.
- Đây là bổ sung ảnh cho từng đoạn; không tự sao chép banner từ ảnh chụp
  và không tạo bảng nhóm dịch vụ trong ảnh mẫu. Hãy chọn banner gốc từ
  thư viện; bảng giá tổng hợp hiện có vẫn chỉnh tại menu riêng.
- Giữ sửa lỗi URL gửi form của v1.9.14 và đường dẫn gọn của v1.9.13.

FPT LANDING PAGE — 1.9.14
========================================
- Sửa lỗi gửi form: trường ẩn WordPress tên action có thể trùng thuộc tính
  form.action của trình duyệt và làm yêu cầu gửi tới URL [object HTMLInputElement]
  thay vì admin-post.php. JavaScript nay lấy URL từ thuộc tính action của form.
- Triệu chứng đã đối chiếu từ phản hồi người dùng: trang trả về là trang 404
  WordPress; vì yêu cầu không tới trình xử lý form nên không lưu khách và không
  gửi email. Sửa đúng lỗi URL này để form dùng lại endpoint đăng ký hiện tại.
- Cài bản cập nhật và xóa cache LiteSpeed/CDN. Kiểm tra Network phải thấy
  POST tới /wp-admin/admin-post.php (hoặc đường dẫn admin tương ứng) và nhận
  JSON status. Đăng ký thành công sẽ lưu khách và thử gửi email như trước.
- Sau khi máy chủ xác nhận đã lưu, sự kiện GTM fptlp_lead_saved phát một lần.
  Email vẫn phụ thuộc wp_mail/SMTP. Kiểm tra riêng trong Khách đăng ký và
  trạng thái email của khách đó sau khi thử form.
- Các tính năng đường dẫn gọn v1.9.13 được giữ nguyên.

FPT LANDING PAGE — 1.9.13
========================================
- Thêm link gọn dùng chung trang WordPress có đường dẫn landing-fpt:
  /landing-fpt/internet/     — Internet cá nhân
  /landing-fpt/combo-tv/     — Combo truyền hình FPT
  /landing-fpt/combo-vvip/   — Combo truyền hình V.VIP
  /landing-fpt/gaming/       — Internet Game thủ
- Không phải tạo trang con hoặc nhập lại nội dung. Nhóm được chọn ngay
  khi PHP dựng trang, sau đó JavaScript giữ cùng lựa chọn. Đường dẫn gọn
  ưu tiên hơn tham số fptlp_group nếu có cả hai. Nhóm bị ẩn quay về nhóm
  mặc định đang hiển thị. Các link ?fptlp_group=... cũ vẫn dùng được.
- Không chuyển hướng về trang cha, không bỏ UTM/gclid. Giữ canonical SEO
  của trang cha theo WordPress; các URL này là biến thể của cùng trang.
- Trang con thật trùng đường dẫn được ưu tiên. Không thêm route cho nhóm
  bất kỳ hoặc ảnh hưởng đường dẫn ngoài landing-fpt. Không đổi trang chủ.
- Sau cài bản cập nhật: mở quản trị WordPress để hệ thống cập nhật quy tắc
  một lần; vào Cài đặt > Đường dẫn tĩnh > Lưu thay đổi nếu gặp 404, rồi xóa
  cache LiteSpeed/CDN. Website phải hỗ trợ đường dẫn đẹp như trang hiện có.
- Kiểm tra bốn URL trên website trước khi thay URL quảng cáo. Không cần
  đổi Google Ads ID, Label hoặc sự kiện GTM. AJAX và cách gửi POST dự phòng
  vẫn dùng form cũ; POST dự phòng quay lại đúng đường dẫn nhóm.
- Đã kiểm tra ánh xạ route bằng PHP với API WordPress mô phỏng, va chạm
  trang con, cập nhật/xóa quy tắc, nhóm từ PHP, tương thích link cũ và form
  cùng chuyển đổi trong Chromium. Chưa cài hoặc kiểm tra rewrite trên
  hosting WordPress thật của bạn.

FPT LANDING PAGE — 1.9.12
========================================
- Đầu trang gọn, nút báo giá ngay dưới giới thiệu; bỏ hai khung CTA lặp.
  Tiêu đề mẫu cũ hiển thị dạng ngắn; tiêu đề tự chỉnh được giữ nguyên.
  Không tự điền khu vực hay giá chưa xác nhận. Vào Cài đặt chung để sửa
  Khu vực phục vụ, Giá / điều kiện đầu trang và Nút báo giá đầu trang.
- Quản lý gói cước > Thông tin cơ bản: thêm Phù hợp với. Nếu chưa nhập,
  lấy câu Phù hợp đã có trong Quyền lợi; nếu không có, hiện lời mời tư vấn
  trung tính, không tự suy đoán số thiết bị, diện tích hoặc nhu cầu sử dụng.
- Bảng giá tổng hợp mặc định đồng bộ tên, giá, thiết bị từ gói cước; giá
  khu vực cập nhật cùng thẻ. Thiết bị lấy từ danh sách thiết bị hoặc các
  quyền lợi đã nhập có Modem/Router/Mesh/Access Point/Box. Khi thiếu, mời
  khách xem quyền lợi. Bảng nhập riêng cũ vẫn được lưu; bỏ chọn đồng bộ
  tại Bảng giá tổng hợp nếu cần sử dụng lại. Không phải nhập giá hai nơi.
- Bài SEO vẫn thu gọn, nằm dưới gói cước; bảng giá và FAQ giữ vị trí cũ.
- Form gửi bằng AJAX và hiện kết quả trong popup. Lỗi giữ nội dung nhập.
  Thành công chỉ nghĩa là WordPress đã lưu yêu cầu, không đảm bảo email
  đã tới hộp thư. Khi trình duyệt không có fetch, POST thường đưa về
  thông báo ở đầu landing page; chế độ dự phòng không đo chuyển đổi.
- Link quảng cáo (hoạt động cả với trang được cache):
  /landing-fpt/?fptlp_group=internet
  /landing-fpt/?fptlp_group=combo-tv
  /landing-fpt/?fptlp_group=combo-vvip
  /landing-fpt/?fptlp_group=gaming
  Dùng & thay ? khi URL đã có tham số. Nhóm không tồn tại quay về nhóm
  mặc định đã lưu. Các tham số không đổi giá hay dữ liệu gói cước.

THIẾT LẬP ĐO CHUYỂN ĐỔI (cần cấu hình tài khoản Google của bạn)
- AJAX chỉ trả mã chuyển đổi ngẫu nhiên khi lưu được yêu cầu mới.
  Không phát chuyển đổi cho lỗi, bot honeypot, trùng yêu cầu hay chỉ mở
  URL ?fptlp_status=success. Không gửi tên, điện thoại, địa chỉ vào dataLayer.
- Cách 1, GTM: Custom Event trigger fptlp_lead_saved; Google Ads Conversion
  Tracking tag dùng ID/Label thật. Tạo Data Layer Variable
  fptlp_conversion_id và gán vào Transaction ID. Kiểm tra trong Tag Assistant.
  Có thể tạo GA4 generate_lead từ cùng sự kiện; tránh nhập cả chuyển đổi
  GA4 và Ads trực tiếp làm hai mục tiêu chính cho cùng một yêu cầu.
- Cách 2, Google tag sẵn có: Cài đặt đăng ký > Google Ads send_to,
  nhập AW-123456789/nhan_thuc_te. Chỉ dùng khi website đã cài Google tag
  và gtag sẵn sàng. transaction_id dùng cùng mã ngẫu nhiên để chống trùng.
- Chỉ chọn một cách. Nếu dùng GTM, để trống send_to. Plugin không tự cài
  Google tag, không nhập ID giả, không thay đổi consent/cookie của website.
- Nếu phản hồi mạng bị mất sau khi máy chủ lưu, lần gửi lại được báo trùng;
  tình huống này có thể thiếu chuyển đổi. Không tự đếm chỉ dựa vào bấm gửi.
- Sau cập nhật: tải ZIP thay bản cũ, xóa cache LiteSpeed/CDN, xác nhận khu
  vực và điều kiện thật, điền Phù hợp với, nối sự kiện trong Google, thử
  một yêu cầu và kiểm tra Khách đăng ký cùng Tag Assistant trên website.
- Đã kiểm tra PHP với WordPress API mô phỏng, Chromium desktop/mobile,
  lưu quản trị, giá khu vực, link chiến dịch, lỗi mạng, lỗi nonce, bot,
  chống trùng và dữ liệu chuyển đổi. Chưa triển khai trên website thật
  hoặc kiểm chứng trong tài khoản Google Ads của bạn.

FPT LANDING PAGE — 1.9.11
========================================
- Tích hợp đồng hồ từ speed-meter-preview.html: kim và cung màu cùng
  quét tới tối đa rồi trở về mức đặt; số tốc độ tăng theo Mbps/Gbps.
- Chạy một lần khi tải trang. Giữ giá trị cuối khi tắt JavaScript hoặc
  bật giảm chuyển động; giữ nguyên nội dung không phải số như Liên hệ.
- Giữ mức đồng hồ đã lưu của gói cũ. Thêm mức 80% và Tự theo tốc độ:
  download/upload cùng trên 300 Mbps: 100%; download trên 300 và upload
  đúng 300 Mbps: 80%; còn lại: 30%.
- Chỉnh tại Quản lý gói cước > Ảnh và nhãn nổi bật > Mức đồng hồ tốc độ.
- Không đưa nút phát lại và thanh thời gian của file xem thử lên website.
- Kiểm tra PHP, lưu cài đặt, chuyển động, giảm chuyển động, không JavaScript
  và bố cục 320/390/850/1366 px. Chưa kiểm tra trên hosting thật.

FPT LANDING PAGE — 1.9.10
========================================
- Bản chính thức cải tiến quản trị dựa trên bản 1.9.9. Không bao gồm
  bản nháp popup dùng chung cho bài dịch vụ.
- Mỗi gói thu gọn được, luôn hiện tên và giá. Gói đầu mỗi danh mục
  mở sẵn; các gói còn lại đóng để dễ tìm và giảm độ dài màn hình.
- Năm nhóm: Thông tin cơ bản; Ảnh và nhãn nổi bật; Quyền lợi và thiết
  bị; Giá theo khu vực; Liên kết bài viết. Thông tin cơ bản mở sẵn.
- Hai cột trên máy tính, một cột trên điện thoại; nút Lưu bám cuối
  màn hình và báo khi có thay đổi chưa lưu.
- Thêm nút lên/xuống cho từng quyền lợi và thiết bị. Giữ chọn ảnh
  từ Media Library, thêm/xóa gói, thiết bị, giá khu vực và quyền lợi.
- Nhập sai tự mở danh mục, gói và nhóm chứa trường lỗi rồi đưa con
  trỏ tới đúng ô cần sửa. Giữ cảnh báo rời trang khi chưa lưu.
- Không đổi cấu trúc dữ liệu hoặc giao diện ngoài website. Khi tắt
  JavaScript, biểu mẫu gốc vẫn hiển thị các trường như bản 1.9.9.
- Đã kiểm tra trình duyệt desktop/mobile, chọn ảnh bằng API mô phỏng,
  lưu biểu mẫu và đọc lại bằng PHP. Chưa kiểm tra trên hosting thật.

FPT LANDING PAGE — 1.9.9
========================================
- Thêm bảng giá bên dưới bài viết, trước FAQ, luôn hiển thị kể cả khi
  bài viết thu gọn. Bốn cột: gói, giá, đăng ký, thiết bị/dịch vụ đi kèm.
- FPT Landing Page > Bảng giá tổng hợp: bật/tắt bảng, sửa tiêu đề,
  ghi chú, tên các cột, chữ trên nút; thêm/xóa/đổi thứ tự tối đa 30 dòng.
  Mỗi dòng có tên gói/tốc độ, giá tháng và thiết bị nhập riêng.
- Sáu dòng khởi tạo theo ảnh người dùng, ghi rõ giá tham khảo. Đây là
  bảng nhập riêng, không tự đồng bộ với giá trên thẻ hoặc giá khu vực.
- Nút Đăng ký dùng popup hiện tại. Khách đăng ký và email lưu đúng gói
  trong bảng; giá tham khảo được đọc lại từ cấu hình trên máy chủ.
- Điện thoại có khung vuốt ngang kèm hướng dẫn, giữ trang không tràn.
  Bảng dùng tiêu đề hàng/cột và vùng cuộn truy cập được bằng bàn phím.
- Cài đặt chung > Cam kết hỗ trợ: ba ô riêng cho dòng dấu tick dưới
  phần giới thiệu đầu trang; để trống để ẩn từng nội dung hoặc cả dòng.
- Kiểm tra PHP/API WordPress mô phỏng và Chromium 320–1366 px,
  lưu biểu mẫu thật qua trình duyệt, thêm/xóa/đổi thứ tự và đăng ký.
  Chưa kiểm tra trực tiếp trên hosting/SMTP của người dùng.

FPT LANDING PAGE — 1.9.8
========================================
- Giữ bố cục popup, kiểm tra số điện thoại Việt Nam ở cả trình duyệt và
  máy chủ. Nhận số di động 10 chữ số, số cố định 11 chữ số; chuẩn hóa
  +84, 0084 hoặc 84 về đầu 0, cho phép dấu cách, ngoặc và dấu phân cách.
- Nút Đăng ký chuyển sang Đang gửi và khóa trong lúc gửi; trở lại trang
  bằng nút Back sẽ khôi phục nút. Trường hợp tắt JavaScript vẫn được
  kiểm tra và chống gửi trùng trên máy chủ.
- Yêu cầu trùng (số điện thoại, loại yêu cầu, gói, khu vực, địa chỉ,
  dịch vụ) trong 10 phút không tạo thêm khách hay gửi thêm email.
- Giới hạn 3 yêu cầu mới / số điện thoại / 10 phút và 10 yêu cầu mới /
  địa chỉ mạng / 10 phút. Địa chỉ mạng lấy từ REMOTE_ADDR, không tin
  header do khách tự gửi. Hosting dùng proxy cần cấu hình địa chỉ
  khách đúng tại máy chủ. Khóa và bộ đếm dùng khóa băm, không chứa
  số điện thoại hay địa chỉ IP thô. Khóa tạm hết hạn sau 120 giây;
  tác vụ dọn khóa bỏ sót chạy hằng ngày và dừng khi tắt plugin.
- Khách đăng ký: thêm cột Thông báo email và hộp trạng thái khi mở
  từng khách. Ghi email nhận, lần thử gần nhất, số lần thử. Nút Gửi lại
  thông báo có kiểm tra quyền quản trị, nonce và khoảng chờ 60 giây.
  Gửi lại dùng email hiện tại trong Cài đặt đăng ký, không tạo khách mới.
- Hệ thống đã nhận yêu cầu gửi chỉ có nghĩa wp_mail trả về thành công,
  không bảo đảm thư đã vào hộp thư. Mail lỗi vẫn giữ dữ liệu khách.
  Khách cũ hiển thị Chưa có lịch sử gửi cho đến lần gửi lại đầu tiên.
- Cài đặt chung > Danh mục trống: tùy chọn Ẩn danh mục chưa có gói,
  bao gồm doanh nghiệp. Mặc định tắt để giữ cách hiển thị cũ. Nếu nhóm
  mở sẵn bị ẩn, tự chọn nhóm có gói; nhóm vẫn chỉnh được trong quản trị.
- CSS/JS chỉ nạp sớm khi tìm thấy shortcode trong nội dung, synced
  block, widget Shortcode hoặc mẫu Elementor được tham chiếu. Các mẫu
  xuất muộn vẫn in CSS trước nội dung và nạp JS ở footer. Tích hợp
  đặc biệt có thể dùng filter fptlp_load_assets để nạp sớm theo trang.
- Đã kiểm tra với API WordPress/SMTP mô phỏng và Chromium. Chưa kiểm
  tra trên cơ sở dữ liệu WordPress, theme, Elementor và SMTP hosting thật.

FPT LANDING PAGE — 1.9.7
========================================
- Căn giữa nhóm thẻ gói cước khi còn dư chỗ, bao gồm mục doanh nghiệp.
- Giữ chiều rộng thẻ; khi nhiều gói vượt khung vẫn cuộn ngang bình thường.
- Kiểm tra 1–5 gói tại các chiều rộng 320, 390, 850 và 1366 px.

FPT LANDING PAGE — 1.9.6
========================================
- Khi mở bài viết, chỉ hiện một nút Thu gọn bài viết ở cuối bài.
- Khi đóng bài viết, hiện nút Xem thêm bài viết ở đầu.
- Giữ hỗ trợ bàn phím; nếu tắt JavaScript, dùng nút đóng/mở gốc.

FPT LANDING PAGE — 1.9.5
========================================
- Thanh tab phía trên còn 4 nhóm; gói doanh nghiệp dùng lại dữ liệu cũ
  và hiển thị thành một khu vực độc lập bên dưới phần giới thiệu.
- FPT Landing Page > Giới thiệu Internet: sửa tiêu đề, đoạn ngắn,
  thẻ màu và ảnh chọn qua Media Library; thêm/xóa/đổi thứ tự các thẻ.
- FPT Landing Page > Gói doanh nghiệp: sửa tiêu đề, đoạn giới thiệu
  và danh sách gói doanh nghiệp trong một màn hình riêng.
- FPT Landing Page > Bài viết Internet FPT: sửa phần mở đầu, từng
  tiêu đề/đoạn văn và mức phí hòa mạng. Phí nằm trong bài viết, không
  nhập chung một chuỗi. Nội dung khởi tạo ghi rõ các mức phí tham khảo
  do chủ website lựa chọn từ ảnh mẫu; cần xác nhận mức áp dụng thực tế.
- Bài viết có Xem thêm/Thu gọn. FAQ đóng/mở độc lập, dùng được cả khi
  JavaScript tắt; nội dung được xuất sẵn trong HTML, không tải qua AJAX.
- FPT Landing Page > Câu hỏi thường gặp: thêm/xóa/đổi thứ tự, sửa
  riêng câu hỏi và câu trả lời. Có thể tắt hiển thị từng mục nội dung.
- Các biểu mẫu chỉ lưu phần đang chỉnh, giữ dữ liệu khác và chặn lưu
  khi biểu mẫu bị cắt. Dữ liệu gói cước/khách đăng ký/popup được giữ lại.
- Đã kiểm tra với API WordPress và Media Library mô phỏng,
  Chromium desktop/mobile; chưa cài trên hosting thực tế.

- Ba thẻ giới thiệu trên điện thoại gọn hơn: chiều cao tối thiểu 96 px,
  khoảng cách 10 px; chữ dài tự tăng chiều cao, không bị cắt.

CẬP NHẬT
- WordPress > Plugin > Cài mới > Tải plugin lên: chọn ZIP 1.9.5,
  sau đó chọn thay thế phiên bản hiện tại. Giữ thư mục fpt-landing-plugin.
- Sau khi cập nhật, xóa cache website/trình duyệt nếu còn giao diện cũ.
- Các mục nội dung mới nằm trong menu FPT Landing Page.

LỊCH SỬ BẢN ĐÃ PHÁT HÀNH
FPT LANDING PAGE — 1.9.4
========================
Shortcode: [fpt_landing_page]

HOÀN THIỆN GIAO DIỆN TRONG 1.9.4
- Gộp bản thử thẻ nhỏ, ảnh bo góc và đồng hồ chuyển động vào plugin.
- Bốn thẻ nằm gọn trong vùng nội dung rộng từ 1080 px; tự giảm xuống
  3, 2 hoặc 1 cột theo chiều rộng khung chứa, kể cả trong Elementor.
- Khung ảnh thống nhất tỷ lệ 62:45, bo góc trên/dưới. Ảnh khác tỷ lệ
  vẫn giữ đầy đủ nội dung trong khung trắng, không kéo méo hoặc cắt chữ.
  Nên dùng ảnh cùng kích thước 1240 × 900 px để phủ kín khung.
- Trang quản trị thông báo kích thước và tỷ lệ ảnh khi chọn ảnh gói.
  Không sửa hay xóa tệp ảnh gốc trong thư viện WordPress.
- Nhãn luôn ở trong ảnh và chỉ chiếm một dòng. Nhãn dài được thu gọn
  bằng dấu ba chấm; nội dung đầy đủ vẫn lưu và hiển thị trong Xem nhanh.
  Nên dùng nhãn khoảng 24 ký tự, chuyển khuyến mãi dài vào Quyền lợi.
- Tên gói dành chỗ cho hai dòng để giá thẳng hàng; tên quá dài hiện
  đầy đủ trong Xem nhanh và chú thích khi rê chuột vào tên.
- Kim đồng hồ và cung vàng chạy cùng nhau một lần trong 1,5 giây khi
  tải trang. Giga dừng ở 1/3, các gói khác hết cung. Đổi tab không chạy lại.
- Thêm ô Mức đồng hồ tốc độ: lần lưu đầu tự nhận Giga, sau đó lưu
  riêng mức 1/3 hoặc đầy cung để đổi tên gói không làm đổi vị trí kim.
  Thiết bị bật giảm chuyển động hiển thị ngay vị trí cuối.
- Hợp nhất CSS thẻ và bỏ các quy tắc cũ chồng nhau. Giữ popup đăng ký,
  dữ liệu gói cước, giá khu vực, thiết bị và thư viện ảnh WordPress.

KIỂM TRA BẢN 1.9.4
- PHP: dữ liệu cũ, lưu mức đồng hồ khi đổi tên, lưu nhiều lần, quyền lợi,
  thiết bị, giá khu vực và tiếp nhận đăng ký bằng API WordPress mô phỏng.
- Chromium: 320–1920 px; khung nhúng 440/680/850/1200 px; ảnh khác tỷ lệ,
  nhãn/tên dài, vị trí đồng hồ, giảm chuyển động, Xem nhanh và popup.
- Trình quản trị: chọn ảnh (Media API mô phỏng), thông báo tỷ lệ,
  đổi tên gói, thêm/xóa trường, gửi biểu mẫu và đọc lại bằng PHP.
- Chưa kiểm tra trên hosting, theme/Elementor và SMTP thực tế của bạn.

BỐ CỤC THẺ GỌN TRONG 1.9.3
- Thẻ gói cước gọn hơn; hiển thị 4 thẻ trên màn hình rộng, 3 hoặc 2 thẻ
  ở kích thước trung gian và vuốt ngang trên điện thoại.
- Nhãn nằm bên trong góc trên trái ảnh, theo bản xem trước đã chọn.
- Ảnh giữ nguyên tỷ lệ trong khung 1.55:1; không cắt mất nội dung ảnh.
- Thu gọn tiêu đề, tốc độ, quyền lợi; căn đều các nút. Quyền lợi dài
  cuộn trong thẻ và vẫn hiển thị đầy đủ ở phần Xem nhanh.
- Giữ popup đăng ký, dữ liệu gói và bộ chọn ảnh WordPress hiện tại.
- Nhãn phủ trên ảnh: banner có chữ sát mép trên có thể bị che. Nên để
  khoảng trống phía trên nội dung chính khi chuẩn bị ảnh banner.
- Kiểm tra bằng Chromium với dữ liệu mẫu; cần xem lại với ảnh thực tế
  và theme/cache trên hosting sau khi cài bản thử này.

ĐIỀU CHỈNH ẢNH TRONG 1.9.2
- Giữ nhãn nổi bật ở góc trên trái của thẻ như bố cục ban đầu.
- Với thẻ có nhãn, ảnh thu vào 8 px mỗi bên và có khoảng cách phía trên
  để nhãn không đè lên chữ. Nhãn dài tự xuống dòng và đẩy ảnh xuống.
- Ảnh vẫn giữ tỷ lệ, hiển thị trọn trong khung 16:9. Thẻ không có nhãn
  vẫn dùng toàn chiều rộng ảnh.
- Đã kiểm tra các cỡ màn hình từ 320 đến 1280 px, nhãn ngắn/dài/không có,
  đồng thời kiểm tra lại xem nhanh, giá khu vực và popup đăng ký.

ĐIỀU CHỈNH ẢNH TRONG 1.9.1
- Ảnh gói cước nằm trong khung 16:9, giữ đúng tỷ lệ và hiển thị trọn ảnh.
- Nhãn nổi bật chuyển xuống ngay dưới ảnh, không chồng lên chữ trong ảnh.
- Nhãn dài tự xuống dòng; gói không có nhãn không chừa hàng nhãn trống.
- Ảnh khác tỷ lệ 16:9 có thể có khoảng nền để giữ đầy đủ nội dung.
- Đã kiểm tra Chromium ở 320, 344, 390, 444, 768 và 1280 px với ảnh
  thử 16:9 và 4:3, nhãn ngắn/dài/không có nhãn; kiểm tra lại xem nhanh,
  giá khu vực và popup đăng ký. Chưa kiểm tra trên hosting của bạn.

TÍNH NĂNG MỚI TRONG 1.9.0
- Xem nhanh ngay trong thẻ: hiện đầy đủ quyền lợi, tốc độ, ảnh thiết bị,
  giá và nút đăng ký. Bấm Quay lại hoặc Escape để đóng.
- Liên kết chi tiết đã nhập vẫn được giữ: nằm tại nút Xem trang chi tiết
  bên trong phần Xem nhanh.
- Thiết bị / quà tặng đi kèm: thêm nhiều tên và ảnh riêng qua thư viện
  WordPress; dùng nút Xóa thiết bị để bỏ khỏi gói, không xóa ảnh gốc.
- Giá theo khu vực: nhập tên khu vực và giá đã xác nhận cho từng gói.
  Các gói nên dùng cùng tên khu vực, ví dụ Hà Nội nội thành.
- Bộ chọn khu vực chỉ xuất hiện khi có ít nhất một giá khu vực được lưu.
  Lựa chọn được ghi nhớ trên trình duyệt. Không tự xác định vị trí khách.
- Gói chưa có giá riêng cho khu vực đã chọn sẽ giữ giá mặc định kèm
  thông báo rằng đó là giá tham khảo. Không tự suy đoán hoặc chép giá.
- Popup đăng ký giữ bố cục, các ô nhập và cách đóng/mở hiện tại.
  Khu vực, gói và giá tham khảo được ghi thêm vào khách đăng ký và email.
  Giá được xác định từ cài đặt phía máy chủ tại thời điểm tiếp nhận.

CÁCH THỬ BẢN 1.9.0
1. Cập nhật ZIP, mở Quản lý gói cước và chọn một gói hiện có.
2. Tại Thiết bị / quà tặng đi kèm, bấm Thêm thiết bị, nhập tên và chọn ảnh.
3. Tại Giá theo khu vực, thêm tên khu vực và giá thực tế của gói đó.
4. Bấm Lưu tất cả gói cước. Mở landing page, tải lại và chọn khu vực.
5. Bấm Xem nhanh rồi Đăng ký gói này để kiểm tra popup hiện tại.
Không cần nhập lại dữ liệu cũ. Plugin không tự thêm giá khu vực hay
ảnh thiết bị mẫu vào website. Các hình/giá trong kiểm tra là fixture.

KIỂM TRA BẢN 1.9.0
- PHP: đọc dữ liệu cũ, lưu thiết bị/giá khu vực, chống dữ liệu thiếu/trùng,
  giữ dữ liệu khi lưu bị cắt, thông tin khách và nội dung email mô phỏng.
- Chromium thực: giao diện desktop, tablet và mobile 320/390 px; xem
  nhanh, chuyển khu vực, nhớ lựa chọn, popup, thêm/xóa các ô quản trị.
- Media Library được mô phỏng theo vòng đời WordPress trong kiểm tra.
- Chưa kiểm thử trực tiếp trên hosting, theme/Elementor và SMTP của bạn.
- CSS và JavaScript popup gốc không thay đổi; giao diện mới có tài
  nguyên riêng. Cập nhật phiên bản giúp trình duyệt tải đúng mã mới.

SỬA LỖI TRONG 1.8.2
- Sửa lỗi bấm Chọn ảnh / Đổi ảnh không mở thư viện: chỉ truy cập
  selection khi WordPress đã khởi tạo trạng thái của cửa sổ Media.
- Tải Media và tài nguyên quản trị qua admin_enqueue_scripts, trước
  khi WordPress in phần đầu trang, giới hạn ở các trang của plugin.
- Thông báo rõ nếu Media API chưa tải được.
- Tăng phiên bản tài nguyên để trình duyệt nhận bản sửa mới.
- Giữ nguyên bố cục popup đăng ký và dữ liệu đang lưu.
- Đã tái hiện lỗi 1.8.1 bằng mô phỏng đúng vòng đời Media, kiểm tra
  bản sửa trên trang cài đặt, banner và gói cước. Chưa kiểm thử trực tiếp
  trên hosting của bạn.

SỬA LỖI TRONG 1.8.1
- Lưu biểu mẫu: chỉ mở nhóm và tập trung vào ô không hợp lệ đầu tiên.
- Không ẩn nhầm quyền lợi khi dữ liệu cũ có ô trống xen giữa.
- Tiêu đề đăng ký trong cài đặt nay được áp dụng cho popup.
- ID của popup và biểu mẫu không trùng khi dùng nhiều shortcode.
- Tránh gắn lặp sự kiện frontend; giữ tiêu đề khi chuyển qua chế độ gọi lại.
- Hiển thị thông báo lưu của WordPress trên trang cài đặt.
- Tái sử dụng cửa sổ chọn ảnh; xóa lựa chọn cũ sau khi cửa sổ khởi tạo xong.
- Khách đăng ký chỉ được truy cập bằng quyền manage_options, tương tự
  trang quản trị plugin. Tài khoản biên tập viên mặc định sẽ không xem
  được thông tin khách hàng qua đường dẫn quản trị trực tiếp.
- Giữ nguyên CSS, bố cục và các trường trong popup.

THAY ĐỔI TỪ 1.8.0
- Quản lý gói cước bằng các trường riêng: tên, giá, tốc độ tải xuống,
  tốc độ tải lên, nhãn nổi bật và liên kết chi tiết.
- Mỗi quyền lợi là một ô nhập. Có nút thêm / xóa quyền lợi.
- Thêm, xóa, thu gọn và đổi thứ tự từng gói trong 5 nhóm dịch vụ.
- Ảnh banner và ảnh gói cước: chọn / tải lên qua thư viện WordPress,
  xem trước ngay, đổi hoặc gỡ ảnh.
- Đọc dữ liệu dạng dòng của bản cũ. Khi lưu, nhóm gói được lưu thành
  các trường có cấu trúc; không phải nhập ký tự phân cách | hoặc ;.
- Giữ nguyên giao diện landing page, popup đăng ký / gọi lại,
  xử lý khách đăng ký và email thông báo.

CÁCH DÙNG
1. WordPress > FPT Landing Page > Quản lý gói cước.
2. Chọn nhóm cần sửa; nhập các trường trong từng thẻ gói.
3. Giá: ví dụ 195.000đ. Tốc độ: ví dụ 300 Mbps hoặc 1 Gbps.
4. Chọn ảnh, chọn trong thư viện hoặc tải lên, bấm Dùng ảnh này.
5. Bấm Lưu tất cả gói cước. Các thay đổi chỉ áp dụng sau khi lưu.

Mỗi nhóm vẫn hiển thị tối đa 6 gói đầu trên landing page.
Các gói vượt quá 6 vẫn được lưu và có thể chuyển lên trên.
Gỡ ảnh chỉ bỏ ảnh khỏi banner / gói, không xóa tệp trong thư viện.
Gỡ banner sẽ dùng hình minh họa mặc định.

CẬP NHẬT
Sao lưu cơ sở dữ liệu và plugin hiện tại. Vào Plugins > Add New >
Upload Plugin, chọn FPT-Landing-v1.9.4.zip và thay thế phiên bản cũ.
Giữ nguyên thư mục fpt-landing-plugin. Không cần xóa plugin trước.
Sau cập nhật, kiểm tra các nhóm gói và chọn ảnh trên website của bạn.
Nếu khôi phục bản 1.7.7 sau khi đã lưu gói bằng 1.8.0 trở lên, khôi phục cả
bản sao cài đặt / cơ sở dữ liệu trước cập nhật vì định dạng lưu đã đổi.

KIỂM TRA PHÁT TRIỂN
Đã kiểm tra cú pháp PHP / JavaScript, đọc dữ liệu cũ, lưu dữ liệu mới,
đổi thứ tự và các thao tác quản trị trong môi trường mô phỏng API
WordPress / Media. Chưa kiểm thử tích hợp trên một website WordPress
thực tế. Bản 1.8.1 đạt 52 kiểm tra PHP / DOM mô phỏng cùng kiểm tra cú pháp.
CSS frontend không thay đổi. Các sửa đổi JavaScript / markup popup
chỉ xử lý tiêu đề, ID và sự kiện; không thay đổi bố cục.

Nếu quay lại 1.8.x và tiếp tục lưu gói, các trường thiết bị / giá khu vực
của 1.9.0 có thể bị bỏ. Giữ bản sao cơ sở dữ liệu trước khi hạ phiên bản.
