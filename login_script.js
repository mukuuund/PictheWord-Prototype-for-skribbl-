function createRoom() {
    const username = document.getElementById('username').value;

    if (!username) {
        alert('Please enter a username');
        return;
    }

    sessionStorage.setItem('username', username); // Store username in sessionStorage

    fetch('create_room.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `username=${encodeURIComponent(username)}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.href = `select_players.html?roomCode=${data.roomCode}&sessionId=${data.sessionId}`;
            } else {
                alert(data.message || 'Error creating room');
            }
        })
        .catch(error => console.error('Error creating room:', error));
}


function enterRoom() {
    document.getElementById('joinRoomSection').style.display = 'block';
}

function submitRoomCode() {
    const username = document.getElementById('username').value;
    const roomCode = document.getElementById('roomCode').value;

    if (!username || !roomCode) {
        alert('Please enter both username and room code');
        return;
    }

    fetch('join_room.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `username=${encodeURIComponent(username)}&roomCode=${encodeURIComponent(roomCode)}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.href = `loading.html?sessionId=${data.sessionId}`;
            } else {
                alert(data.message || 'Error joining room');
            }
        })
        .catch(error => console.error('Error joining room:', error));
}
