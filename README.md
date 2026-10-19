# VETCARE CLINIC OPERATING SYSTEM (VETCARE OS)

Hệ thống Quản trị Vận hành Toàn diện Phòng khám Thú y  
Nền tảng ứng dụng: Laravel 13.x + Vue 3 (Composition API) + Inertia.js v2 + Tailwind CSS + MariaDB + Google Gemini AI

---

## 1. GIỚI THIỆU TỔNG QUAN

VetCare Clinic OS là giải pháp phần mềm quản trị toàn diện dành cho các phòng khám, bệnh viện thú y và cơ sở chăm sóc thú cưng. Hệ thống giải quyết trọn vẹn chu trình khám chữa bệnh thực tế: từ tiếp đón khách hàng, điều phối lịch hẹn, hồ sơ bệnh án điện tử, hỗ trợ chẩn đoán lâm sàng bằng Trí tuệ Nhân tạo (Google Gemini AI), quản lý kho dược phẩm đa lô theo nguyên tắc FEFO, cho đến tự động kết xuất hóa đơn viện phí, quản lý sổ quỹ và báo cáo tài chính phân tích chuyên sâu.

Dự án được xây dựng theo kiến trúc Modern Monolithic Single Page Application (Modern Monolith SPA) kết hợp giữa framework Laravel 13 ở phía máy chủ và Vue 3 ở phía trình duyệt thông qua giao thức cầu nối Inertia.js v2, mang lại tốc độ xử lý nhanh, trải nghiệm người dùng liền mạch như ứng dụng desktop nhưng vẫn duy trì tính toàn vẹn và bảo mật cao ở backend.

---

## 2. VẤN ĐỀ VẬN HÀNH VÀ GIẢI PHÁP

| Thực trạng phòng khám truyền thống | Giải pháp của VetCare Clinic OS |
| :--- | :--- |
| **Thất thoát kho thuốc:** Thuốc nhập nhiều đợt với hạn dùng khác nhau nhưng chỉ quản lý tổng số lượng; nhân viên hay xuất nhầm thuốc mới nhập, bỏ quên thuốc cũ dẫn đến quá hạn phải tiêu hủy. | **Quản lý đa lô (medicine_batches) theo FEFO:** Thuật toán tự động quét và trừ từ lô có hạn dùng gần nhất đến các lô xa hơn; bảo vệ giao dịch nguyên tử bằng Database Transaction và khóa bi quan (`lockForUpdate`). |
| **Xung đột điều phối lịch hẹn:** Khách hàng đến sảnh chờ không rõ bác sĩ nào khám, bác sĩ khám chéo ca của nhau, phân quyền lỏng lẻo gây sai lệch trách nhiệm y khoa. | **Smart Check-in & Doctor-Specific Isolation:** Tiếp đón check-in linh hoạt qua modal; phân định rõ quyền sở hữu ca khám (bác sĩ chỉ được khám và xem bệnh án ca mình phụ trách); lễ tân bị chặn tuyệt đối quyền tạo bệnh án (403 Forbidden). |
| **Thất thoát viện phí & Nhập liệu thủ công:** Tiền công dịch vụ và tiền thuốc tính riêng lẻ, dễ bỏ sót thủ thuật khi thu ngân tính tiền. | **Tự động sinh Hóa đơn Viện phí 2-trong-1:** Ngay khi bác sĩ hoàn tất ca khám, hệ thống tự động tổng hợp tiền công dịch vụ kỹ thuật và tiền thuốc thành một hóa đơn duy nhất ở trạng thái chờ thanh toán. |
| **Hạn chế trong hỗ trợ chẩn đoán:** Bác sĩ mới ra trường gặp khó khăn khi phân biệt các ca bệnh có triệu chứng tương đồng hoặc tương tác thuốc phức tạp. | **Trợ lý AI Gemini Clinical Copilot:** Tích hợp mô hình Google Gemini 1.5 Flash đóng vai trò cố vấn lâm sàng, tự động đối chiếu triệu chứng với danh mục thuốc và dịch vụ hiện có trong kho để gợi ý phác đồ chính xác. |

---

## 3. NGĂN XẾP CÔNG NGHỆ (TECHNOLOGY STACK)

