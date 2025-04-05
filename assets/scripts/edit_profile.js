function previewImage(event) {
    const input = event.target;
    const preview = document.getElementById('preview');

    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function (e) {
            preview.src = e.target.result; // Set the image source to the file's data URL
        };

        reader.readAsDataURL(input.files[0]); // Read the file as a data URL
    }
}

async function uploadImage(event) {

    if (!document.getElementById('image').value === "") {
        return;
    }

    const file = document.getElementById('image').files[0];
    const apiKey = "73a46ef3fc1b634993a6addc9b377f0e";

    // Convert image file to Base64
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = async function () {
        const base64String = reader.result.split(',')[1]; // Remove the Data URL prefix

        const formData = new FormData();
        formData.append("image", base64String); // ImgBB requires base64 string

        startLoader()
        try {
            const response = await fetch(`https://api.imgbb.com/1/upload?key=${apiKey}`, {
                method: "POST",
                body: formData,
            });

            const result = await response.json();
            if (result.success) {
                console.log("Image URL:", result.data.url);
                document.getElementById('preview').src = result.data.url;

                // Store the image URL in a hidden input field
                let hiddenInput = document.getElementById('image-url');
                if (!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'image_url';
                    hiddenInput.id = 'image-url';
                    document.getElementById('edit-profile-form').appendChild(hiddenInput);
                }
                hiddenInput.value = result.data.url;
            } else {
                console.error("Upload failed:", result);
            }
        } catch (error) {
            console.error("Error uploading image:", error);
        }
        finally {
            stopLoader()
        }
    };

    reader.onerror = function (error) {
        console.error("Error reading file:", error);
    };
}

function enable2fa(){
    const button = document.getElementById("auth-btn");
    const qrCodeImage = document.getElementById("qrcode");
    startLoader(); // Start the loader
    fetch(button.dataset.enableurl, {
        method: "POST",
        headers: {
            "X-Requested-With": "XMLHttpRequest",
            "Content-Type": "application/json"
        }
    })
    .then(response => response.text()) // Change from .json() to .text()
    .then(text => {
        console.log("Raw Response:", text); // Debugging
        return JSON.parse(text); // Manually parse JSON
    })
    .then(data => {
        if (data.qrCodeUrl) {
            let  qrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(data.qrCodeUrl)}`;
            qrCodeImage.src = qrCodeUrl;
            qrCodeImage.style.display = "block";
            button.innerHTML = "Désactiver";
            button.setAttribute("onclick", "disable2fa()");
        } else {
            alert("Erreur lors de l'activation du 2FA.");
        }
    })
    .catch(error => console.error("Erreur:", error))
    .finally(() => {
        stopLoader(); // Stop the loader
        console.log("stopLoader called"); // Debugging
    });
    
}

function disable2fa(){
    const qrCodeImage = document.getElementById("qrcode");
    const button = document.getElementById("auth-btn");

    qrCodeImage.style.display = "none";
    qrCodeImage.src = "";
    button.innerHTML = "Activer";
    button.setAttribute("onclick", "enable2fa()");

    fetch(button.dataset.disableurl, {
        method: "POST",
        headers: {
            "X-Requested-With": "XMLHttpRequest",
            "Content-Type": "application/json"
        }
    })
    .catch(error => console.error("Erreur:", error));
}

window.onload = function () {
    const qrCodeImage = document.getElementById("qrcode");
    const button = document.getElementById("auth-btn");
    var secret = qrCodeImage.dataset.secret;
    if (secret) {
        qrCodeImage.src = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(secret)}`;
        qrCodeImage.style.display = "block";
        button.innerHTML = "Désactiver";
        button.setAttribute("onclick", "disable2fa()");
    }
}
