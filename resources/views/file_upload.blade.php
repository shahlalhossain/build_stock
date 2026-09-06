<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" >
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        .upload-card {
            border: 1px dashed #bbb;
            border-radius: 8px;
            padding: 8px;
            text-align: center;
            transition: 0.2s;
            background: #fafafa;
            cursor: pointer;
        }

        .upload-card,
        .upload-card * {
            cursor: pointer !important;
        }

        .upload-card:hover {
            border-color: #777;
        }

        .upload-card img.placeholder {
            opacity: 0.6;
            width: 180px;
        }

        .preview-box img {
            max-height: 180px;
            object-fit: contain;
        }

        .delete-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #dc3545;
            color: #fff;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .delete-btn:hover {
            background: #bb2d3b;
        }
    </style>

</head>
<body>

<div class="container-fluid py-4 px-xxl-5">
    <div class="row g-4">

        <div class="col-sm-9"></div>
        <div class="col-sm-3">
            <div class="row">
                <!-- PHOTO -->
                <div class="col-12 mb-3">
                    <div class="upload-card" onclick="document.getElementById('photoInput').click()">

                        <!-- Placeholder -->
                        <div id="photoPlaceholder">
                            <img src="{{ asset('assets/images/users/user_avatar.png') }}" class="placeholder mb-2">
                            <div class="text-muted small">Click to Select Photo</div>
                        </div>

                        <!-- Preview -->
                        <div id="photoPreview" class="preview-box position-relative d-none">
                            <button class="delete-btn" onclick="deleteImage(event,'photo')">
                                <i class="bi bi-x-lg"></i>
                            </button>

                            <img id="photoImg" class="img-fluid mb-2" alt="Photo">
                            <div id="photoInfo" class="small text-muted"></div>
                        </div>

                        <input type="file" id="photoInput" class="d-none" accept="image/*" onchange="loadImage(event,'photo')">
                    </div>
                </div>
                <!-- SIGNATURE -->
                <div class="col-12">
                    <div class="upload-card" onclick="document.getElementById('sigInput').click()">

                        <!-- Placeholder -->
                        <div id="sigPlaceholder">
                            <img src="{{ asset('assets/images/users/signature.png') }}" class="placeholder mb-2">
                            <div class="text-muted small">Click to Select Signature</div>
                        </div>

                        <!-- Preview -->
                        <div id="sigPreview" class="preview-box position-relative d-none">
                            <button class="delete-btn" onclick="deleteImage(event,'sig')">
                                <i class="bi bi-x-lg"></i>
                            </button>

                            <img id="sigImg" class="img-fluid mb-2" alt="Signature">
                            <div id="sigInfo" class="small text-muted"></div>
                        </div>

                        <input type="file" id="sigInput" class="d-none" accept="image/*" onchange="loadImage(event,'sig')">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Load image into preview
    function loadImage(evt, type) {
        const file = evt.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(type + "Img").src = e.target.result;

            // show/hide sections
            document.getElementById(type + "Preview").classList.remove("d-none");
            document.getElementById(type + "Placeholder").classList.add("d-none");

            // file name + size
            const sizeKB = (file.size / 1024).toFixed(2);
            document.getElementById(type + "Info").innerHTML =
                `${file.name}<br>(${sizeKB} KB)`;
        };

        reader.readAsDataURL(file);
    }

    // Delete image
    function deleteImage(event, type) {
        event.stopPropagation();
        document.getElementById(type + "Preview").classList.add("d-none");
        document.getElementById(type + "Placeholder").classList.remove("d-none");
        document.getElementById(type + "Input").value = "";
    }
</script>

</body>
</html>