### 3.1. Máy chủ Ứng dụng & Backend
- **Ngôn ngữ:** PHP 8.3+ (tận dụng Typed Properties, Match Expressions, Readonly Classes).
- **Framework cốt lõi:** Laravel Framework 13.x.
- **Kiến trúc SPA Bridge:** Inertia.js Laravel Adapter (`inertiajs/inertia-laravel: ^2.0`).
- **Phân quyền & Kiểm soát truy cập:** Spatie Laravel-Permission (`spatie/laravel-permission: ^8.3`).
- **Xác thực người dùng:** Laravel Breeze & Laravel Sanctum.
- **Xuất văn bản PDF:** Barryvdh Laravel DomPDF (`barryvdh/laravel-dompdf: ^3.1`) hỗ trợ Unicode tiếng Việt đầy đủ.
- **Xuất bảng tính:** Maatwebsite Excel (`maatwebsite/excel: ^4.0`) kết hợp luồng xuất CSV mã hóa UTF-8 BOM chuẩn tiếng Việt.
- **Định tuyến JavaScript:** Tightenco Ziggy (`tightenco/ziggy: ^2.0`).

### 3.2. Giao diện & Frontend
- **Framework hiển thị:** Vue.js 3.4+ (sử dụng hoàn toàn cú pháp `<script setup>` của Composition API).
- **Client Router & State Bridge:** `@inertiajs/vue3: ^2.0` (hỗ trợ Persistent Layouts, Partial Reloads).
- **CSS Framework:** Tailwind CSS 3.2+ (Utility-first CSS, thiết kế responsive trên mọi độ phân giải).
- **Trực quan hóa dữ liệu:** Chart.js 4.5+ (biểu đồ cột kép so sánh doanh thu - chi phí, biểu đồ tròn cơ cấu nguồn thu).
- **Giao tiếp HTTP:** Axios 1.19+ (xử lý các yêu cầu nền cho cơ chế Smart Polling và gọi API chẩn đoán).
- **Build Tool:** Vite 8.0+ với Hot Module Replacement (HMR).

### 3.3. Cơ sở dữ liệu & Lưu trữ
- **Hệ quản trị CSDL:** MariaDB 10.4+ / MySQL 8.x.
- **Storage Engine:** InnoDB (hỗ trợ Khóa ngoại, Ràng buộc toàn vẹn và Giao dịch ACID).
- **Bảng mã (Collation):** `utf8mb4_unicode_ci`.
- **Cấu trúc bảng:** 14 bảng quan hệ chuẩn hóa 3NF.

### 3.4. Trí tuệ Nhân tạo (AI Integration)
- **Engine chính:** Google Gemini 1.5 Flash RESTful API (`gemini-1.5-flash`).
- **Cơ chế:** RAG nhẹ (Retrieval-Augmented Generation) nạp trực tiếp danh mục thuốc còn tồn kho và danh mục dịch vụ phòng khám vào context prompt.
- **Fallback Engine:** Bộ quy tắc chuyên gia thú y nội bộ (Rule-based Expert System) tự động kích hoạt khi mất kết nối Internet hoặc vượt hạn mức API.

---

## 4. KIẾN TRÚC HỆ THỐNG VÀ NGUYÊN LÝ HOẠT ĐỘNG

### 4.1. Mô hình Phân tầng (Architectural Layers)

```
[ Trình duyệt Người dùng (Client Browser) ]
  - Vue 3 Components (Composition API)
  - Persistent Layout (AuthenticatedLayout, Sidebar)
  - Reactive Smart Polling Engine (30s)
               |
               |  Giao thức Inertia.js v2 (HTTP Header X-Inertia: true)
               v
[ Máy chủ Ứng dụng Laravel (Backend Application) ]
  - Routing & Middleware (Authenticate, Role Authorization, VerifyCsrfToken)
  - Controllers (AppointmentController, MedicalRecordController, InvoiceController...)
  - Form Request Validation Layer (StoreMedicalRecordRequest, StoreAppointmentRequest...)
  - Services Layer (InventoryService, AiDiagnosisService)
  - Background Jobs (GenerateInvoiceJob)
  - Notification Channels (Database, Mail)
               |
               |  Eloquent ORM & ACID Transactions (lockForUpdate)
               v
[ Cơ sở Dữ liệu & Dịch vụ Bên ngoài ]
  - MariaDB 10.4 / MySQL (14 bảng dữ liệu quan hệ)
  - Google Cloud Generative Language API (Gemini 1.5 Flash)
  - Hệ thống lưu trữ file cục bộ (Ảnh đại diện, file PDF)
```

