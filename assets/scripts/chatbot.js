function toggleChatbot() {
    const window = document.getElementById('chatbot-window');
    if (window.style.display === "none" || window.style.display === "") {
        window.style.display = "flex";
    } else {
        window.style.display = "none";
    }
}

function handleKeyPress(event) {
    if (event.key === "Enter") {
        sendMessage();
    }
}

function sendMessage() {
    const input = document.getElementById('chatbot-input');
    const message = input.value.trim();
    if (message === '') return;

    appendMessage('Vous', message);

    // Show typing animation
    appendTyping();

    fetch('{{ path('gemini_chat') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({message: message})
    })
    .then(response => response.json())
    .then(data => {
        removeTyping();
        appendMessage('Gemini', data.reply);
    })
    .catch(error => {
        removeTyping();
        appendMessage('Erreur', 'Impossible de contacter le serveur.');
    });

    input.value = '';
}

function appendMessage(sender, text) {
    const messages = document.getElementById('chatbot-messages');
    const messageElement = document.createElement('div');
    messageElement.innerHTML = `<strong>${sender}:</strong> ${text}`;
    messages.appendChild(messageElement);
    messages.scrollTop = messages.scrollHeight;
}

function appendTyping() {
    const messages = document.getElementById('chatbot-messages');
    const typingElement = document.createElement('div');
    typingElement.id = "typing";
    typingElement.innerHTML = `<em>Gemini est en train d'écrire...</em>`;
    messages.appendChild(typingElement);
    messages.scrollTop = messages.scrollHeight;
}

function removeTyping() {
    const typingElement = document.getElementById('typing');
    if (typingElement) {
        typingElement.remove();
    }
}