{{-- Image Cropper Component --}}
<div class="mb-3">

    @php
    $hasRealImage = isset($currentImage) && $currentImage && !str_contains($currentImage, 'default-logo-placeholder.svg');
    $displayImage = $currentImage ?? asset('build/images/others/placeholder.jpg');
    $statusBadge = $hasRealImage ? 'bg-success' : 'bg-secondary';
    $statusText = $hasRealImage ? 'Ready to save' : 'No image uploaded';
    $imageType = $hasRealImage ? 'Uploaded Image' : 'Default Placeholder';
    $logoClass = isset($logoClass) ? $logoClass : 'bg-white';
    @endphp

    {{-- Current Image Display (always shown) --}}
    <div id="existingImageDisplay_{{ $inputId ?? 'avatar' }}" class="mb-3">
        <div class="d-flex align-items-center p-3 border rounded bg-light">
            <img src="{{ $displayImage }}" alt="Current {{ $label ?? 'Image' }}" class="img-thumbnail me-3 {{ $logoClass }}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
            <div class="flex-grow-1">
                <p class="mb-1"><strong>Status:</strong> <span class="badge {{ $statusBadge }}">{{ $statusText }}</span></p>
                <p class="mb-2"><strong>Type:</strong> <span>{{ $imageType }}</span></p>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-primary d-flex align-items-center" id="changeExisting_{{ $inputId ?? 'avatar' }}">
                        <i data-lucide="edit" class="me-1 icon-sm"></i> {{ $hasRealImage ? 'Change' : 'Upload' }} {{ $label ?? 'Image' }}
                    </button>
                    @if($hasRealImage)
                    <button type="button" class="btn btn-sm btn-outline-danger d-flex align-items-center" id="removeExisting_{{ $inputId ?? 'avatar' }}">
                        <i data-lucide="trash-2" class="me-1 icon-sm"></i> Remove
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden file input (shown when changing/uploading image) --}}
    <div id="fileInputContainer_{{ $inputId ?? 'avatar' }}" style="display: none;">
        <input type="file" class="form-control @error($inputId ?? 'avatar') is-invalid @enderror"
            id="{{ $inputId ?? 'avatar' }}_upload" accept="image/*">
        @error($inputId ?? 'avatar')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="form-text text-muted">Allowed formats: JPEG, PNG, JPG, GIF. Max size: {{ $maxUploadSize ?? 10 }}MB</small>
    </div>

    {{-- Hidden inputs for form submission --}}
    <input type="hidden" id="{{ $inputId ?? 'avatar' }}_cropped" name="{{ $inputId ?? 'avatar' }}_cropped">
    <input type="hidden" id="{{ $inputId ?? 'avatar' }}_remove" name="{{ $inputId ?? 'avatar' }}_remove" value="0">
</div>