### 4.2. Các Mẫu thiết kế phần mềm (Design Patterns)
- **Service Layer Pattern:** Logic trừ kho FEFO phức tạp và logic kết nối Gemini AI được đóng gói riêng biệt tại `app/Services/InventoryService.php` và `app/Services/AiDiagnosisService.php`, giúp Controller luôn tinh gọn và độc lập.
- **Role-Based Access Control (RBAC):** Phân chia 4 vai trò rõ ràng: `admin` (Quản trị viên), `vet` (Bác sĩ thú y), `receptionist` (Lễ tân), `accountant` (Kế toán).
- **Row-Level Security & Doctor-Specific Isolation:** Kiểm tra quyền hạn trực tiếp tại tầng Controller/Policy, đảm bảo bác sĩ chỉ được thao tác trên ca khám được phân công cho chính mình.
- **Pessimistic Locking Pattern:** Sử dụng `lockForUpdate()` khi kiểm tra và trừ tồn kho, triệt tiêu hoàn toàn rủi ro Race Condition khi nhiều bác sĩ cùng kê một loại biệt dược tại cùng thời điểm.

---

## 5. CÁC PHÂN HỆ CHỨC NĂNG CỐT LÕI

### 5.1. Phân hệ Tiếp đón & Điều phối Lịch hẹn
- Quản lý hồ sơ chủ nuôi và thú cưng (tìm kiếm nhanh bằng số điện thoại, lọc phân trang máy chủ).
- Hỗ trợ cả hai hình thức: Đặt lịch hẹn trước qua điện thoại/website và tiếp nhận khách vãng lai (walk-in).
- Lọc lịch khám theo ngày, khoảng ngày, bác sĩ điều trị và trạng thái (`pending`, `confirmed`, `checked_in`, `completed`, `cancelled`, `no_show`).
- Dynamic Check-in Modal: Cho phép lễ tân chỉ định hoặc đổi bác sĩ điều trị tại thời điểm tiếp nhận, ghi nhận thân nhiệt và triệu chứng ban đầu.
- Cơ chế nhận ca khám tự do (Claim Appointment): Bác sĩ có thể chủ động nhận các ca tiếp đón vãng lai chưa có bác sĩ chỉ định.

### 5.2. Phân hệ Bệnh án Điện tử & Kê đơn Dược phẩm
- Hồ sơ bệnh án điện tử lưu trữ triệu chứng lâm sàng, chẩn đoán, cân nặng, thân nhiệt và lời dặn dò.
- Medical History Timeline: Tự động tải và hiển thị toàn bộ lịch sử các lần khám trước của thú cưng theo trục thời gian, giúp bác sĩ nắm bắt bệnh nền và tiền sử dị ứng thuốc chỉ trong vài giây.
- Kê đơn thuốc với thành phần tìm kiếm Search-Select thông minh, tự động hiển thị số lượng tồn kho khả dụng và đơn giá niêm yết.
- Gắn kèm các chỉ định dịch vụ kỹ thuật (siêu âm, xét nghiệm máu, chụp X-quang, triệt sản, tiêm chủng).
- Xuất phiếu khám bệnh định dạng PDF tiếng Việt chuẩn A4.

### 5.3. Cố vấn Trí tuệ Nhân tạo (Gemini AI Clinical Copilot)
- Tích hợp trực tiếp trên giao diện tạo bệnh án của bác sĩ.
- Bác sĩ chỉ cần nhập loài vật nuôi, cân nặng, thân nhiệt và triệu chứng quan sát được.
- AI phân tích và trả về:
  1. Chẩn đoán chính có xác suất cao nhất kèm độ tin cậy.
  2. Hai chẩn đoán phân biệt cần lưu ý.
  3. Gợi ý thuốc điều trị (ràng buộc nghiêm ngặt: chỉ gợi ý các loại thuốc hiện đang còn hàng trong kho).
  4. Gợi ý dịch vụ kỹ thuật cận lâm sàng tương ứng trong bảng giá phòng khám.
  5. Lời khuyên chăm sóc gửi đến chủ nuôi.

### 5.4. Quản lý Kho Dược phẩm Đa lô theo thuật toán FEFO
- Quản lý chi tiết từng loại thuốc theo nhiều lô sản xuất (`medicine_batches`) với số lô, ngày nhập, giá nhập và ngày hết hạn riêng biệt.
- Thuật toán trừ kho FEFO (First-Expired, First-Out): Tự động ưu tiên trừ từ lô thuốc có hạn dùng gần nhất đến các lô có hạn dùng xa hơn.
- Toàn bộ quá trình được bọc trong giao dịch `DB::transaction()` và khóa dòng dữ liệu; tự động rollback nếu tổng số lượng thực tế trong kho không đáp ứng đủ.
- Tự động tính toán lại tổng tồn kho và ngày hết hạn gần nhất tại bảng cha `medicines`.
- Hệ thống tự động phát hiện và gửi thông báo cảnh báo tồn kho thấp (Low Stock Alert) tới Quản trị viên khi lượng thuốc chạm ngưỡng an toàn.

