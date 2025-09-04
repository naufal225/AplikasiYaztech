<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reimbursement Request #RY{{ $reimbursement->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            line-height: 1.6;
            margin: 40px;
        }
        .header, .section {
            margin-bottom: 30px;
        }
        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .title {
            font-size: 24px;
            text-align: center;
            font-weight: bold;
        }
        .sub-title {
            font-size: 11px;
            margin-bottom: 12px;
        }
        .label {
            font-weight: bold;
            width: 160px;
            display: inline-block;
        }
        .value {
            display: inline-block;
        }
        .box {
            border: 1px solid #000;
            padding: 10px;
            margin-top: 4px;
            background-color: #f8f8f8;
        }
        .status-approved { color: green; }
        .status-rejected { color: red; }
        .status-pending { color: orange; }
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="title">PT YAZTECH ENGINEERING SOLUSINDO</div>
    </div>

    <div class="section">
        <div class="sub-title">Reimbursement Request #RY{{ $reimbursement->id }} | {{ \Carbon\Carbon::parse($reimbursement->created_at)->format('F d, Y \a\t H:i') }}</div>
        <h3>Employee Information</h3>
        <div><span class="label">Email:</span> <span class="value">{{ $reimbursement->employee->email }}</span></div>
        <div><span class="label">Name:</span> <span class="value">{{ $reimbursement->employee->name }}</span></div>
        <div><span class="label">Team Lead:</span> <span class="value">{{ $reimbursement->approver->name ?? 'N/A' }}</span></div>
        <div><span class="label">Divisi:</span> <span class="value">{{ $reimbursement->employee->division->name ?? 'N/A' }}</span></div>
    </div>

    <div class="section">
        <h3>Reimbursement Details</h3>
        <div class="grid-2">
            <div>
                <div><span class="label">Date of Expanse:</span></div>
                <div class="box">{{ \Carbon\Carbon::parse($reimbursement->date)->format('l, M d, Y') }}</div>
            </div>
            <div>
                <div><span class="label">Customer:</span></div>
                <div class="box">{{ $reimbursement->customer ?? 'N/A' }}</div>
            </div>
            <div>
                <div><span class="label">Total Amount:</span></div>
                <div class="box">
                    Rp {{ number_format($reimbursement->total ?? 0, 0, ',', '.') }}
                </div>
            </div>
            <div>
                <div><span class="label">Invoice:</span></div>
                <div class="box">
                    @php
                        $path = storage_path('app/public/' . $reimbursement->invoice_path);
                        $type = pathinfo($path, PATHINFO_EXTENSION);
                        $data = file_get_contents($path);
                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    @endphp
                    <img src="{{ $base64 }}" alt="Invoice" width="300">
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <h3>Approval Status</h3>
        <div class="grid-2">
            <div>
                <div><span class="label">Team Lead Status:</span></div>
                <div class="box status-{{ $reimbursement->status_1 }}">
                    @if($reimbursement->status_1 === 'pending')
                        Pending Review
                    @elseif($reimbursement->status_1 === 'approved')
                        Approved
                    @elseif($reimbursement->status_1 === 'rejected')
                        Rejected
                    @endif
                </div>
            </div>
            <div>
                <div><span class="label">Manager Status:</span></div>
                <div class="box status-{{ $reimbursement->status_2 }}">
                    @if($reimbursement->status_2 === 'pending')
                        Pending Review
                    @elseif($reimbursement->status_2 === 'approved')
                        Approved
                    @elseif($reimbursement->status_2 === 'rejected')
                        Rejected
                    @endif
                </div>
            </div>
            <div>
                <div><span class="label">Team Lead Note:</span></div>
                <div class="box">{{ $reimbursement->note_1 ?? '-' }}</div>
            </div>
            <div>
                <div><span class="label">Manager Note:</span></div>
                <div class="box">{{ $reimbursement->note_2 ?? '-' }}</div>
            </div>
        </div>
    </div>

</body>
</html>
