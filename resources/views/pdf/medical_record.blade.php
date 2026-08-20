<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Phieu_Kham_Benh_{{ str_pad($record->id, 4, '0', STR_PAD_LEFT) }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #1f2937;
            line-height: 1.4;
            margin: 0;
            padding: 10px;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .clinic-name {
            font-size: 16px;
            font-weight: bold;
            color: #4f46e5;
            text-transform: uppercase;
        }
        .clinic-info {
            font-size: 10px;
            color: #6b7280;
        }
        .doc-title {
            font-size: 15px;
            font-weight: bold;
            color: #111827;
            text-align: center;
            text-transform: uppercase;
            margin: 10px 0 5px 0;
        }
        .doc-sub {
            text-align: center;
            font-size: 10px;
            color: #4f46e5;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .info-box {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .info-box td {
            padding: 4px 6px;
            vertical-align: top;
            font-size: 11px;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #111827;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
            margin-top: 12px;
            margin-bottom: 8px;
        }
        .clinical-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 8px 10px;
            font-size: 11px;
            margin-bottom: 8px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .data-table th {
            background-color: #f3f4f6;
            color: #374151;
            font-size: 10px;
            text-transform: uppercase;
            border: 1px solid #e5e7eb;
            padding: 5px 8px;
            text-align: left;
        }
        .data-table td {
            border: 1px solid #e5e7eb;
            padding: 5px 8px;
            font-size: 11px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .signature-table {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .signature-table td {
            text-align: center;
            vertical-align: top;
            font-size: 11px;
        }
        .signature-space {
            height: 60px;
        }
    </style>
</head>
<body>

    <!-- Header thông tin phòng khám -->
    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                <div class="clinic-name">Phòng Khám Thú Y VETCARE</div>
                <div class="clinic-info">
                    Địa chỉ: Số 123 Đường Thú Y, Quận Cầu Giấy, TP. Hà Nội<br>
                    Hotline cấp cứu: 0901.234.567 · Email: contact@vetcare.vn
                </div>
            </td>
            <td style="width: 40%; text-align: right;" class="clinic-info">
                <strong>Mã phiếu:</strong> #KB-{{ str_pad($record->id, 4, '0', STR_PAD_LEFT) }}<br>
                <strong>Ngày khám:</strong> {{ $record->created_at->format('d/m/Y H:i') }}<br>
                <strong>Bác sĩ:</strong> BS. {{ $record->doctor->name ?? '—' }}
            </td>
        </tr>
    </table>

    <div class="doc-title">PHIẾU KHÁM BỆNH & CHỈ ĐỊNH ĐIỀU TRỊ</div>
    <div class="doc-sub">HỒ SƠ BỆNH ÁN ĐIỆN TỬ VETCARE</div>

    <!-- Thông tin Bệnh nhân & Khách hàng -->
    <table class="info-box">
        <tr>
            <td style="width: 50%;">
                <strong>Chủ vật nuôi:</strong> {{ $record->pet->customer->name ?? '—' }}<br>
                <strong>Điện thoại:</strong> {{ $record->pet->customer->phone ?? '—' }}<br>
                <strong>Địa chỉ:</strong> {{ $record->pet->customer->address ?? '—' }}
            </td>
            <td style="width: 50%;">
                <strong>Tên vật nuôi:</strong> {{ $record->pet->name ?? '—' }} ({{ ucfirst($record->pet->species ?? '') }})<br>
                <strong>Giống:</strong> {{ $record->pet->breed ?? '—' }} · <strong>Giới tính:</strong> {{ $record->pet->gender == 'male' ? 'Đực' : ($record->pet->gender == 'female' ? 'Cái' : '—') }}<br>
                <strong>Cân nặng:</strong> {{ $record->weight_at_visit ? $record->weight_at_visit . ' kg' : '—' }} · <strong>Thân nhiệt:</strong> {{ $record->temperature_at_visit ? $record->temperature_at_visit . ' °C' : '—' }}
            </td>
        </tr>
    </table>

    <!-- Khám lâm sàng & Chẩn đoán -->
    <div class="section-title">1. Khám lâm sàng & Chẩn đoán</div>
    <div class="clinical-box">
        <strong>Triệu chứng lâm sàng:</strong> {{ $record->symptoms }}
    </div>
    <div class="clinical-box" style="background-color: #eef2ff; border-color: #c7d2fe;">
        <strong style="color: #3730a3;">Chẩn đoán kết luận:</strong> <span style="color: #1e1b4b; font-weight: bold;">{{ $record->diagnosis }}</span>
    </div>

    <!-- Dịch vụ chỉ định -->
    @if($record->services && $record->services->count() > 0)
    <div class="section-title">2. Dịch vụ & Thủ thuật kỹ thuật</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;" class="text-center">STT</th>
                <th style="width: 60%;">Tên dịch vụ</th>
                <th style="width: 30%;" class="text-right">Đơn giá</th>
            </tr>
        </thead>
        <tbody>
            @foreach($record->services as $idx => $service)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>{{ $service->name }}</td>
                <td class="text-right">{{ number_format($service->pivot->price ?? $service->price, 0, ',', '.') }} đ</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Đơn thuốc chỉ định -->
    <div class="section-title">3. Đơn thuốc & Hướng dẫn sử dụng</div>
    @if(!$record->prescriptions || $record->prescriptions->count() === 0)
        <p style="font-size: 11px; font-style: italic; color: #6b7280; margin: 4px 0 10px 0;">Không có chỉ định dùng thuốc.</p>
    @else
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;" class="text-center">STT</th>
                <th style="width: 42%;">Tên thuốc / Quy cách</th>
                <th style="width: 30%;">Liều dùng & Cách dùng</th>
                <th style="width: 10%;" class="text-center">SL</th>
                <th style="width: 10%;" class="text-center">Ngày</th>
            </tr>
        </thead>
        <tbody>
            @foreach($record->prescriptions as $idx => $p)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>
                    <strong>{{ $p->medicine->name ?? 'Thuốc' }}</strong>
                    @if(!empty($p->medicine->unit))
                        <span style="font-size: 9px; color: #6b7280;">({{ $p->medicine->unit }})</span>
                    @endif
                </td>
                <td>{{ $p->dosage }}</td>
                <td class="text-center font-bold">{{ $p->quantity }}</td>
                <td class="text-center">{{ $p->days ? $p->days . ' ngày' : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Lời dặn của bác sĩ -->
    @if(!empty($record->note))
    <div class="section-title">4. Lời dặn dò của bác sĩ</div>
    <div class="clinical-box" style="font-style: italic; color: #4b5563;">
        {{ $record->note }}
    </div>
    @endif

    <!-- Chữ ký -->
    <table class="signature-table">
        <tr>
            <td style="width: 50%;">
                <strong>Khách hàng / Chủ nuôi</strong><br>
                <span style="font-size: 9px; color: #6b7280;">(Ký và ghi rõ họ tên)</span>
                <div class="signature-space"></div>
                <strong>{{ $record->pet->customer->name ?? '' }}</strong>
            </td>
            <td style="width: 50%;">
                <strong>Bác sĩ điều trị</strong><br>
                <span style="font-size: 9px; color: #6b7280;">(Ký và đóng dấu)</span>
                <div class="signature-space"></div>
                <strong>BS. {{ $record->doctor->name ?? '' }}</strong>
            </td>
        </tr>
    </table>

</body>
</html>
