// Chart.js Configuration
Chart.defaults.font.family = "Inter, system-ui, sans-serif";
Chart.defaults.color = "#6B7280";

// Color scheme based on the design system
const colors = {
    primary: "#2563EB", // Blue
    secondary: "#0EA5E9", // Sky Blue
    accent: "#10B981", // Green
    warning: "#F59E0B", // Amber
    error: "#EF4444", // Red
    neutral: "#6B7280", // Gray
    light: "#F3F4F6", // Light Gray
};

// 1. Monthly Requests Comparison Chart (Bar Chart)
const monthlyRequestsCtx = document
    .getElementById("monthlyRequestsChart")
    .getContext("2d");
new Chart(monthlyRequestsCtx, {
    type: "bar",
    data: {
        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
        datasets: [
            {
                label: "Leave Requests",
                data: [12, 19, 15, 25, 22, 18],
                backgroundColor: colors.primary,
                borderRadius: 6,
            },
            {
                label: "Reimbursement",
                data: [8, 15, 12, 18, 16, 14],
                backgroundColor: colors.secondary,
                borderRadius: 6,
            },
            {
                label: "Overtime",
                data: [6, 10, 8, 12, 9, 11],
                backgroundColor: colors.accent,
                borderRadius: 6,
            },
            {
                label: "Official Travel",
                data: [3, 5, 4, 7, 6, 5],
                backgroundColor: colors.warning,
                borderRadius: 6,
            },
        ],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: "bottom",
                labels: {
                    usePointStyle: true,
                    padding: 20,
                },
            },
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: "#F3F4F6",
                },
            },
            x: {
                grid: {
                    display: false,
                },
            },
        },
    },
});

// 2. Request Status Distribution (Doughnut Chart)
const statusDistributionCtx = document
    .getElementById("statusDistributionChart")
    .getContext("2d");
new Chart(statusDistributionCtx, {
    type: "doughnut",
    data: {
        labels: ["Approved", "Pending", "Rejected"],
        datasets: [
            {
                data: [65, 25, 10],
                backgroundColor: [colors.accent, colors.warning, colors.error],
                borderWidth: 0,
                cutout: "60%",
            },
        ],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: "bottom",
                labels: {
                    usePointStyle: true,
                    padding: 20,
                },
            },
        },
    },
});

// 3. Reimbursement Trend (Line Chart)
const reimbursementTrendCtx = document
    .getElementById("reimbursementTrendChart")
    .getContext("2d");
new Chart(reimbursementTrendCtx, {
    type: "line",
    data: {
        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
        datasets: [
            {
                label: "Amount (Million IDR)",
                data: [45, 52, 48, 61, 58, 67],
                borderColor: colors.secondary,
                backgroundColor: colors.secondary + "20",
                fill: true,
                tension: 0.4,
                pointBackgroundColor: colors.secondary,
                pointBorderColor: "#fff",
                pointBorderWidth: 2,
                pointRadius: 6,
            },
        ],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false,
            },
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: "#F3F4F6",
                },
            },
            x: {
                grid: {
                    display: false,
                },
            },
        },
    },
});

// 4. Leave Types Breakdown (Pie Chart)
const leaveTypesCtx = document
    .getElementById("leaveTypesChart")
    .getContext("2d");
new Chart(leaveTypesCtx, {
    type: "pie",
    data: {
        labels: [
            "Annual Leave",
            "Sick Leave",
            "Personal Leave",
            "Maternity Leave",
        ],
        datasets: [
            {
                data: [40, 25, 20, 15],
                backgroundColor: [
                    colors.primary,
                    colors.accent,
                    colors.warning,
                    colors.secondary,
                ],
                borderWidth: 0,
            },
        ],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: "bottom",
                labels: {
                    usePointStyle: true,
                    padding: 20,
                },
            },
        },
    },
});

// 5. Overtime Hours by Department (Horizontal Bar Chart)
const overtimeCtx = document.getElementById("overtimeChart").getContext("2d");
new Chart(overtimeCtx, {
    type: "bar",
    data: {
        labels: ["IT", "Finance", "HR", "Marketing", "Operations"],
        datasets: [
            {
                label: "Hours",
                data: [120, 85, 45, 95, 110],
                backgroundColor: colors.accent,
                borderRadius: 6,
            },
        ],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: "y",
        plugins: {
            legend: {
                display: false,
            },
        },
        scales: {
            x: {
                beginAtZero: true,
                grid: {
                    color: "#F3F4F6",
                },
            },
            y: {
                grid: {
                    display: false,
                },
            },
        },
    },
});
