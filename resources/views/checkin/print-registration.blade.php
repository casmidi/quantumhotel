<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Print - {{ trim((string) ($registration->RegNo ?? '')) }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            color-scheme: light;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #eef3fb;
            color: #101828;
            font-family: Arial, "Segoe UI", sans-serif;
        }

        .print-shell {
            width: 880px;
            margin: 24px auto;
            padding: 28px 30px 36px;
            background: #fff;
            border: 1px solid #d8e0ef;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        }

        .hotel-header {
            display: grid;
            grid-template-columns: 88px 1fr 88px;
            gap: 16px;
            align-items: center;
            padding-bottom: 10px;
            border-bottom: 1px solid #1f2937;
        }

        .hotel-logo-box {
            width: 88px;
            height: 88px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .hotel-logo-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .hotel-logo-fallback {
            color: #173761;
            font-size: 0.72rem;
            font-weight: 700;
            line-height: 1.4;
            text-align: center;
        }

        .hotel-center {
            text-align: center;
        }

        .hotel-name {
            margin: 0;
            color: #1336a3;
            font-size: 2rem;
            font-weight: 700;
        }

        .hotel-line {
            margin-top: 4px;
            font-size: 0.94rem;
            line-height: 1.35;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.25fr 1fr;
            gap: 28px;
            padding: 16px 0 12px;
            border-bottom: 1px solid #1f2937;
        }

        .hero-title {
            margin: 0 0 10px;
            color: #102a83;
            font-size: 1.2rem;
            font-weight: 400;
            text-decoration: underline;
            text-underline-offset: 4px;
            text-align: right;
        }

        .qr-box {
            width: 132px;
            height: 132px;
            margin-left: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #111827;
            background: #fff;
            overflow: hidden;
        }

        .qr-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .barcode-caption {
            margin-top: 4px;
            font-size: 0.78rem;
            text-align: right;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table td {
            padding: 4px 0;
            font-size: 0.98rem;
            vertical-align: top;
        }

        .label-cell {
            width: 122px;
            white-space: nowrap;
        }

        .colon-cell {
            width: 14px;
            text-align: center;
        }

        .strong-value {
            font-weight: 700;
        }

        .section-divider {
            margin: 14px 0 10px;
            border-top: 1px solid #1f2937;
        }

        .address-grid {
            display: grid;
            gap: 2px;
        }

        .field-row {
            display: grid;
            grid-template-columns: 96px 16px 1fr 160px 16px 1fr;
            gap: 0;
            align-items: start;
            min-height: 24px;
            font-size: 0.97rem;
        }

        .field-row.compact {
            grid-template-columns: 112px 16px 1fr 112px 16px 1fr;
        }

        .field-row.single {
            grid-template-columns: 112px 16px 1fr;
        }

        .field-row .value {
            min-height: 20px;
            font-weight: 700;
        }

        .field-row .muted-value {
            font-weight: 400;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 42px;
            margin-top: 44px;
        }

        .signature-block {
            min-height: 118px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }

        .signature-line {
            padding-top: 8px;
            border-top: 1px solid #111827;
            font-weight: 700;
            text-align: center;
        }

        .print-actions {
            width: 880px;
            margin: 16px auto 24px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .print-actions button {
            border: none;
            border-radius: 999px;
            padding: 12px 20px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
        }

        .close-btn {
            background: #dbe7fb;
            color: #1336a3;
        }

        .print-btn {
            background: #173761;
            color: #fff;
        }

        @media print {
            body {
                background: #fff;
            }

        .print-shell {
            width: 100%;
            margin: 0;
            border: none;
            box-shadow: none;
            padding: 0;
        }

            .print-actions {
                display: none;
            }
        }
    </style>
</head>
<body>
    @php
        $checkInDate = !empty($registration->TglIn) ? \Carbon\Carbon::parse($registration->TglIn)->format('d-m-Y') : '';
        $checkOutDate = !empty($registration->TglKeluar) ? \Carbon\Carbon::parse($registration->TglKeluar)->format('d-m-Y') : '';
        $birthDate = !empty($registration->TglLahir) ? \Carbon\Carbon::parse($registration->TglLahir)->format('d-m-Y') : '';
        $timeIn = !empty($registration->JamIn) ? \Carbon\Carbon::parse($registration->JamIn)->format('H:i:s') : '';
        $rate = number_format((float) ($registration->Rate ?? 0), 0, ',', '.');
        $qrPayload = trim((string) ($registration->RegNo ?? ''));
        if (trim((string) ($registration->RegNo2 ?? '')) !== '' || trim((string) ($registration->Kode ?? '')) !== '') {
            $qrPayload = 'REGNO:' . trim((string) ($registration->RegNo ?? ''))
                . '|REGNO2:' . trim((string) ($registration->RegNo2 ?? ''))
                . '|ROOM:' . trim((string) ($registration->Kode ?? ''));
        }
        $qrCode = new \Endroid\QrCode\Builder\Builder(
            writer: new \Endroid\QrCode\Writer\SvgWriter(),
            writerOptions: [],
            validateResult: false,
            data: $qrPayload,
            encoding: new \Endroid\QrCode\Encoding\Encoding('UTF-8'),
            errorCorrectionLevel: \Endroid\QrCode\ErrorCorrectionLevel::High,
            size: 132,
            margin: 0,
            roundBlockSizeMode: \Endroid\QrCode\RoundBlockSizeMode::Margin,
        );
        $qrCodeUrl = $qrCode->build()->getDataUri();
    @endphp

    <div class="print-shell">
        <div class="hotel-header">
            <div class="hotel-logo-box">
                @if (!empty($hotelProfile['logo_url']))
                    <img src="{{ $hotelProfile['logo_url'] }}" alt="Hotel logo">
                @else
                    <div class="hotel-logo-fallback">HOTEL<br>LOGO</div>
                @endif
            </div>
            <div class="hotel-center">
                <h1 class="hotel-name">{{ $hotelProfile['name'] }}</h1>
                <div class="hotel-line">{{ $hotelProfile['address'] }}</div>
                <div class="hotel-line">Telp : {{ $hotelProfile['phone'] }}</div>
                <div class="hotel-line">Email: {{ $hotelProfile['email'] }} / Website: {{ $hotelProfile['website'] }}</div>
            </div>
            <div></div>
        </div>

        <div class="hero-grid">
            <div>
                <table class="data-table">
                    <tr>
                        <td class="label-cell">Registration</td>
                        <td class="colon-cell">:</td>
                        <td>{{ trim((string) ($registration->RegNo ?? '')) }}</td>
                    </tr>
                    <tr>
                        <td class="label-cell">Arrival Date</td>
                        <td class="colon-cell">:</td>
                        <td>{{ $checkInDate }}</td>
                    </tr>
                    <tr>
                        <td class="label-cell">Departure Date</td>
                        <td class="colon-cell">:</td>
                        <td>{{ $checkOutDate }}</td>
                    </tr>
                    <tr>
                        <td class="label-cell">Time In</td>
                        <td class="colon-cell">:</td>
                        <td>{{ $timeIn }}</td>
                    </tr>
                    <tr>
                        <td class="label-cell">Guest</td>
                        <td class="colon-cell">:</td>
                        <td class="strong-value">{{ trim((string) ($registration->Guest ?? '')) }}</td>
                    </tr>
                </table>
            </div>

            <div>
                <h2 class="hero-title">GUEST REGISTRATION</h2>
                <div class="qr-box">
                    <img src="{{ $qrCodeUrl }}" alt="QR registration {{ trim((string) ($registration->RegNo ?? '')) }}">
                </div>
                <div class="barcode-caption">{{ trim((string) ($registration->RegNo ?? '')) }}</div>

                <table class="data-table" style="margin-top: 10px;">
                    <tr>
                        <td class="label-cell">Room Number</td>
                        <td class="colon-cell">:</td>
                        <td>{{ trim((string) ($registration->Kode ?? '')) }}</td>
                    </tr>
                    <tr>
                        <td class="label-cell">Room Type</td>
                        <td class="colon-cell">:</td>
                        <td>{{ trim((string) ($registration->Kelas ?? '')) }}</td>
                    </tr>
                    <tr>
                        <td class="label-cell">Room Rate</td>
                        <td class="colon-cell">:</td>
                        <td>{{ $rate }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="section-divider"></div>

        <div class="address-grid">
            <div class="field-row single">
                <div>Address</div>
                <div>:</div>
                <div class="value">{{ trim((string) ($registration->Alamat ?? '')) }}</div>
            </div>
            <div class="field-row single">
                <div>Jln</div>
                <div>:</div>
                <div class="value muted-value">{{ trim((string) ($registration->Alamat ?? '')) }}</div>
            </div>
            <div class="field-row single">
                <div>Kelurahan</div>
                <div>:</div>
                <div class="value">{{ trim((string) ($registration->Kelurahan ?? '')) }}</div>
            </div>
            <div class="field-row single">
                <div>Kecamatan</div>
                <div>:</div>
                <div class="value">{{ trim((string) ($registration->Kecamatan ?? '')) }}</div>
            </div>
            <div class="field-row single">
                <div>Kabupaten</div>
                <div>:</div>
                <div class="value">{{ trim((string) ($registration->Kota ?? '')) }}</div>
            </div>
            <div class="field-row single">
                <div>Propinsi</div>
                <div>:</div>
                <div class="value">{{ trim((string) ($registration->Propinsi ?? '')) }}</div>
            </div>
        </div>

        <div style="height: 10px;"></div>

        <div class="field-row compact">
            <div>Company Name</div>
            <div>:</div>
            <div class="value">{{ trim((string) ($registration->Usaha ?? '')) }}</div>
            <div>Occupation</div>
            <div>:</div>
            <div class="value">{{ trim((string) ($registration->Profesi ?? '')) }}</div>
        </div>

        <div class="field-row single" style="margin-top: 8px;">
            <div>Purpose of Visit</div>
            <div>:</div>
            <div class="value">{{ trim((string) ($registration->Purpose ?? '')) }}</div>
        </div>

        <div style="height: 10px;"></div>

        <div class="field-row compact">
            <div>Passport No. / Ktp No.</div>
            <div>:</div>
            <div class="value">{{ trim((string) ($registration->Ktp ?? '')) }}</div>
            <div>Religion</div>
            <div>:</div>
            <div class="value">{{ trim((string) ($registration->Agama ?? '')) }}</div>
        </div>

        <div class="field-row compact">
            <div>Nationality</div>
            <div>:</div>
            <div class="value">{{ trim((string) ($registration->KodeNegara ?? '')) }}</div>
            <div>Place / Date of Birth</div>
            <div>:</div>
            <div class="value">
                {{ trim((string) ($registration->TempatLahir ?? '')) }}
                @if (trim((string) ($registration->TempatLahir ?? '')) !== '' && $birthDate !== '')
                    ,
                @endif
                {{ $birthDate }}
            </div>
        </div>

        <div style="height: 10px;"></div>

        <div class="field-row compact">
            <div>Payment Methode</div>
            <div>:</div>
            <div class="value">{{ trim((string) ($registration->Payment ?? '')) }}</div>
            <div>Phone</div>
            <div>:</div>
            <div class="value">{{ trim((string) ($registration->Phone ?? '')) }}</div>
        </div>

        <div class="field-row compact">
            <div>Credit Card</div>
            <div>:</div>
            <div class="value">{{ trim((string) ($registration->CreditCard ?? '')) }}</div>
            <div>Visa</div>
            <div>:</div>
            <div class="value">{{ trim((string) ($registration->Visa ?? '')) }}</div>
        </div>

        <div class="field-row single">
            <div>Remark</div>
            <div>:</div>
            <div class="value">{{ trim((string) ($registration->Remark ?? '')) }}</div>
        </div>

        <div class="field-row compact">
            <div>Guest 2</div>
            <div>:</div>
            <div class="value">{{ trim((string) ($registration->Guest2 ?? '')) }}</div>
            <div>Guest 3</div>
            <div>:</div>
            <div class="value">{{ trim((string) ($registration->Guest3 ?? '')) }}</div>
        </div>

        <div class="field-row compact">
            <div>Safe Deposit</div>
            <div>:</div>
            <div class="value">{{ trim((string) ($registration->SafeDeposit ?? '')) }}</div>
            <div>Petugas</div>
            <div>:</div>
            <div class="value">{{ $cashierName ?: trim((string) ($registration->UserIn ?? '')) }}</div>
        </div>

        <div class="signatures">
            <div class="signature-block">
                <div class="signature-line">({{ $cashierName ?: trim((string) ($registration->UserIn ?? '')) }})</div>
            </div>
            <div class="signature-block">
                <div class="signature-line">({{ $guestCaption }})</div>
            </div>
        </div>
    </div>

    <div class="print-actions">
        <button type="button" class="close-btn" onclick="window.close()">Close</button>
        <button type="button" class="print-btn" onclick="window.print()">Print</button>
    </div>
</body>
</html>
