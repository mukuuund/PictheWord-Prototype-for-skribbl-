// WebSocket connection
const socket = new WebSocket('ws://localhost:8080/game');

// Fetch session ID and username
const sessionId = sessionStorage.getItem('sessionId') || prompt('Enter session ID:');
const username = sessionStorage.getItem('username') || prompt('Enter your username:') || 'Anonymous';

// Store session ID and username for future use
sessionStorage.setItem('sessionId', sessionId);
sessionStorage.setItem('username', username);

// Notify server of session ID and username when connection opens
socket.onopen = () => {
    console.log('Connected to WebSocket server');
    socket.send(JSON.stringify({ type: 'setUsername', username: username, sessionId: sessionId }));
};

// Handle incoming WebSocket messages
socket.onmessage = (event) => {
    const data = JSON.parse(event.data);

    switch (data.type) {
        case 'draw':
            drawOnCanvas(data.x, data.y, data.prevX, data.prevY);
            break;

        case 'clear':
            clearCanvas();
            break;

        case 'chat':
            displayChatMessage(data.username, data.message);
            break;

        case 'endGame':
            displayEndGameMessage(data.winner, data.word);
            break;
    }
};

// Canvas setup
const canvas = document.getElementById('gameCanvas');
const ctx = canvas.getContext('2d');
let drawing = false;
let prevX = 0;
let prevY = 0;

// Mouse event listeners for drawing
canvas.addEventListener('mousedown', (event) => {
    drawing = true;
    const rect = canvas.getBoundingClientRect();
    prevX = event.clientX - rect.left;
    prevY = event.clientY - rect.top;
});

canvas.addEventListener('mouseup', () => {
    drawing = false;
});

canvas.addEventListener('mousemove', (event) => {
    if (!drawing) return;

    const rect = canvas.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top;

    // Draw locally
    drawOnCanvas(x, y, prevX, prevY);

    // Send drawing data to WebSocket server
    socket.send(JSON.stringify({ type: 'draw', x, y, prevX, prevY }));

    prevX = x;
    prevY = y;
});

// Function to draw on the canvas
function drawOnCanvas(x, y, prevX, prevY) {
    ctx.lineWidth = 5;
    ctx.lineCap = 'round';
    ctx.strokeStyle = 'black';

    ctx.beginPath();
    ctx.moveTo(prevX, prevY);
    ctx.lineTo(x, y);
    ctx.stroke();
}

// Clear canvas functionality
document.getElementById('clearCanvas').addEventListener('click', () => {
    clearCanvas();

    // Notify others to clear their canvas
    socket.send(JSON.stringify({ type: 'clear' }));
});

function clearCanvas() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
}

// Chat functionality
const chatBox = document.getElementById('chatBox');
const chatMessage = document.getElementById('chatMessage');
const sendMessageButton = document.getElementById('sendMessage');

// Send chat messages
sendMessageButton.addEventListener('click', () => {
    const message = chatMessage.value.trim();
    if (!message) return;

    socket.send(JSON.stringify({ type: 'chat', sessionId: sessionId, message: message }));
    chatMessage.value = '';
});

// Display incoming chat messages
function displayChatMessage(username, message) {
    const newMessage = document.createElement('div');
    newMessage.textContent = `${username}: ${message}`;
    chatBox.appendChild(newMessage);
    chatBox.scrollTop = chatBox.scrollHeight; // Auto-scroll to the latest message
}

// Display end game message
function displayEndGameMessage(winner, word) {
    const message = `Game Over! Winner: ${winner}. The word was "${word}".`;
    const endGameDiv = document.createElement('div');
    endGameDiv.textContent = message;
    endGameDiv.style.fontWeight = 'bold';
    endGameDiv.style.color = 'green';
    chatBox.appendChild(endGameDiv);
    chatBox.scrollTop = chatBox.scrollHeight;
}
