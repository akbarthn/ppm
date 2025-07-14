@extends('layout.app')

@section('content')
<h2 class="text-center mb-4">Scan Wajah - Shift ID: {{ $shiftId }}</h2>

<div class="d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div style="position: relative; width: 500px; height: 375px;">
        <video id="video" width="500" height="375" autoplay muted style="position: absolute;"></video>
        <canvas id="overlay" width="500" height="375" style="position: absolute;"></canvas>
    </div>
</div>

<p id="status" class="text-center mt-3 text-success fw-bold"></p>

<script src="{{ asset('faceapi/face-api.min.js') }}"></script>
<script>
    const shiftId = {{ $shiftId }};
    const video = document.getElementById('video');
    const canvas = document.getElementById('overlay');
    const status = document.getElementById('status');
    let alreadyDetected = false;

    Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri('/faceapi/models'),
        faceapi.nets.faceLandmark68Net.loadFromUri('/faceapi/models'),
        faceapi.nets.faceRecognitionNet.loadFromUri('/faceapi/models')
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

        const labeledDescriptors = await loadLabeledImages();
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
                            image: match.label,
                            shift_id: shiftId
                        })
                    }).then(res => res.json())
                      .then(data => {
                          alert(data.msg);
                          location.reload();
                      });
                }
            });
        }, 1000);
    });

    function loadLabeledImages() {
        const labels = {!! json_encode(
            \App\Models\User::where(function($q) {
                $q->where('role', 'admin')
                ->orWhereNull('role');
            })->whereNotNull('image')->pluck('image')->toArray()
        ) !!};



        return Promise.all(
            labels.map(async label => {
                const imgUrl = `/storage/foto_karyawan/${label}`;
                try {
                    const img = await faceapi.fetchImage(imgUrl);
                    const detection = await faceapi
                        .detectSingleFace(img)
                        .withFaceLandmarks()
                        .withFaceDescriptor();

                    if (!detection) throw new Error('Wajah tidak terdeteksi di gambar ' + label);

                    return new faceapi.LabeledFaceDescriptors(label, [detection.descriptor]);
                } catch (error) {
                    console.warn('Gagal memuat wajah: ', label);
                    return null;
                }
            })
        ).then(data => data.filter(d => d !== null));
    }
</script>
@endsection