{{-- Enhanced Cropper Modal --}}
<div class="modal fade" id="cropperModal_{{ $inputId ?? 'avatar' }}" tabindex="-1" aria-labelledby="cropperModalLabel_{{ $inputId ?? 'avatar' }}" aria-hidden="false">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center" id="cropperModalLabel_{{ $inputId ?? 'avatar' }}">
                    <i data-lucide="crop" class="icon-sm me-2"></i>
                    Crop {{ $label ?? 'Image' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    {{-- Main Cropper Area --}}
                    <div class="col-lg-8 border-end">
                        <div class="p-3">
                            <div class="cropper-container bg-light rounded border" style="height: 350px;">
                                <img id="cropperImage_{{ $inputId ?? 'avatar' }}" src="#" alt="Cropper Image" style="max-width: 100%; max-height: 100%;">
                            </div>

                            {{-- Cropper Controls --}}
                            <div class="mt-3">
                                <div class="d-flex justify-content-center gap-2 flex-wrap">
                                    <div class="btn-group" role="group" aria-label="Rotation controls">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="cropperRotateLeft_{{ $inputId ?? 'avatar' }}" title="Rotate Left">
                                            <i data-lucide="rotate-ccw" class="icon-sm"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="cropperRotateRight_{{ $inputId ?? 'avatar' }}" title="Rotate Right">
                                            <i data-lucide="rotate-cw" class="icon-sm"></i>
                                        </button>
                                    </div>

                                    <div class="btn-group" role="group" aria-label="Flip controls">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="cropperFlipHorizontal_{{ $inputId ?? 'avatar' }}" title="Flip Horizontal">
                                            <i data-lucide="flip-horizontal" class="icon-sm"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="cropperFlipVertical_{{ $inputId ?? 'avatar' }}" title="Flip Vertical">
                                            <i data-lucide="flip-vertical" class="icon-sm"></i>
                                        </button>
                                    </div>

                                    <div class="btn-group" role="group" aria-label="Zoom controls">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="cropperZoomIn_{{ $inputId ?? 'avatar' }}" title="Zoom In">
                                            <i data-lucide="zoom-in" class="icon-sm"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="cropperZoomOut_{{ $inputId ?? 'avatar' }}" title="Zoom Out">
                                            <i data-lucide="zoom-out" class="icon-sm"></i>
                                        </button>
                                    </div>

                                    <button type="button" class="btn btn-outline-warning btn-sm" id="cropperReset_{{ $inputId ?? 'avatar' }}" title="Reset">
                                        <i data-lucide="refresh-cw" class="icon-sm me-1"></i>Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Settings & Preview Panel --}}
                    <div class="col-lg-4">
                        <div class="p-3">
                            {{-- Preview Section --}}
                            <div class="mb-4">
                                <h6 class="text-secondary mb-3 d-flex align-items-center">
                                    <i data-lucide="eye" class="icon-sm me-2"></i>Preview
                                </h6>
                                <div class="preview-container text-center">
                                    <img id="croppedPreview_{{ $inputId ?? 'avatar' }}" src="#" alt="Preview"
                                        class="img-thumbnail border-2"
                                        style="width: 150px; height: 150px; object-fit: cover; background: #f8f9fa;">
                                </div>
                                <div class="text-center mt-2">
                                    <small class="text-muted" id="previewDimensions_{{ $inputId ?? 'avatar' }}">150 × 150 px</small>
                                </div>
                            </div>

                            {{-- Aspect Ratio Section --}}
                            <div class="mb-4">
                                <label class="form-label d-flex align-items-center">
                                    <i data-lucide="square" class="icon-sm me-2"></i>Aspect Ratio
                                </label>
                                <select class="form-select form-select-sm" id="aspectRatio_{{ $inputId ?? 'avatar' }}">
                                    <option value="free">Free (No constraint)</option>
                                    <option value="1">Square (1:1)</option>
                                    <option value="1.33">4:3 (Landscape)</option>
                                    <option value="0.75">3:4 (Portrait)</option>
                                    <option value="1.78">16:9 (Widescreen)</option>
                                    <option value="0.56">9:16 (Mobile)</option>
                                </select>
                            </div>

                            {{-- Output Size Section --}}
                            <div class="mb-4">
                                <label class="form-label d-flex align-items-center">
                                    <i data-lucide="maximize" class="icon-sm me-2"></i>Output Size
                                </label>
                                <select class="form-select form-select-sm" id="cropSize_{{ $inputId ?? 'avatar' }}">
                                    <option value="original">Use Original Size</option>
                                    <option value="150">150×150 (Thumbnail)</option>
                                    <option value="300" selected>300×300 (Small)</option>
                                    <option value="500">500×500 (Medium)</option>
                                    <option value="800">800×800 (Large)</option>
                                    <option value="1200">1200×1200 (Extra Large)</option>
                                    <option value="custom">Custom Size</option>
                                </select>

                                {{-- Custom Size Inputs --}}
                                <div id="customSizeInputs_{{ $inputId ?? 'avatar' }}" class="mt-2" style="display: none;">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <input type="number" class="form-control form-control-sm"
                                                id="customWidth_{{ $inputId ?? 'avatar' }}"
                                                placeholder="Width" min="50" max="2000" value="300">
                                        </div>
                                        <div class="col-6">
                                            <input type="number" class="form-control form-control-sm"
                                                id="customHeight_{{ $inputId ?? 'avatar' }}"
                                                placeholder="Height" min="50" max="2000" value="300">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Quality Section --}}
                            <div class="mb-4">
                                <label class="form-label d-flex align-items-center">
                                    <i data-lucide="settings" class="icon-sm me-2"></i>
                                    Quality: <span id="qualityValue_{{ $inputId ?? 'avatar' }}" class="ms-auto">90%</span>
                                </label>
                                <input type="range" class="form-range" id="imageQuality_{{ $inputId ?? 'avatar' }}"
                                    min="10" max="100" value="90" step="5">
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">Low</small>
                                    <small class="text-muted">High</small>
                                </div>
                            </div>

                            {{-- Format Section --}}
                            <div class="mb-4">
                                <label class="form-label d-flex align-items-center">
                                    <i data-lucide="file-image" class="icon-sm me-2"></i>Output Format
                                </label>
                                <select class="form-select form-select-sm" id="imageFormat_{{ $inputId ?? 'avatar' }}">
                                    <option value="default">Default (Keep original format)</option>
                                    <option value="png">PNG (Best quality, larger file)</option>
                                    <option value="jpeg">JPEG (Good quality, smaller file)</option>
                                    <option value="webp">WebP (Modern format, smallest file)</option>
                                </select>
                            </div>

                            {{-- Image Information Section --}}
                            <div class="mb-4">
                                <h6 class="text-secondary mb-3 d-flex align-items-center">
                                    <i data-lucide="info" class="icon-sm me-2"></i>Image Information
                                </h6>

                                {{-- Original Image Info --}}
                                <div class="card card-body bg-light mb-2 p-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted fw-semibold">Original:</small>
                                        <small id="originalInfo_{{ $inputId ?? 'avatar' }}" class="text-dark">Select image</small>
                                    </div>
                                </div>

                                {{-- Crop Info --}}
                                <div class="card card-body bg-primary bg-opacity-10 p-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-primary fw-semibold">Output:</small>
                                        <small id="cropInfo_{{ $inputId ?? 'avatar' }}" class="text-primary">Ready to crop</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Quick Actions --}}
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-info btn-sm" id="skipCrop_{{ $inputId ?? 'avatar' }}">
                                    <i data-lucide="skip-forward" class="icon-sm me-1"></i>Use Original (No Crop)
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="autoFit_{{ $inputId ?? 'avatar' }}">
                                    <i data-lucide="maximize-2" class="icon-sm me-1"></i>Auto Fit Image
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i data-lucide="x" class="icon-sm me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="cropAndSave_{{ $inputId ?? 'avatar' }}">
                    <i data-lucide="check" class="icon-sm me-1"></i>Apply & Save
                </button>
            </div>
        </div>
    </div>
