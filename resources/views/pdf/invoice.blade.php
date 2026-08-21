<!DOCTYPE html>
<html lang="vi">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Hoa_Don_{{ $invoice->invoice_number }}</title>
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
            font-size: 16px;
            font-weight: bold;
            color: #111827;
            text-align: center;
            text-transform: uppercase;
            margin: 10px 0 5px 0;
        }

        .doc-sub {
            text-align: center;
            font-size: 11px;
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
            padding: 6px 8px;
            text-align: left;
        }

        .data-table td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
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

        .total-box {
            width: 50%;
            margin-left: auto;
            margin-top: 10px;
            border-collapse: collapse;
        }

        .total-box td {
            padding: 4px 8px;
            font-size: 11px;
        }

        .total-highlight {
            font-size: 13px;
            font-weight: bold;
            color: #4f46e5;
            border-top: 1px solid #e5e7eb;
            padding-top: 6px !important;
        }

        .signature-table {
            width: 100%;
            margin-top: 30px;
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

        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: bold;
        }

        .status-paid {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-unpaid {
            background-color: #fef3c7;
            color: #92400e;
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
                <strong>Số HĐ:</strong> {{ $invoice->invoice_number }}<br>
                <strong>Ngày lập:</strong> {{ $invoice->created_at->format('d/m/Y H:i') }}<br>
                <strong>Trạng thái:</strong>
                @if($invoice->status === 'paid')
                <span class="status-paid">ĐÃ THANH TOÁN</span>
                @else
                <span class="status-unpaid">CHỜ THANH TOÁN</span>
                @endif
            </td>
        </tr>
    </table>

    <div class="doc-title">HÓA ĐƠN THANH TOÁN DỊCH VỤ</div>
    <div class="doc-sub">MÃ HÓA ĐƠN: {{ $invoice->invoice_number }}</div>

    <!-- Thông tin khách hàng & Bệnh nhân -->
    <table class="info-box">
        <tr>
            <td style="width: 50%;">
                <strong>Khách hàng:</strong> {{ $invoice->customer->name ?? '—' }}<br>
                <strong>Số điện thoại:</strong> {{ $invoice->customer->phone ?? '—' }}<br>
                <strong>Địa chỉ:</strong> {{ $invoice->customer->address ?? '—' }}
            </td>
            <td style="width: 50%;">
                <strong>Bệnh nhân (Vật nuôi):</strong> {{ $invoice->medicalRecord->pet->name ?? '—' }} ({{ ucfirst($invoice->medicalRecord->pet->species ?? '') }})<br>
                <strong>Phiếu khám gốc:</strong> #KB-{{ str_pad($invoice->medical_record_id, 4, '0', STR_PAD_LEFT) }}<br>
                <strong>Bác sĩ khám:</strong> BS. {{ $invoice->medicalRecord->doctor->name ?? '—' }}
            </td>
        </tr>
    </table>

    <div class="section-title">Chi tiết các khoản thanh toán</div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;" class="text-center">STT</th>
                <th style="width: 52%;">Hạng mục / Dịch vụ / Thuốc</th>
                <th style="width: 10%;" class="text-center">SL</th>
                <th style="width: 15%;" class="text-right">Đơn giá</th>
                <th style="width: 15%;" class="text-right">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            @php $stt = 1; @endphp

            <!-- Phí khám -->
            @if((float)$invoice->subtotal_exam > 0)
            <tr>
                <td class="text-center">{{ $stt++ }}</td>
                <td><strong>Phí khám & Tư vấn tổng quát</strong></td>
                <td class="text-center">1</td>
                <td class="text-right">{{ number_format($invoice->subtotal_exam, 0, ',', '.') }} đ</td>
                <td class="text-right font-bold">{{ number_format($invoice->subtotal_exam, 0, ',', '.') }} đ</td>
            </tr>
            @endif

            <!-- Dịch vụ khác -->
            @if($invoice->medicalRecord && $invoice->medicalRecord->services)
            @foreach($invoice->medicalRecord->services as $service)
            @if(!str_contains(strtolower($service->name), 'khám tổng quát'))
            <tr>
                <td class="text-center">{{ $stt++ }}</td>
                <td>{{ $service->name }}</td>
                <td class="text-center">1</td>
                <td class="text-right">{{ number_format($service->pivot->price ?? $service->price, 0, ',', '.') }} đ</td>
                <td class="text-right font-bold">{{ number_format($service->pivot->price ?? $service->price, 0, ',', '.') }} đ</td>
            </tr>
            @endif
            @endforeach
            @endif

            <!-- Đơn thuốc -->
            @if($invoice->medicalRecord && $invoice->medicalRecord->prescriptions)
            @foreach($invoice->medicalRecord->prescriptions as $p)
            <tr>
                <td class="text-center">{{ $stt++ }}</td>
                <td>
                    {{ $p->medicine->name ?? 'Thuốc' }}
                    @if(!empty($p->medicine->unit))
                    <span style="font-size: 9px; color: #6b7280;">({{ $p->medicine->unit }})</span>
                    @endif
                    <br><span style="font-size: 9px; color: #6b7280;">Liều dùng: {{ $p->dosage }}</span>
                </td>
                <td class="text-center">{{ $p->quantity }}</td>
                <td class="text-right">{{ number_format($p->medicine->sell_price ?? 0, 0, ',', '.') }} đ</td>
                <td class="text-right font-bold">{{ number_format(($p->medicine->sell_price ?? 0) * $p->quantity, 0, ',', '.') }} đ</td>
            </tr>
            @endforeach
            @endif
        </tbody>
    </table>

    <!-- Tổng kết tiền -->
    <table class="total-box">
        <tr>
            <td class="text-left" style="width: 50%;">Tiền khám:</td>
            <td class="text-right">{{ number_format($invoice->subtotal_exam, 0, ',', '.') }} đ</td>
        </tr>
        <tr>
            <td>Tiền dịch vụ:</td>
            <td class="text-right">{{ number_format($invoice->subtotal_service, 0, ',', '.') }} đ</td>
        </tr>
        <tr>
            <td>Tiền thuốc:</td>
            <td class="text-right">{{ number_format($invoice->subtotal_medicine, 0, ',', '.') }} đ</td>
        </tr>
        <tr class="total-highlight">
            <td><strong>TỔNG CỘNG:</strong></td>
            <td class="text-right"><strong>{{ number_format($invoice->total_amount, 0, ',', '.') }} đ</strong></td>
        </tr>
        @if($invoice->paid_at)
        <tr>
            <td colspan="2" class="text-right" style="font-size: 10px; color: #065f46; font-style: italic;">
                Đã thanh toán ngày {{ $invoice->paid_at->format('d/m/Y H:i') }}
            </td>
        </tr>
        @endif
    </table>

    <!-- Chữ ký -->
    <table class="signature-table">
        <tr>
            <td style="width: 50%;">
                <strong>Khách hàng</strong><br>
                <span style="font-size: 9px; color: #6b7280;">(Ký và ghi rõ họ tên)</span>
                <div class="signature-space"></div>
                <strong>{{ $invoice->customer->name ?? '' }}</strong>
            </td>
            <td style="width: 50%;">
                <strong>Thu ngân / Người lập</strong><br>
                <span style="font-size: 9px; color: #6b7280;">(Ký và đóng dấu)</span>
                <div class="signature-space"></div>
                <strong>{{ $invoice->creator->name ?? 'Thu ngân VetCare' }}</strong>
            </td>
        </tr>
    </table>

</body>

</html>