@extends('layout.app')

@section('content')
<h2 class="text-center mb-4">Scan Wajah - Shift ID: {{ $id_shift }}</h2>

<div class="d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div style="position: relative; width: 500px; height: 375px;">
        <video id="video" width="500" height="375" autoplay muted style="position: absolute;"></video>
        <canvas id="overlay" width="500" height="375" style="position: absolute;"></canvas>
    </div>
</div>

<p id="status" class="text-center mt-3 text-success fw-bold"></p>

<script src="{{ asset('faceapi/face-api.min.js') }}"></script>
<script>
    const aksi = "{{ $aksi }}";
    const shiftId = {{ $id_shift }};
    const video = document.getElementById('video');
    const canvas = document.getElementById('overlay');
    const status = document.getElementById('status');
    let alreadyDetected = false;

    Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri('/faceapi/models'),
        faceapi.nets.faceLandmark68Net.loadFromUri('/faceapi/models'),
        faceapi.nets.faceRecognitionNet.loadFromUri('/faceapi/models'),
        faceapi.nets.ssdMobilenetv1.loadFromUri('/faceapi/models') 
    ]).then(startVideo);

    function startVideo() {
        navigator.mediaDevices.getUserMedia({ video: {} })
            .then(stream => {
                video.srcObject = stream;
            })
            .catch(err => console.error("Gagal akses kamera", err));
    }

    video.addEventListener('play', async () => {
    const displaySize = { width: video.width, height: video.height };
    faceapi.matchDimensions(canvas, displaySize);

    // Ambil data wajah yang telah disimpan
    const labeledDescriptors = await loadLabeledImages();

    // ✅ Tambahkan pengecekan agar tidak error jika kosong
    if (!labeledDescriptors || labeledDescriptors.length === 0) {
        console.error("❌ Tidak ada data wajah terdaftar.");
        document.getElementById('status').innerText = "❌ Tidak ada data wajah. Daftarkan wajah dulu.";
        return; // Stop proses scanning
    }

    const faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.6);

    setInterval(async () => {
        const detections = await faceapi.detectAllFaces(
            video,
            new faceapi.TinyFaceDetectorOptions()
        ).withFaceLandmarks().withFaceDescriptors();

        const resized = faceapi.resizeResults(detections, displaySize);

        canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);

        resized.forEach(detection => {
            const match = faceMatcher.findBestMatch(detection.descriptor);
            const box = detection.detection.box;
            const drawBox = new faceapi.draw.DrawBox(box, { label: match.toString() });
            drawBox.draw(canvas);

            if (match.label !== "unknown" && !alreadyDetected) {
                alreadyDetected = true;
                status.innerText = 'Terdeteksi: ' + match.label;

                fetch(`/absensi/checkin`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        nama: match.label,
                        shift_id: shiftId,
                        aksi: aksi // ini penting
                    })
                })

                .then(res => res.json())
                .then(data => {
                    alert(data.msg);
                    location.reload();
                });
            }
        });
    }, 1000);
});


function loadLabeledImages() {
    const users = {!! json_encode(
        \App\Models\User::where(function($q) {
            $q->where('role', 'admin')
            ->orWhereNull('role');
        })->whereNotNull('image')->get(['nama','image'])
    ) !!};

    return Promise.all(
        users.map(async user => {
            if (!user.image) return null; // ⛔ skip jika tidak ada gambar
            const imgUrl = `/foto_karyawan/${user.image}`;
            try {
                const img = await faceapi.fetchImage(imgUrl);
                const detection = await faceapi
                    .detectSingleFace(img, new faceapi.TinyFaceDetectorOptions())
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (!detection) throw new Error('Wajah tidak terdeteksi di gambar ' + user.nama);

                return new faceapi.LabeledFaceDescriptors(user.nama, [detection.descriptor]);
            } catch (error) {
                console.warn('Gagal memuat wajah: ', user.nama);
                return null;
            }
        })
    ).then(data => data.filter(d => d !== null));
}

</script>
@endsection
