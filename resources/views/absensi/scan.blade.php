@extends('layout.app')

@section('content')
<h2 class="text-center mb-4">
    Scan Wajah Absensi
</h2>

<div class="d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div style="position: relative; width: 500px; height: 375px;">
        <video id="video" width="500" height="375" autoplay muted style="position: absolute;"></video>
        <canvas id="overlay" width="500" height="375" style="position: absolute;"></canvas>
    </div>
</div>

<p id="status" class="text-center mt-3 text-primary fw-bold">Mencari wajah...</p>

{{-- CDN SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('faceapi/face-api.min.js') }}"></script>

<script>
    const video = document.getElementById('video');
    const canvas = document.getElementById('overlay');
    const statusText = document.getElementById('status');
    let alreadyProcessed = false;
    // --- PERBAIKAN: Deklarasikan processingFace di sini ---
    let processingFace = false; // Ini adalah baris yang hilang

    Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri('/faceapi/models'),
        faceapi.nets.faceLandmark68Net.loadFromUri('/faceapi/models'),
        faceapi.nets.faceRecognitionNet.loadFromUri('/faceapi/models'),
        faceapi.nets.ssdMobilenetv1.loadFromUri('/faceapi/models')
    ]).then(startVideo);

    function startVideo() {
        navigator.mediaDevices.getUserMedia({
                video: {}
            })
            .then(stream => {
                video.srcObject = stream;
            })
            .catch(err => {
                statusText.innerText = "❌ Kamera tidak bisa diakses.";
                statusText.className = "text-center mt-3 text-danger fw-bold";
                Swal.fire({
                    icon: 'error',
                    title: 'Akses Kamera Gagal',
                    text: 'Pastikan Anda memberikan izin akses kamera.'
                });
            });
    }

    video.addEventListener('play', async () => {
        const displaySize = {
            width: video.width,
            height: video.height
        };
        faceapi.matchDimensions(canvas, displaySize);

        const labeledDescriptors = await loadLabeledImages();
        if (!labeledDescriptors.length) {
            statusText.innerText = "❌ Tidak ada data wajah terdaftar.";
            statusText.className = "text-center mt-3 text-danger fw-bold";
            Swal.fire({
                icon: 'warning',
                title: 'Data Wajah Kosong',
                text: 'Tidak ada data wajah karyawan yang terdaftar untuk pencocokan.'
            });
            return;
        }

        const faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.6);

        setInterval(async () => {
            if (processingFace) return;

            const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks().withFaceDescriptors();
            const resized = faceapi.resizeResults(detections, displaySize);
            canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);

            if (resized.length > 0) {
                const bestMatch = faceMatcher.findBestMatch(resized[0].descriptor);
                const box = resized[0].detection.box;
                const drawBox = new faceapi.draw.DrawBox(box, {
                    label: bestMatch.toString()
                });
                drawBox.draw(canvas);

                if (bestMatch.label !== "unknown") {
    processingFace = true;
    statusText.innerText = `🔍 Wajah terdeteksi: ${bestMatch.label}`;
    statusText.className = "text-center mt-3 text-info fw-bold";

    try {
        // Ambil data user berdasarkan label (misal: ID disimpan di label, atau mapping manual)
        // Misalnya label: "3 - Akbar", kita ekstrak ID-nya:
        const labelParts = bestMatch.label.split(" - "); // pastikan format label-nya sesuai
        const userId = labelParts[0];

        const response = await fetch('/absensi/check-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ id: userId }) // kirim ID, bukan nama
        });

        const result = await response.json();

        if (result.status) {
            Swal.fire({
                title: 'Konfirmasi Absensi',
                html: `Nama: <b>${result.data.nama}</b><br>
                       Shift: <b>${result.data.shift_name} (${result.data.shift_start} - ${result.data.shift_end})</b><br>
                       Aksi: <b>${result.data.aksi === 'checkin' ? 'Check In' : 'Check Out'}</b><br>
                       Keterangan: <b>${result.data.keterangan}</b>`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Ya, Lanjutkan',
                cancelButtonText: 'Tidak, Batalkan',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(async (confirmResult) => {
                if (confirmResult.isConfirmed) {
                    const endpoint = result.data.aksi === 'checkin' ? '/absensi/checkin' : '/absensi/checkout';
                    const absensiResponse = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            id: result.data.id,
                            shift_id: result.data.shift_id
                        })
                    });

                    const absensiResult = await absensiResponse.json();

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: absensiResult.status ? 'success' : 'error',
                        title: absensiResult.msg,
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });

                    if (absensiResult.status) {
                        statusText.className = "text-center mt-3 text-success fw-bold";
                        setTimeout(() => window.location.href = '/absensi', 2000);
                    } else {
                        statusText.className = "text-center mt-3 text-danger fw-bold";
                        processingFace = false;
                    }
                } else {
                    statusText.innerText = "Absensi dibatalkan. Mencari wajah kembali...";
                    statusText.className = "text-center mt-3 text-primary fw-bold";
                    processingFace = false;
                }
            });
        } else {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'warning',
                title: result.msg,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            statusText.innerText = result.msg;
            statusText.className = "text-center mt-3 text-warning fw-bold";
            processingFace = false;
        }

    } catch (error) {
        console.error('Gagal komunikasi dengan server:', error);
        statusText.innerText = "⚠️ Gagal berkomunikasi dengan server.";
        statusText.className = "text-center mt-3 text-danger fw-bold";
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: 'Terjadi kesalahan jaringan.',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
        processingFace = false;
    }
}else {
                    statusText.innerText = "❌ Wajah tidak dikenali.";
                    statusText.className = "text-center mt-3 text-warning fw-bold";
                    processingFace = false;
                }
            } else {
                statusText.innerText = "🔎 Mencari wajah...";
                statusText.className = "text-center mt-3 text-primary fw-bold";
                processingFace = false;
            }
        }, 1000);// Interval deteksi wajah
    });
    async function loadLabeledImages() {
        const users = @json(\App\Models\User::whereNotNull('image')->get(['nama', 'image']));

        console.log("Users fetched:", users);

        const descriptors = [];

        for (const user of users) {
            const imgUrl = `/foto_karyawan/${user.image}`;
            try {
                const img = await faceapi.fetchImage(imgUrl);
                const detection = await faceapi
                    .detectSingleFace(img, new faceapi.TinyFaceDetectorOptions())
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (detection) {
                    descriptors.push(new faceapi.LabeledFaceDescriptors(user.nama, [detection.descriptor]));
                } else {
                    console.warn(`Wajah tidak terdeteksi pada gambar ${user.nama}`);
                }
            } catch (e) {
                console.warn(`Gagal memuat gambar untuk ${user.nama}: ${e}`);
            }
        }
        return descriptors;
    }
</script>
@endsection