</div>



{{-- Display cropped image after processing --}}
<div id="croppedImageDisplay_{{ $inputId ?? 'avatar' }}" class="my-3" style="display: none;">
    <div class="card">
        <div class="card-body">
            <h6 class="card-title">Selected Image Preview</h6>
            <div class="d-flex align-items-center">
                <img id="finalCroppedImage_{{ $inputId ?? 'avatar' }}" src="#" alt="Cropped Image" class="img-thumbnail me-3" style="width: 100px; height: 100px; object-fit: cover;">
                <div>
                    <p class="mb-1"><strong>Status:</strong> <span id="cropStatus_{{ $inputId ?? 'avatar' }}" class="badge bg-success">Ready to save</span></p>
                    <p class="mb-1"><strong>Type:</strong> <span id="imageType_{{ $inputId ?? 'avatar' }}">Cropped</span></p>
                    <button type="button" class="btn btn-sm btn-outline-primary d-flex align-items-center" id="changeCrop_{{ $inputId ?? 'avatar' }}">
                        <i data-lucide="edit" class="icon-sm me-1"></i> Change Image
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('plugin-styles')
<link href="{{ asset('build/plugins/cropperjs/cropper.min.css') }}" rel="stylesheet" />
@endpush

@push('plugin-scripts')
<script src="{{ asset('build/plugins/cropperjs/cropper.min.js') }}"></script>
@endpush