### 5.5. Tài chính, Hóa đơn Viện phí & Sổ quỹ
- Tự động kích hoạt `GenerateInvoiceJob` để sinh hóa đơn viện phí mã `INV-YYYYMMDD-XXXX` ngay khi bác sĩ hoàn thành ca khám.
- Hóa đơn tổng hợp 2 trong 1: Tiền công khám + Tiền dịch vụ kỹ thuật + Tiền thuốc kê đơn.
- Thu ngân kiểm tra và bấm "Xác nhận thanh toán" (hỗ trợ tiền mặt hoặc chuyển khoản); hệ thống lưu vết thời gian `paid_at` và tự động gửi thông báo cho bác sĩ điều trị.
- In hóa đơn viện phí định dạng PDF tiếng Việt trao tay khách hàng.
- Phân hệ Sổ quỹ Phiếu chi (Expense Vouchers): Ghi nhận chi phí nhập thuốc, chi phí mặt bằng, điện nước và vận hành, phục vụ đối soát dòng tiền.

### 5.6. Báo cáo Thống kê & Phân tích Dữ liệu
- Báo cáo Doanh thu - Chi phí - Lợi nhuận ròng: Biểu đồ cột kép thể hiện dòng tiền thu vào và chi ra theo từng ngày trong tháng; tự động tính toán lợi nhuận ròng thực tế.
- Phân tích cơ cấu nguồn thu (tỷ trọng tiền thuốc so với tiền dịch vụ thủ thuật).
- Báo cáo năng suất khám chữa bệnh và doanh thu theo từng bác sĩ.
- Xuất dữ liệu báo cáo tài chính ra file CSV/Excel kèm mã hóa UTF-8 BOM, đảm bảo mở trực tiếp trên Microsoft Excel không bị lỗi font tiếng Việt.
- Báo cáo cơ cấu bệnh tật phổ biến nhất phân loại theo loài (chó, mèo).
- Thống kê khách hàng thân thiết theo số lượt khám và tổng chi tiêu lũy kế.

### 5.7. Hệ thống Thông báo Đa kênh & Đồng bộ Real-time
- Sử dụng mô hình thông báo đa hình (Polymorphic Notifications) của Laravel.
- Tự động phát thông báo khi: Khách đặt lịch mới, Bệnh nhân check-in đến sảnh, Hóa đơn mới chờ thu ngân, Hóa đơn thanh toán thành công, Thuốc chạm ngưỡng tồn kho thấp.
- Cơ chế Smart Reactive Polling (chu kỳ 30 giây): Kiểm tra số lượng thông báo mới qua endpoint nhẹ mà không gây tải máy chủ.
- Đồng bộ tức thì giữa Pop-up menu chuông thông báo trên Header và Trang Trung tâm Thông báo (`/notifications`).

---

## 6. CẤU TRÚC CƠ SỞ DỮ LIỆU (14 BẢNG CHUẨN 3NF)

1. `users`: Tài khoản nhân viên, mật khẩu Bcrypt, số điện thoại, trạng thái hoạt động (`is_active`).
2. `roles`, `permissions`, `model_has_roles`: Cấu trúc phân quyền Spatie RBAC.
3. `customers`: Hồ sơ khách hàng, số điện thoại định danh duy nhất, địa chỉ, email.
4. `pets`: Hồ sơ thú cưng, loài (chó, mèo), giống, ngày sinh, cân nặng, ảnh đại diện, liên kết chủ nuôi.
5. `appointments`: Lịch hẹn khám, thời gian hẹn, bác sĩ phụ trách, trạng thái xử lý, ghi chú tiếp đón.
6. `medical_records`: Bệnh án điện tử, triệu chứng, chẩn đoán, cân nặng, thân nhiệt lúc khám, liên kết lịch hẹn và bác sĩ khám.
7. `medicines`: Danh mục thuốc, đơn vị tính, tổng tồn kho thực tế, giá nhập, giá bán, ngưỡng cảnh báo.
8. `medicine_batches`: Lô thuốc, mã lô sản xuất, số lượng nhập, số lượng còn lại, ngày hết hạn của lô (phục vụ FEFO).
9. `prescriptions`: Chi tiết đơn thuốc, liên kết bệnh án và thuốc, số lượng cấp, liều dùng, số ngày sử dụng.
10. `services`: Bảng giá dịch vụ kỹ thuật (tiêm chủng, phẫu thuật, siêu âm, xét nghiệm).
11. `medical_record_services`: Dịch vụ chỉ định trong ca khám, lưu vết đơn giá tại thời điểm thực hiện.
12. `invoices`: Hóa đơn viện phí, mã hóa đơn duy nhất, tiền khám, tiền thuốc, tiền dịch vụ, tổng tiền, trạng thái thanh toán.
13. `expense_vouchers`: Phiếu chi sổ quỹ phòng khám, phân loại danh mục chi, số tiền, ngày chi, chứng từ.
14. `notifications`: Bảng thông báo đa hình lưu vết nội dung, trạng thái đã đọc (`read_at`), liên kết người nhận.

