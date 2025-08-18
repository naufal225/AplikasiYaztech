<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Overtime Request #{{ $overtime->id }}</title>
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
        <div class="sub-title">Overtime Request #{{ $overtime->id }} | {{ \Carbon\Carbon::parse($overtime->created_at)->format('F d, Y \a\t H:i') }}</div>
        <h3>Employee Information</h3>
        <div><span class="label">Email:</span> <span class="value">{{ Auth::user()->email }}</span></div>
        <div><span class="label">Name:</span> <span class="value">{{ Auth::user()->name }}</span></div>
        <div><span class="label">Team Lead:</span> <span class="value">{{ $overtime->approver->name ?? 'N/A' }}</span></div>
        <div><span class="label">Divisi:</span> <span class="value">{{ $overtime->employee->division->name ?? 'N/A' }}</span></div>
    </div>

    <div class="section">
        <h3>Overtime Details</h3>
        <div class="grid-2">
            <div>
                <div><span class="label">Start Date:</span></div>
                <div class="box">{{ \Carbon\Carbon::parse($overtime->date_start)->format('l, M d, Y \a\t H:i') }}</div>
            </div>
            <div>
                <div><span class="label">End Date:</span></div>
                <div class="box">{{ \Carbon\Carbon::parse($overtime->date_end)->format('l, M d, Y \a\t H:i') }}</div>
            </div>
            <div>
                <div><span class="label">Duration:</span></div>
                <div class="box">
                    @php
                        $totalMinutes = $overtime->total;
                        $hours = floor($totalMinutes / 60);
                        $minutes = $totalMinutes % 60;
                    @endphp

                    {{ $hours }} jam {{ $minutes }} menit
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <h3>Approval Status</h3>
        <div class="grid-2">
            <div>
                <div><span class="label">Team Lead Status:</span></div>
                <div class="box status-{{ $overtime->status_1 }}">
                    @if($overtime->status_1 === 'pending')
                        Pending Review
                    @elseif($overtime->status_1 === 'approved')
                        Approved
                    @elseif($overtime->status_1 === 'rejected')
                        Rejected
                    @endif
                </div>
            </div>
            <div>
                <div><span class="label">Manager Status:</span></div>
                <div class="box status-{{ $overtime->status_2 }}">
                    @if($overtime->status_2 === 'pending')
                        Pending Review
                    @elseif($overtime->status_2 === 'approved')
                        Approved
                    @elseif($overtime->status_2 === 'rejected')
                        Rejected
                    @endif
                </div>
            </div>
            <div>
                <div><span class="label">Team Lead Note:</span></div>
                <div class="box">{{ $overtime->note_1 ?? '-' }}</div>
            </div>
            <div>
                <div><span class="label">Manager Note:</span></div>
                <div class="box">{{ $overtime->note_2 ?? '-' }}</div>
            </div>
        </div>
    </div>

</body>
</html>