@php
// Determine default image URL based on input ID
$defaultImageUrl = asset('images/others/logo-placeholder.png');
$inputIdValue = $inputId ?? 'avatar';

if (strpos($inputIdValue, 'logo') !== false) {
if (strpos($inputIdValue, 'admin') !== false) {
if (strpos($inputIdValue, 'small') !== false) {
if (strpos($inputIdValue, 'dark') !== false) {
$defaultImageUrl = logo_url('admin', 'small', 'dark');
} else {
$defaultImageUrl = logo_url('admin', 'small', 'light');
}
} else {
if (strpos($inputIdValue, 'dark') !== false) {
$defaultImageUrl = logo_url('admin', 'large', 'dark');
} else {
$defaultImageUrl = logo_url('admin', 'large', 'light');
}
}
} elseif (strpos($inputIdValue, 'frontend') !== false) {
if (strpos($inputIdValue, 'small') !== false) {
if (strpos($inputIdValue, 'dark') !== false) {
$defaultImageUrl = logo_url('frontend', 'small', 'dark');
} else {
$defaultImageUrl = logo_url('frontend', 'small', 'light');
}
} else {
if (strpos($inputIdValue, 'dark') !== false) {
$defaultImageUrl = logo_url('frontend', 'large', 'dark');
} else {
$defaultImageUrl = logo_url('frontend', 'large', 'light');
}
}
} elseif (strpos($inputIdValue, 'favicon') !== false) {
$defaultImageUrl = favicon_url();
}
}

// Allow override via parameter
if (isset($defaultImage) && $defaultImage) {
$defaultImageUrl = $defaultImage;
}
@endphp

