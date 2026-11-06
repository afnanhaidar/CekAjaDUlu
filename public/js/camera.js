(() => {
    const form = document.getElementById('photo-upload-form');

    if (form === null) {
        return;
    }

    const activateButton = document.getElementById('activate-camera');
    const captureButton = document.getElementById('capture-photo');
    const retakeButton = document.getElementById('retake-photo');
    const uploadButton = document.getElementById('upload-photo');
    const video = document.getElementById('camera-preview');
    const canvas = document.getElementById('photo-canvas');
    const image = document.getElementById('photo-preview');
    const fileInput = document.getElementById('surat');
    const message = document.getElementById('camera-message');

    if (
        !(activateButton instanceof HTMLButtonElement)
        || !(captureButton instanceof HTMLButtonElement)
        || !(retakeButton instanceof HTMLButtonElement)
        || !(uploadButton instanceof HTMLButtonElement)
        || !(video instanceof HTMLVideoElement)
        || !(canvas instanceof HTMLCanvasElement)
        || !(image instanceof HTMLImageElement)
        || !(fileInput instanceof HTMLInputElement)
        || !(message instanceof HTMLElement)
    ) {
        return;
    }

    let stream = null;
    let previewUrl = null;

    const stopCamera = () => {
        if (stream !== null) {
            stream.getTracks().forEach((track) => track.stop());
            stream = null;
        }

        video.pause();
        video.srcObject = null;
        video.hidden = true;
        captureButton.disabled = true;
    };

    const clearPreview = () => {
        if (previewUrl !== null) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }

        image.removeAttribute('src');
        image.hidden = true;
    };

    const showMessage = (text) => {
        message.textContent = text;
        message.classList.remove('d-none');
    };

    const hideMessage = () => {
        message.textContent = '';
        message.classList.add('d-none');
    };

    const showFallback = () => {
        stopCamera();
        showMessage('Kamera tidak bisa diakses. Pilih foto dari perangkat.');
    };

    const displayPreview = (file) => {
        clearPreview();
        previewUrl = URL.createObjectURL(file);
        image.src = previewUrl;
        image.hidden = false;
    };

    const setCapturedFile = (file) => {
        try {
            const transfer = new DataTransfer();
            transfer.items.add(file);
            fileInput.files = transfer.files;
        } catch (error) {
            showMessage('Foto dari kamera tidak dapat diproses. Pilih foto dari perangkat.');

            return false;
        }

        return true;
    };

    const startCamera = async () => {
        clearPreview();
        fileInput.value = '';
        uploadButton.disabled = true;
        retakeButton.classList.add('d-none');
        hideMessage();

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            showFallback();

            return;
        }

        stopCamera();

        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: {
                        ideal: 'environment',
                    },
                },
                audio: false,
            });
            video.srcObject = stream;
            video.hidden = false;
            await video.play();
            captureButton.disabled = false;
        } catch (error) {
            showFallback();
        }
    };

    const capturePhoto = () => {
        if (stream === null || video.videoWidth === 0 || video.videoHeight === 0) {
            showMessage('Kamera belum siap. Coba aktifkan kembali kamera.');

            return;
        }

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const context = canvas.getContext('2d');

        if (context === null) {
            showMessage('Foto tidak dapat diproses. Coba ulangi.');

            return;
        }

        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        canvas.toBlob((blob) => {
            if (blob === null) {
                showMessage('Foto tidak dapat diproses. Coba ulangi.');

                return;
            }

            const photo = new File([blob], `surat-${Date.now()}.jpg`, {
                type: 'image/jpeg',
            });

            if (!setCapturedFile(photo)) {
                return;
            }

            displayPreview(photo);
            stopCamera();
            uploadButton.disabled = false;
            retakeButton.classList.remove('d-none');
        }, 'image/jpeg', 0.9);
    };

    activateButton.addEventListener('click', startCamera);
    captureButton.addEventListener('click', capturePhoto);
    retakeButton.addEventListener('click', startCamera);
    fileInput.addEventListener('change', () => {
        const [file] = fileInput.files;

        if (file === undefined) {
            return;
        }

        stopCamera();
        displayPreview(file);
        uploadButton.disabled = false;
        retakeButton.classList.remove('d-none');
    });
    window.addEventListener('pagehide', () => {
        stopCamera();
        clearPreview();
    });
})();
