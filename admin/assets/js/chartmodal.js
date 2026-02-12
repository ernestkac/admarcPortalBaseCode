
        const slides = document.querySelectorAll('.chart-slide');
        const chartTitle = document.getElementById('chartTitle');
        const chartDescription = document.getElementById('chartDescription');
        let currentSlide = 0;

        const chartInfo = [
            {
            title: "Usage Chart",
            description: "This chart shows the top users for a selected month."
            },
            {
            title: "Monthly Chart",
            description: "The chart shows usage by month—noticeable spikes appear in January 2025, when employees were anticipating salary increment, and in May 2025, when the increment were actually implemented."
            },
            {
            title: "Monthly Chart by Names",
            description: "This chart shows monthly usage trends bt Name for a selected year."
            },
            {
            title: "Daily Chart",
            description: "This chart shows the daily login count for the selected month."
            }
        ];

        function showSlide(index) {
            slides.forEach((slide, i) => {
            slide.classList.toggle('active', i === index);
            });
            chartTitle.textContent = chartInfo[index].title;
            chartDescription.textContent = chartInfo[index].description;
            currentSlide = index;
        }

        document.getElementById('nextChart').addEventListener('click', () => {
            showSlide((currentSlide + 1) % slides.length);
        });

        document.getElementById('prevChart').addEventListener('click', () => {
            showSlide((currentSlide - 1 + slides.length) % slides.length);
        });

        // Initialize
        showSlide(0);

        //script for chart
        
        const chartMonthPicker = document.getElementById('chartMonthPicker');
        const yearPicker = document.getElementById('yearPicker');
        const chartDaysPicker = document.getElementById('chartdaysPicker');

        const usageCtx = document.getElementById('usageChart').getContext('2d');
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        const dailyCtx = document.getElementById('dailyChart').getContext('2d');

        let usageChart, monthlyChart, dailyChart;

        async function fetchUsageByEmployee(month) {
            const res = await fetch(`namechart.php?month=${month}`);
            return await res.json();
        }

        async function fetchUsageByMonth(year) {
            const res = await fetch(`monthchart.php?year=${year}`);
            return await res.json();
        }

        async function fetchUsageByDay(month) {
            const res = await fetch(`daychart.php?month=${month}`);
            return await res.json();
        }

        function createUsageChart(data) {
            const labels = data.map(row => row.name);
            const values = data.map(() => 0); // animate from 0

            if (usageChart) usageChart.destroy();

            usageChart = new Chart(usageCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Logins',
                        data: values,
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    animation: {
                        duration: 1000,
                        easing: 'easeOutCubic'
                    },
                    scales: {
                        x: { beginAtZero: true },
                        y: { title: { display: true, text: 'Employee' } }
                    }
                }
            });

            // Animate to actual values
            setTimeout(() => {
                usageChart.data.datasets[0].data = data.map(row => row.usage_count);
                usageChart.update();
            }, 100);
        }

        function createMonthlyChart(data) {
            const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

            const counts = new Array(12).fill(0);
            data.forEach(row => {
                counts[row.month - 1] = row.usage_count;
            });

            if (monthlyChart) monthlyChart.destroy();

            monthlyChart = new Chart(monthlyCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Monthly Usage',
                        data: counts,
                        backgroundColor: 'rgba(153, 102, 255, 0.6)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    onClick: (e, elements) => {
                        if (elements.length > 0) {
                            const index = elements[0].index;
                            const year = yearPicker.value;
                            const month = String(index + 1).padStart(2, '0');
                            const monthStr = `${year}-${month}`;

                            chartDaysPicker.value = monthStr;
                            fetchUsageByDay(monthStr).then(data => {
                                createDailyChart(data, monthStr);
                                showSlide(3);
                            });
                        }
                    },
                    responsive: true,
                    animation: {
                        duration: 1000,
                        easing: 'easeOutCubic'
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Login Count'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Month'
                            }
                        }
                    }
                }
            });
        }
        function getDaysInMonth(ym) {
            if (!/^\d{4}-\d{2}$/.test(ym)) {
                throw new Error("Invalid format. Use YYYY-MM");
            }
            const [y, m] = ym.split('-').map(Number);
            return new Date(y, m, 0).getDate();
        }

        function createDailyChart(data, month) {
            console.log("herehre"+month);
            const daysInMonth = getDaysInMonth( month);
            console.log("herehre"+daysInMonth);
            const labels = Array.from({length: daysInMonth}, (_, i) => i + 1);
            const counts = new Array(daysInMonth).fill(0);

            data.forEach(row => {
                if (row.day >= 1 && row.day <= daysInMonth) {
                    counts[row.day - 1] = row.usage_count;
                }
            });

            if (dailyChart) dailyChart.destroy();

            dailyChart = new Chart(dailyCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Daily Logins',
                        data: counts,
                        borderColor: 'rgba(255, 159, 64, 1)',
                        backgroundColor: 'rgba(255, 159, 64, 0.2)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    animation: {
                        duration: 1000,
                        easing: 'easeOutCubic'
                    },
                    scales: {
                        y: { beginAtZero: true, title: { display: true, text: 'Login Count' } },
                        x: { title: { display: true, text: 'Day' } }
                    }
                }
            });
        }

        async function updateCharts() {
            const month = chartMonthPicker.value;
            const monthDayChart = chartDaysPicker.value;
            const year = yearPicker.value;

            const usageData = await fetchUsageByEmployee(month);
            const monthlyData = await fetchUsageByMonth(year);
            const groupedData = await fetchGroupedMonthlyUsage(year);
            const dailyData = await fetchUsageByDay(monthDayChart);

            createUsageChart(usageData);
            createMonthlyChart(monthlyData);
            createGroupedChart(groupedData);
            createDailyChart(dailyData, monthDayChart);
        }


        // Initial Load
        updateCharts();

        // Event Listeners
        chartMonthPicker.addEventListener('change', updateCharts);
        chartDaysPicker.addEventListener('change', updateCharts);
        yearPicker.addEventListener('change', updateCharts);

        //chart 3 monthly usage grouped by name
            const groupedCtx = document.getElementById('groupedMonthlyChart').getContext('2d');
            let groupedChart;

            async function fetchGroupedMonthlyUsage(year) {
                const res = await fetch(`monthbynamechart.php?year=${year}`);
                return await res.json();
            }

            function getRandomColor() {
                const r = () => Math.floor(Math.random() * 256);
                return `rgba(${r()}, ${r()}, ${r()}, 0.6)`;
            }

            function createGroupedChart(rawData) {
                const monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                                    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

                const users = [...new Set(rawData.map(row => row.name))];
                const datasets = [];

                users.forEach(user => {
                    const userData = new Array(12).fill(0);
                    rawData
                        .filter(row => row.name === user)
                        .forEach(row => {
                            userData[row.month - 1] = row.count;
                        });

                    datasets.push({
                        label: user,
                        data: userData,
                        backgroundColor: getRandomColor()
                    });
                });

                if (groupedChart) groupedChart.destroy();

                groupedChart = new Chart(groupedCtx, {
                    type: 'bar',
                    data: {
                        labels: monthLabels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        animation: {
                            duration: 1000,
                            easing: 'easeOutCubic'
                        },
                        plugins: {
                            tooltip: { mode: 'index', intersect: false },
                            legend: { display: true }
                        },
                        scales: {
                            x: { stacked: false },
                            y: {
                                beginAtZero: true,
                                title: { display: true, text: 'Login Count' }
                            }
                        }
                    }
                });
            }
        //end of chart 3 monthly usage grouped by name
