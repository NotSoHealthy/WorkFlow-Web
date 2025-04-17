
async function generateQRCodeData(data) {
    return new Promise((resolve, reject) => {
        const container = document.createElement('div');
        container.style.display = 'none';
        document.body.appendChild(container);

        new QRCode(container, {
            text: data,
            width: 256,
            height: 256,
            correctLevel: QRCode.CorrectLevel.H
        });

        setTimeout(() => {
            const img = container.querySelector('img');
            if (img) {
                const dataURL = img.src;
                document.body.removeChild(container);
                resolve(dataURL);
            } else {
                // Fallback if a canvas was rendered instead
                const canvas = container.querySelector('canvas');
                if (canvas) {
                    const dataURL = canvas.toDataURL("image/png");
                    document.body.removeChild(container);
                    resolve(dataURL);
                } else {
                    document.body.removeChild(container);
                    reject("QR code not generated");
                }
            }
        }, 500);
    });
}

async function uploadBase64Image(base64String) {
    const apiKey = "73a46ef3fc1b634993a6addc9b377f0e";

    if (base64String.indexOf(',') !== -1) {
        base64String = base64String.split(',')[1];
    }

    const formData = new FormData();
    formData.append("image", base64String);

    try {
        const response = await fetch(`https://api.imgbb.com/1/upload?key=${apiKey}`, {
            method: "POST",
            body: formData,
        });
        const result = await response.json();
        if (result.success) {
            return result.data.url;
        } else {
            throw new Error("Upload failed: " + JSON.stringify(result));
        }
    } catch (error) {
        console.error("Error uploading image:", error);
        throw error;
    }
}
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById('reservation-form');
    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        try {
            const formData = new FormData(form);
            const dataObj = {};
            formData.forEach((value, key) => {
                dataObj[key] = value;
            });
            const filteredData = {};
            for (const key in dataObj) {
                if (!key.includes('_token')&&!key.includes('qr_url')) {
                    filteredData[key] = dataObj[key];
                }
            }
            let typeVal = filteredData["reservation[Type]"];
            let NBP = filteredData["reservation[NombreDePlaces]"];
            if(typeVal == "40"){
                typeVal = "Acces Normal";
            }else if(typeVal == "50"){
                typeVal = "Semi VIP";
            }
            else{
                typeVal = "VIP";
            }
            let reservation_Price = document.getElementById('reservation_Price');
            let reservation_event_title = document.getElementById('event_Title');
            const dataString = `Evenement=${reservation_event_title.value}\ntype=${typeVal}\nNombre De Place=${NBP}\nPrix=${reservation_Price.value}`;
            const qrDataURL = await generateQRCodeData(dataString);
            const uploadedQRUrl = await uploadBase64Image(qrDataURL);
            let hiddenInput = document.getElementById('reservation[qr_url]');
            if (!hiddenInput) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'reservation[qr_url]';
                hiddenInput.id = 'reservation[qr_url]';
                form.appendChild(hiddenInput);
            }
            hiddenInput.value = uploadedQRUrl;
            form.submit();
        } catch (error) {
            console.error("Error processing QR code:", error);
            form.submit();
        }
    });
});
