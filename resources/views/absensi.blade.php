<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clockify Attendance</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>

<body>
    <h1>Absensi</h1>

    <button onclick="clockIn()">Clock In</button>
    <button onclick="clockOut()">Clock Out</button>

    <h2>Timer: <span id="timer">00:00:00</span></h2>
    <h2>Durasi Kerja: <span id="duration">-</span></h2>
    <h2>Response:</h2>
    <pre id="response"></pre>

    <script>
        let startTime = null;
        let timerInterval = null;

        function formatTime(seconds) {
            let hrs = Math.floor(seconds / 3600);
            let mins = Math.floor((seconds % 3600) / 60);
            let secs = seconds % 60;
            return `${String(hrs).padStart(2, '0')}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        }

        function startTimer() {
            if (!startTime) return;
            timerInterval = setInterval(() => {
                let elapsedSeconds = Math.floor((Date.now() - startTime) / 1000);
                document.getElementById('timer').innerText = formatTime(elapsedSeconds);
            }, 1000);
        }

        function stopTimer() {
            clearInterval(timerInterval);
        }

        function clockIn() {
            axios.post('/api/clock-in')
                .then(response => {
                    document.getElementById('response').innerText = JSON.stringify(response.data, null, 2);
                    startTime = Date.now();
                    startTimer();
                })
                .catch(error => {
                    document.getElementById('response').innerText = JSON.stringify(error.response.data, null, 2);
                });
        }

        function clockOut() {
            axios.put('/api/clock-out')
                .then(response => {
                    document.getElementById('response').innerText = JSON.stringify(response.data, null, 2);
                    stopTimer();
                    // Update durasi kerja di halaman
                    document.getElementById('duration').innerText = response.data.duration;
                })
                .catch(error => {
                    document.getElementById('response').innerText = JSON.stringify(error.response.data, null, 2);
                });
        }
    </script>

</body>

</html>