---

## 7. CẤU TRÚC THƯ MỤC DỰ ÁN

```
vetcare/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AiDiagnosisController.php       # Controller tiếp nhận yêu cầu tham vấn AI
│   │   │   ├── AppointmentController.php       # Quản lý tiếp đón, check-in, lịch hẹn
│   │   │   ├── CustomerController.php          # Quản lý hồ sơ chủ nuôi
│   │   │   ├── DashboardController.php         # Thống kê bảng điều khiển theo vai trò
│   │   │   ├── ExpenseVoucherController.php    # Quản lý phiếu chi sổ quỹ
│   │   │   ├── InvoiceController.php           # Quản lý hóa đơn viện phí, thu tiền, in PDF
│   │   │   ├── MedicalRecordController.php     # Khám bệnh, kê đơn, lịch sử bệnh án
│   │   │   ├── MedicineController.php          # Kho thuốc, quản lý lô, nhập hàng
│   │   │   ├── NotificationController.php      # Quản lý thông báo, Smart Polling
│   │   │   ├── PetController.php               # Quản lý thú cưng, hồ sơ sức khỏe
│   │   │   ├── ReportController.php            # Báo cáo doanh thu, bệnh lý, xuất Excel
│   │   │   ├── ServiceController.php           # Quản lý danh mục bảng giá dịch vụ
│   │   │   └── UserController.php              # Quản lý tài khoản nhân viên phòng khám
│   │   └── Requests/                           # Form Requests kiểm duyệt dữ liệu đầu vào
│   ├── Jobs/
│   │   └── GenerateInvoiceJob.php              # Job tự động sinh hóa đơn viện phí tích hợp
│   ├── Models/                                 # 11 Eloquent Models tương ứng 14 bảng dữ liệu
│   ├── Notifications/                          # Các lớp thông báo hệ thống
│   └── Services/
│       ├── AiDiagnosisService.php              # Logic gọi Gemini AI & Fallback Rule Engine
│       └── InventoryService.php                # Thuật toán trừ kho FEFO & Pessimistic Lock
├── database/
│   ├── migrations/                             # 18 migration files tạo schema CSDL chuẩn hóa
│   └── seeders/                                # Dữ liệu mẫu ban đầu (Roles, Users, Thuốc, Dịch vụ)
├── resources/
│   ├── js/
│   │   ├── Components/                         # UI components (Sidebar, SearchSelect, StatCards...)
│   │   ├── Composables/
│   │   │   └── useAuth.js                      # Phân quyền tiện ích phía Vue (isAdmin, isVet...)
│   │   ├── Layouts/
│   │   │   ├── AuthenticatedLayout.vue         # Khung giao diện chính sau đăng nhập
│   │   │   └── GuestLayout.vue                 # Khung giao diện đăng nhập
│   │   ├── Pages/                              # 30+ màn hình Vue SPA hoàn chỉnh
│   │   └── app.js                              # Điểm khởi tạo ứng dụng Inertia & Vue 3
│   └── views/
│       ├── app.blade.php                       # Template HTML gốc chứa root element Inertia
│       └── pdf/                                # Template Blade xuất PDF Bệnh án & Hóa đơn
└── routes/
    ├── web.php                                 # Khai báo toàn bộ 34 routes & middleware RBAC
    └── auth.php                                # Khai báo routes xác thực người dùng
```

---

## 8. HƯỚNG DẪN CÀI ĐẶT VÀ TRIỂN KHAI

