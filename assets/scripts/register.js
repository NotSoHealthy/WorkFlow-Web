const GEMINI_API_URL = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=";
const GEMINI_API_KEY = 'AIzaSyBbeQks7sz0LgHOG9cQ1ErLCzuXZ5PESw0';
const ENZOIC_API_URL = 'https://api.enzoic.com/v1/passwords';
const ENZOIC_API_KEY = 'ae1d9151ee8d49189f608d229a159441';
const ENZOIC_API_SECRET = 'P*yF5T+&J-!8m2SWJjR2ZKV3stHqhM+h';

let passwordInput = document.querySelector("input[type=password]");
let passwordLevelRow = document.getElementById("passwordLevelRow");
var typingTimer;
var doneTypingInterval = 1000;

passwordInput.addEventListener('keyup', () => {
    clearTimeout(typingTimer);
    if (passwordInput.value) {
        typingTimer = setTimeout(passwordCheck, doneTypingInterval);
    }
});

async function getPasswordLevel(password) {
    const url = `${GEMINI_API_URL}${GEMINI_API_KEY}`;

    const body = {
        contents: [
            {
                parts: [
                    {
                        text: `I am going to give you a password. Give this password a score between 1 and 10 based on how secure it is:\n${password}\nRemember, give a score between 1 and 10 as your only output.`
                    }
                ]
            }
        ],
        generationConfig: {
            response_mime_type: "application/json",
            response_schema: {
                type: "object",
                properties: {
                    number: { type: "integer" }
                },
                required: ["number"]
            }
        }
    };

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(body)
        });

        if (!response.ok) {
            console.error(`HTTP request failed with status: ${response.status}`);
            const errorText = await response.text();
            console.error(errorText);
            return 0;
        }

        const jsonResponse = await response.json();

        console.log('Raw API Response:', jsonResponse);

        const text = jsonResponse.candidates?.[0]?.content?.parts?.[0]?.text?.trim();

        if (!text) {
            console.error("No valid text found in response.");
            return 0;
        }

        const parsedNumberObject = JSON.parse(text);
        const number = parsedNumberObject.number;

        console.log("Password Score:", number);
        return number;

    } catch (error) {
        console.error("Error during API call:", error.message);
        return 0;
    }
}

async function passwordCheck() {
    var password = document.querySelector("input[type=password]").value;
    var passwordLevelSpans = document.querySelectorAll(".password-level");
    var passwordLevelText = document.getElementById("passwordLevelText");
    var passwordAlert = document.getElementById("passwordAlert");
    var passwordLevel = await getPasswordLevel(password);
    passwordLevelRow.style.display = "block";

    passwordLevelSpans.forEach(span => {
        span.style.opacity = "0";
    });

    for (let i = 0; i < passwordLevel; i++) {
        setTimeout(() => {
            passwordLevelSpans[i].style.opacity = "1";
        }, Math.min(i * 100, 100));
    }

    if (passwordLevel <= 3) {
        passwordLevelText.innerHTML = "Faible";
    } else if (passwordLevel <= 7) {
        passwordLevelText.innerHTML = "Moyen";
    } else if (passwordLevel <= 10) {
        passwordLevelText.innerHTML = "Fort";
    } else {
        passwordLevelText.innerHTML = "Inconnu";
    }

    var passwordCompromised = await isPasswordCompromised(password);
    if (passwordCompromised) {
        passwordAlert.style.display = "block";
    }
    else {
        passwordAlert.style.display = "none";
    }

}

async function isPasswordCompromised(password) {
    const partialSHA256 = (await encodeToSHA256(password)).substring(0, 10);

    const credentials = btoa(`${ENZOIC_API_KEY}:${ENZOIC_API_SECRET}`);

    const response = await fetch(ENZOIC_API_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': '*/*',
            'Authorization': `Basic ${credentials}`,
        },
        body: JSON.stringify({ partialSHA256 }),
    });

    if (response.status === 200) {
        console.log('Password is COMPROMISED.');
        return true;
    } else if (response.status === 404) {
        console.log('Password is SAFE.');
        return false;
    } else {
        console.error(`Error: Received unexpected response code: ${response.status}`);
        return false;
    }
}

async function encodeToSHA256(password) {
    const encoder = new TextEncoder();
    const data = encoder.encode(password);
    const hashBuffer = await crypto.subtle.digest('SHA-256', data);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    return hashHex;
}


// window.onload = function () {
//     var passwordLevelSpans = document.querySelectorAll(".password-level");
//     var t = .5;
//     for (var i = 0; i < passwordLevelSpans.length; i++) {
//         passwordLevelSpans[i].style.transition = "all " + t + "s ease-in-out";
//         t += .1;
//     }
// }