@push('custom-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputId = '{{ $inputId ?? "avatar" }}';
        const defaultImageUrl = '{{ $defaultImageUrl }}';

        // Prevent multiple initializations of the same cropper
        if (window['cropper_initialized_' + inputId]) {
            return;
        }
        window['cropper_initialized_' + inputId] = true;

        // Check if elements exist before proceeding
        const uploadInput = document.getElementById(inputId + '_upload');
        if (!uploadInput) return; // Exit if this cropper instance doesn't exist

        const cropperImage = document.getElementById('cropperImage_' + inputId);
        const croppedPreview = document.getElementById('croppedPreview_' + inputId);
        const cropperModalElement = document.getElementById('cropperModal_' + inputId);
        const cropSizeSelect = document.getElementById('cropSize_' + inputId);
        const cropAndSaveBtn = document.getElementById('cropAndSave_' + inputId);
        const skipCropBtn = document.getElementById('skipCrop_' + inputId);
        const croppedDataInput = document.getElementById(inputId + '_cropped');
        const croppedImageDisplay = document.getElementById('croppedImageDisplay_' + inputId);
        const finalCroppedImage = document.getElementById('finalCroppedImage_' + inputId);
        const cropStatus = document.getElementById('cropStatus_' + inputId);
        const imageType = document.getElementById('imageType_' + inputId);
        const changeCropBtn = document.getElementById('changeCrop_' + inputId);
        const existingImageDisplay = document.getElementById('existingImageDisplay_' + inputId);
        const changeExistingBtn = document.getElementById('changeExisting_' + inputId);
        const removeExistingBtn = document.getElementById('removeExisting_' + inputId);
        const fileInputContainer = document.getElementById('fileInputContainer_' + inputId);
        const removeInput = document.getElementById(inputId + '_remove');

        // New enhanced controls
        const aspectRatioSelect = document.getElementById('aspectRatio_' + inputId);
        const imageQualityRange = document.getElementById('imageQuality_' + inputId);
        const qualityValueSpan = document.getElementById('qualityValue_' + inputId);
        const imageFormatSelect = document.getElementById('imageFormat_' + inputId);
        const customSizeInputs = document.getElementById('customSizeInputs_' + inputId);
        const customWidthInput = document.getElementById('customWidth_' + inputId);
        const customHeightInput = document.getElementById('customHeight_' + inputId);
        const previewDimensions = document.getElementById('previewDimensions_' + inputId);
        const originalInfo = document.getElementById('originalInfo_' + inputId);
        const cropInfo = document.getElementById('cropInfo_' + inputId);
        const autoFitBtn = document.getElementById('autoFit_' + inputId);

        // Enhanced cropper controls
        const cropperRotateLeft = document.getElementById('cropperRotateLeft_' + inputId);
        const cropperRotateRight = document.getElementById('cropperRotateRight_' + inputId);
        const cropperFlipHorizontal = document.getElementById('cropperFlipHorizontal_' + inputId);
        const cropperFlipVertical = document.getElementById('cropperFlipVertical_' + inputId);
        const cropperZoomIn = document.getElementById('cropperZoomIn_' + inputId);
        const cropperZoomOut = document.getElementById('cropperZoomOut_' + inputId);
        const cropperReset = document.getElementById('cropperReset_' + inputId);

        // Exit if any required elements are missing (some elements might not exist if no current image)
        if (!cropperImage || !croppedPreview || !cropperModalElement || !cropSizeSelect ||
            !cropAndSaveBtn || !skipCropBtn || !croppedDataInput || !croppedImageDisplay ||
            !finalCroppedImage || !fileInputContainer) {
            return;
        }

        const cropperModal = new bootstrap.Modal(cropperModalElement);

        let cropper = null;
        let originalImageData = null;
        let originalFileType = null;

        // Handle file upload
        uploadInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const fileType = file.type;
                originalFileType = fileType;

                if (fileType === 'image/gif' || fileType === 'image/jpeg' || fileType === 'image/png' || fileType === 'image/jpg') {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        originalImageData = e.target.result;
                        cropperImage.src = originalImageData;

                        // Update original image info
                        updateOriginalImageInfo(file);

                        // Set default format based on original file
                        if (imageFormatSelect) {
                            imageFormatSelect.value = 'default';
                        }

                        cropperModal.show();

                        // Reset crop size to default
                        cropSizeSelect.value = '300';

                        // Initialize cropper when modal is shown
                        cropperModalElement.addEventListener('shown.bs.modal', function() {
                            if (cropper) {
                                cropper.destroy();
                            }

                            // Get initial aspect ratio
                            const initialAspectRatio = getAspectRatio();

                            cropper = new Cropper(cropperImage, {
                                aspectRatio: initialAspectRatio,
                                viewMode: 1,
                                dragMode: 'move',
                                autoCropArea: 0.8,
                                restore: false,
                                guides: true,
                                center: true,
                                highlight: false,
                                cropBoxMovable: true,
                                cropBoxResizable: true,
                                toggleDragModeOnDblclick: false,
                                responsive: true,
                                checkOrientation: false,
                                crop: function(event) {
                                    updatePreview();
                                    updateCropInfo();
                                },
                                ready: function() {
                                    updateCropInfo();
                                    updatePreview();
                                    // Re-initialize lucide icons
                                    if (typeof lucide !== 'undefined') {
                                        lucide.createIcons();
                                    }
                                }
                            });
                        }, {
                            once: true
                        });
                    };
                    reader.readAsDataURL(file);
                } else {
                    alert('Please select a valid image file (JPEG, PNG, JPG, GIF)');
                    uploadInput.value = '';
                }
            }
        });

        // Get aspect ratio from select
        function getAspectRatio() {
            if (!aspectRatioSelect) return 1;
            const value = aspectRatioSelect.value;
            return value === 'free' ? NaN : parseFloat(value);
        }

        // Get actual output format
        function getActualFormat() {
            if (!imageFormatSelect) return 'jpeg';
            const selectedFormat = imageFormatSelect.value;

            if (selectedFormat === 'default' && originalFileType) {
                // Return original format
                if (originalFileType === 'image/png') return 'png';
                if (originalFileType === 'image/webp') return 'webp';
                return 'jpeg'; // Default for jpeg, jpg, and others
            }

            return selectedFormat === 'default' ? 'jpeg' : selectedFormat;
        }

        // Update original image info
        function updateOriginalImageInfo(file) {
            if (originalInfo && file) {
                const img = new Image();
                img.onload = function() {
                    const sizeKB = Math.round(file.size / 1024);
                    const format = file.type.split('/')[1].toUpperCase();
                    originalInfo.textContent = `${this.width}×${this.height} px, ${format}, ${sizeKB}KB`;
                };
                img.src = URL.createObjectURL(file);
            }
        }

        // Get output dimensions
        function getOutputDimensions() {
            const selectedSize = cropSizeSelect.value;
            if (selectedSize === 'original') {
                return {
                    width: null,
                    height: null
                };
            } else if (selectedSize === 'custom') {
                return {
                    width: parseInt(customWidthInput.value) || 300,
                    height: parseInt(customHeightInput.value) || 300
                };
            } else {
                const size = parseInt(selectedSize);
                return {
                    width: size,
                    height: size
                };
            }
        }

        // Update preview
        function updatePreview() {
            if (cropper && croppedPreview) {
                const selectedSize = cropSizeSelect.value;
                const quality = imageQualityRange ? parseFloat(imageQualityRange.value) / 100 : 0.9;
                const format = getActualFormat();

                if (selectedSize === 'original') {
                    // Show original image in preview
                    croppedPreview.src = originalImageData;
                    croppedPreview.style.width = '150px';
                    croppedPreview.style.height = 'auto';
                    if (previewDimensions) {
                        previewDimensions.textContent = 'Original size';
                    }
                } else {
                    const dimensions = getOutputDimensions();
                    const canvas = cropper.getCroppedCanvas(dimensions);

                    if (canvas) {
                        const mimeType = format === 'png' ? 'image/png' :
                            format === 'webp' ? 'image/webp' : 'image/jpeg';
                        croppedPreview.src = canvas.toDataURL(mimeType, quality);
                        croppedPreview.style.width = '150px';
                        croppedPreview.style.height = '150px';

                        if (previewDimensions) {
                            previewDimensions.textContent = `${dimensions.width} × ${dimensions.height} px`;
                        }
                    }
                }
            }
        }

        // Update crop info
        function updateCropInfo() {
            if (cropper && cropInfo) {
                const selectedSize = cropSizeSelect.value;
                const quality = imageQualityRange ? Math.round(imageQualityRange.value) : 90;
                const format = getActualFormat().toUpperCase();
                const cropBoxData = cropper.getCropBoxData();

                if (selectedSize === 'original') {
                    const imageData = cropper.getImageData();
                    cropInfo.textContent = `${Math.round(imageData.naturalWidth)}×${Math.round(imageData.naturalHeight)} px, ${format}, ${quality}%`;
                } else {
                    const dimensions = getOutputDimensions();
                    cropInfo.textContent = `${dimensions.width}×${dimensions.height} px, ${format}, ${quality}%`;
                }
            }
        }

        // Handle crop size change
        cropSizeSelect.addEventListener('change', function() {
            // Show/hide custom size inputs
            if (customSizeInputs) {
                customSizeInputs.style.display = this.value === 'custom' ? 'block' : 'none';
            }
            updatePreview();
        });

        // Handle aspect ratio change
        if (aspectRatioSelect) {
            aspectRatioSelect.addEventListener('change', function() {
                if (cropper) {
                    const newAspectRatio = getAspectRatio();
                    cropper.setAspectRatio(newAspectRatio);
                    updatePreview();
                    updateCropInfo();
                }
            });
        }

        // Handle quality change
        if (imageQualityRange && qualityValueSpan) {
            imageQualityRange.addEventListener('input', function() {
                qualityValueSpan.textContent = this.value + '%';
                updatePreview();
                updateCropInfo();
            });
        }

        // Handle format change
        if (imageFormatSelect) {
            imageFormatSelect.addEventListener('change', function() {
                updatePreview();
                updateCropInfo();
            });
        }

        // Handle custom size changes
        if (customWidthInput) {
            customWidthInput.addEventListener('input', function() {
                updatePreview();
                updateCropInfo();
            });
        }
        if (customHeightInput) {
            customHeightInput.addEventListener('input', function() {
                updatePreview();
                updateCropInfo();
            });
        }

        // Enhanced cropper controls
        if (cropperRotateLeft) {
            cropperRotateLeft.addEventListener('click', function() {
                if (cropper) {
                    cropper.rotate(-90);
                    updatePreview();
                    updateCropInfo();
                }
            });
        }

        if (cropperRotateRight) {
            cropperRotateRight.addEventListener('click', function() {
                if (cropper) {
                    cropper.rotate(90);
                    updatePreview();
                    updateCropInfo();
                }
            });
        }

        if (cropperFlipHorizontal) {
            cropperFlipHorizontal.addEventListener('click', function() {
                if (cropper) {
                    const imageData = cropper.getImageData();
                    cropper.scaleX(imageData.scaleX === 1 ? -1 : 1);
                    updatePreview();
                    updateCropInfo();
                }
            });
        }

        if (cropperFlipVertical) {
            cropperFlipVertical.addEventListener('click', function() {
                if (cropper) {
                    const imageData = cropper.getImageData();
                    cropper.scaleY(imageData.scaleY === 1 ? -1 : 1);
                    updatePreview();
                    updateCropInfo();
                }
            });
        }

        if (cropperZoomIn) {
            cropperZoomIn.addEventListener('click', function() {
                if (cropper) {
                    cropper.zoom(0.1);
                    updatePreview();
                    updateCropInfo();
                }
            });
        }

        if (cropperZoomOut) {
            cropperZoomOut.addEventListener('click', function() {
                if (cropper) {
                    cropper.zoom(-0.1);
                    updatePreview();
                    updateCropInfo();
                }
            });
        }

        if (cropperReset) {
            cropperReset.addEventListener('click', function() {
                if (cropper) {
                    cropper.reset();
                    updatePreview();
                    updateCropInfo();
                }
            });
        }

        // Auto fit button
        if (autoFitBtn) {
            autoFitBtn.addEventListener('click', function() {
                if (cropper) {
                    cropper.setData({
                        x: 0,
                        y: 0,
                        width: cropper.getImageData().naturalWidth,
                        height: cropper.getImageData().naturalHeight,
                        rotate: 0,
                        scaleX: 1,
                        scaleY: 1
                    });
                    updatePreview();
                    updateCropInfo();
                }
            });
        }

        // Handle crop and save
        cropAndSaveBtn.addEventListener('click', function() {
            const selectedSize = cropSizeSelect.value;
            const quality = imageQualityRange ? parseFloat(imageQualityRange.value) / 100 : 0.9;
            const format = getActualFormat();
            const mimeType = format === 'png' ? 'image/png' :
                format === 'webp' ? 'image/webp' : 'image/jpeg';

            if (selectedSize === 'original') {
                // Use original image
                croppedDataInput.value = originalImageData;
                showCroppedImage(originalImageData, 'Original Image');
                cropperModal.hide();
            } else if (cropper) {
                // Use cropped image with enhanced settings
                const dimensions = getOutputDimensions();
                const canvas = cropper.getCroppedCanvas(dimensions);

                if (canvas) {
                    // Convert to blob with specified quality and format
                    canvas.toBlob(function(blob) {
                        const reader = new FileReader();
                        reader.onload = function() {
                            croppedDataInput.value = reader.result;
                            const sizeText = dimensions.width && dimensions.height ?
                                `${dimensions.width}×${dimensions.height}` : 'Custom';
                            showCroppedImage(reader.result, `Cropped (${sizeText}, ${format.toUpperCase()}, ${Math.round(quality * 100)}%)`);
                            cropperModal.hide();
                        };
                        reader.readAsDataURL(blob);
                    }, mimeType, quality);
                }
            }
        });

        // Handle skip crop
        skipCropBtn.addEventListener('click', function() {
            if (originalImageData) {
                croppedDataInput.value = originalImageData;
                showCroppedImage(originalImageData, 'Original Image (Skipped Crop)');
                cropperModal.hide();
            }
        });

        // Show cropped image display
        function showCroppedImage(imageSrc, typeText) {
            if (finalCroppedImage && imageType && cropStatus) {
                finalCroppedImage.src = imageSrc;
                imageType.textContent = typeText;
                cropStatus.textContent = 'Ready to save';
                cropStatus.className = 'badge bg-success';
                croppedImageDisplay.style.display = 'block';
            }

            // Hide file input container
            fileInputContainer.style.display = 'none';

            // Hide existing image display if it exists
            if (existingImageDisplay) {
                existingImageDisplay.style.display = 'none';
            }
        }

        // Handle change crop button (for newly cropped images)
        if (changeCropBtn) {
            changeCropBtn.addEventListener('click', function() {
                // Directly trigger file input click
                uploadInput.click();
                croppedDataInput.value = '';
                uploadInput.value = '';
            });
        }

        // Handle change existing image button (for images already in database)
        if (changeExistingBtn) {
            changeExistingBtn.addEventListener('click', function() {
                // Directly trigger file input click instead of showing the input
                uploadInput.click();
                if (removeInput) removeInput.value = '0';
            });
        }

        // Handle remove existing image button
        if (removeExistingBtn) {
            removeExistingBtn.addEventListener('click', function() {
                const removeBtn = this;

                // Use SweetAlert2 for confirmation
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'You want to remove this image? This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i data-lucide="trash-2" class="icon-sm me-1"></i> Yes, remove it!',
                    cancelButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Cancel',
                    customClass: {
                        confirmButton: 'btn btn-sm btn-danger me-2',
                        cancelButton: 'btn btn-sm btn-secondary'
                    },
                    buttonsStyling: false,
                    didOpen: () => {
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Set remove flag
                        if (removeInput) removeInput.value = '1';

                        // Clear any temporary cropped image data
                        if (croppedDataInput) croppedDataInput.value = '';

                        // Clear file input
                        if (uploadInput) uploadInput.value = '';

                        // Hide cropped image display if shown
                        if (croppedImageDisplay) croppedImageDisplay.style.display = 'none';

                        // Update display to show default image
                        const img = existingImageDisplay.querySelector('img');
                        const statusBadge = existingImageDisplay.querySelector('.badge');
                        const typeSpan = existingImageDisplay.querySelector('p:nth-child(2) span');
                        const changeBtn = existingImageDisplay.querySelector('#changeExisting_' + inputId);

                        if (img) img.src = defaultImageUrl;
                        if (statusBadge) {
                            statusBadge.className = 'badge bg-warning';
                            statusBadge.textContent = 'Marked for removal';
                        }
                        if (typeSpan) typeSpan.textContent = 'Will use default';
                        if (changeBtn) {
                            changeBtn.innerHTML = '<i data-lucide="undo" class="me-1 icon-sm"></i> Undo Remove';

                            // Change the change button to undo functionality
                            changeBtn.onclick = function() {
                                // Reset remove flag
                                if (removeInput) removeInput.value = '0';

                                // Restore original display
                                location.reload(); // Simple way to restore original state
                            };
                        }

                        // Hide remove button
                        removeBtn.style.display = 'none';
                    }
                });
            });
        }

        // Handle modal focus and accessibility management
        cropperModalElement.addEventListener('show.bs.modal', function() {
            // Remove focus from any active elements before showing modal
            if (document.activeElement && document.activeElement.blur) {
                document.activeElement.blur();
            }
        });

        // Fix aria-hidden when modal is shown
        cropperModalElement.addEventListener('shown.bs.modal', function() {
            cropperModalElement.setAttribute('aria-hidden', 'false');
        });

        // Restore aria-hidden when modal is hidden
        cropperModalElement.addEventListener('hidden.bs.modal', function() {
            cropperModalElement.setAttribute('aria-hidden', 'true');
        });

        // Clean up cropper when modal is hidden
        cropperModalElement.addEventListener('hidden.bs.modal', function() {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            // Remove focus from any buttons to prevent accessibility warnings
            if (document.activeElement && document.activeElement.blur) {
                document.activeElement.blur();
            }
        });
    });
</script>
@endpush