### 8.1. Yêu cầu Hệ thống
- PHP >= 8.3 với các extension: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `curl`.
- Composer >= 2.x.
- Node.js >= 18.x và PNPM / NPM.
- MariaDB >= 10.4 hoặc MySQL >= 8.0.

### 8.2. Các bước Cài đặt Chi tiết

**Bước 1: Chuyển vào thư mục dự án**
```bash
cd d:\third-project\vetcare
```

**Bước 2: Cài đặt các gói phụ thuộc Backend**
```bash
composer install
```

**Bước 3: Cài đặt các gói phụ thuộc Frontend**
```bash
pnpm install
```

**Bước 4: Thiết lập file môi trường (.env)**
```bash
cp .env.example .env
php artisan key:generate
```

Cấu hình thông số kết nối Cơ sở dữ liệu trong file `.env`:
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vetcare
DB_USERNAME=root
DB_PASSWORD=
```

Cấu hình khóa kết nối Google Gemini API (tùy chọn, hệ thống có sẵn Local Rule Engine dự phòng):
```ini
GEMINI_API_KEY=your_gemini_api_key_here
```

**Bước 5: Tạo bảng CSDL và nạp dữ liệu mẫu**
```bash
php artisan migrate:fresh --seed
```

**Bước 6: Khởi chạy máy chủ phát triển**
Mở 2 cửa sổ terminal:
```bash
# Terminal 1: Chạy máy chủ Laravel
php artisan serve
```

```bash
# Terminal 2: Chạy Vite biên dịch giao diện Frontend
pnpm dev
```

Truy cập ứng dụng tại địa chỉ: `http://127.0.0.1:8000`

---

## 9. TÀI KHOẢN TRẢI NGHIỆM THEO VAI TRÒ (DEMO ACCOUNTS)

Mật khẩu mặc định cho toàn bộ các tài khoản bên dưới là: `password`

| Vai trò người dùng | Email đăng nhập | Quyền hạn và phạm vi thao tác |
| :--- | :--- | :--- |
| **Quản trị viên (Admin)** | `admin@vetcare.local` | Toàn quyền kiểm soát hệ thống: Quản lý nhân viên, cấu hình bảng giá dịch vụ, quản lý thuốc, nhập lô hàng mới, xem toàn bộ báo cáo doanh thu tài chính. |
| **Bác sĩ Thú y (Vet)** | `bacsi@vetcare.local` | Xem danh sách ca khám được phân công, mở form khám bệnh điện tử, tham vấn AI Gemini, kê đơn thuốc trừ kho FEFO, xuất phiếu khám PDF. Bị chặn xem/sửa ca khám của bác sĩ khác. |
| **Nhân viên Lễ tân (Receptionist)** | `letan@vetcare.local` | Đăng ký khách hàng & thú cưng, đặt lịch hẹn, thực hiện Check-in chỉ định bác sĩ khám, xem danh sách hóa đơn và xác nhận thu tiền. Bị chặn tuyệt đối quyền tạo/sửa bệnh án (403). |
| **Kế toán Thu ngân (Accountant)** | `ketoan@vetcare.local` | Quản lý hóa đơn viện phí, xác nhận thanh toán, lập phiếu chi sổ quỹ chi phí hoạt động, xem biểu đồ doanh thu - chi phí - lợi nhuận và xuất file Excel UTF-8 BOM. |

---

## 10. BẢO MẬT VÀ TOÀN VẸN DỮ LIỆU

- **Bảo vệ chống tấn công CSRF:** Tự động kích hoạt qua token CSRF trên toàn bộ các yêu cầu POST/PUT/PATCH/DELETE.
- **Phòng chống SQL Injection:** Sử dụng 100% Eloquent ORM và Prepared Statements trong việc truy vấn dữ liệu.
- **Bảo vệ tính toàn vẹn kho hàng:** Ngăn ngừa race condition bằng `lockForUpdate()`, cam kết số lượng tồn kho giữa các lô và bảng thuốc cha luôn khớp nhau tuyệt đối.
- **Cách ly dữ liệu y tế (Row-level Isolation):** Ngăn chặn việc bác sĩ can thiệp vào bệnh án của đồng nghiệp, đảm bảo y đức và tính pháp lý của hồ sơ y khoa.
- **Custom Error Pages:** Hệ thống xử lý lỗi đồng bộ thương hiệu cho các mã lỗi HTTP chuẩn: 400, 401, 403, 404, 419, 500, 503.

---
