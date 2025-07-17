@extends('layout.app')
@section('content')

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Face Recognition dengan Laravel & face-api.js</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- face-api.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.js"></script>

    <!-- CSRF untuk request Laravel -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        .relative-container {
            position: relative;
            width: 720px;
            height: 560px;
        }
        canvas {
            position: absolute;
            top: 0;
            left: 0;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen font-sans">

    <div class="bg-white p-8 rounded-2xl shadow-xl text-center">
        <h1 class="text-3xl font-bold mb-4 text-gray-800">Pengenalan Wajah Real-Time</h1>
        <p id="loadingMessage" class="text-gray-600 mb-4">Sedang memuat model, mohon tunggu...</p>

        <!-- Video dan Canvas -->
        <div class="relative-container mx-auto border-4 border-gray-300 rounded-lg overflow-hidden mb-4">
            <video id="video" width="720" height="560" autoplay muted playsinline></video>
        </div>

        <!-- Kontrol Pendaftaran -->
        <div id="controls" class="mt-4" style="display: none;">
            <div class="flex justify-center items-center space-x-4">
                <input type="text" id="nameInput" placeholder="Masukkan Nama Anda" class="border p-2 rounded-md w-64">
                <button id="registerButton" class="bg-blue-500 text-white p-2 rounded-md hover:bg-blue-600">Daftarkan Wajah</button>
            </div>
            <p id="registerMessage" class="text-green-600 mt-2 h-6"></p>
        </div>
    </div>

    <script>
        const video = document.getElementById('video');
        const loadingMessage = document.getElementById('loadingMessage');
        const controls = document.getElementById('controls');
        const nameInput = document.getElementById('nameInput');
        const registerButton = document.getElementById('registerButton');
        const registerMessage = document.getElementById('registerMessage');
        const MODEL_URL = 'faceapi/models'; // folder model di public/model

        let labeledFaceDescriptors = [];
        let faceMatcher = null;
        let isProcessing = false;

        // Memuat model & membuka kamera
        const runFaceRecognition = async () => {
            try {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    loadingMessage.innerText = "Browser Anda tidak mendukung akses kamera.";
                    return;
                }

                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                    faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                    faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
                ]);

                loadingMessage.innerText = "Model berhasil dimuat, mengakses kamera...";

                const stream = await navigator.mediaDevices.getUserMedia({ video: {} });
                video.srcObject = stream;

            } catch (error) {
                console.error("Gagal memuat model atau kamera:", error);
                loadingMessage.innerText = `Error: ${error.message}`;
            }
        };

        // Tombol pendaftaran wajah
        registerButton.addEventListener('click', async () => {
            const name = nameInput.value.trim();
            if (name === "") {
                registerMessage.innerText = "Silakan masukkan nama.";
                registerMessage.className = "text-red-600 mt-2 h-6";
                return;
            }

            registerMessage.innerText = "Mendeteksi wajah...";
            registerMessage.className = "text-blue-600 mt-2 h-6";

            try {
                const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                                               .withFaceLandmarks()
                                               .withFaceDescriptor();

                if (detection) {
                    // ✅ Perbaikan di sini
                    const descriptor = new faceapi.LabeledFaceDescriptors(name, [detection.descriptor]);
                    labeledFaceDescriptors.push(descriptor);
                    if (labeledFaceDescriptors.length>0) {
                        faceMatcher = new faceapi.FaceMatcher(labeledFaceDescriptors, 0.6);
                    } else {
                        faceMatcher = null;
                    }
                    // faceMatcher = new faceapi.FaceMatcher(labeledFaceDescriptors, 0.6);

                    // (Opsional) Simpan ke backend Laravel
                    await fetch('/api/register-face', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            name: name,
                            descriptor: Array.from(detection.descriptor)
                        })
                    });

                    registerMessage.innerText = `Wajah ${name} berhasil didaftarkan!`;
                    registerMessage.className = "text-green-600 mt-2 h-6";
                    nameInput.value = "";
                    console.log("Wajah terdaftar:", labeledFaceDescriptors);
                } else {
                    registerMessage.innerText = "Tidak ada wajah terdeteksi. Coba lagi.";
                    registerMessage.className = "text-red-600 mt-2 h-6";
                }
            } catch (error) {
                console.error("Error saat pendaftaran:", error);
                registerMessage.innerText = "Terjadi error saat pendaftaran.";
                registerMessage.className = "text-red-600 mt-2 h-6";
            }
        });

        // Saat video mulai diputar
        video.addEventListener('play', () => {
            loadingMessage.style.display = 'none';
            controls.style.display = 'block';

            const canvas = faceapi.createCanvasFromMedia(video);
            document.querySelector('.relative-container').append(canvas);

            const displaySize = { width: video.width, height: video.height };
            faceapi.matchDimensions(canvas, displaySize);

            setInterval(async () => {
                if (!faceMatcher || isProcessing) return;
                isProcessing = true;

                const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
                                               .withFaceLandmarks()
                                               .withFaceDescriptors();

                const resizedDetections = faceapi.resizeResults(detections, displaySize);
                canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);

                const results = resizedDetections.map(d => faceMatcher.findBestMatch(d.descriptor));

                results.forEach((result, i) => {
                    const box = resizedDetections[i].detection.box;
                    const drawBox = new faceapi.draw.DrawBox(box, { label: result.toString() });
                    drawBox.draw(canvas);
                });

                isProcessing = false;
            }, 100);
        });

        // Jalankan awal
        const init = async () => {
            loadingMessage.innerText = "Memuat model...";
            await runFaceRecognition();
        };
        init();
    </script>
</body>
</html>
@endsection