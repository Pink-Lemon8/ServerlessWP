
<div id="document-upload" class="mt-20" style="display: none;">
    <div class="px-4 sm:px-0">
      <h3 class="text-base font-semibold leading-7 text-gray-900">Upload your prescription:</h3>
      <p class="mt-1 text-sm leading-6 text-gray-500">The document types can be <strong>pdf</strong>, <strong>gif</strong>, <strong>jpg</strong>, <strong>png</strong> and <strong>tif</strong> only. Each document uploaded must be less than 10MB.</p>
    </div>
    <div class="mt-6 border-t border-gray-100">
      <form id="prescriptionUpload" class="rx-upload dropzone dz-clickable" method="post" enctype="multipart/form-data" action="https://new.buyinsulin.com/upload-prescription-document/">
        <div class="upload-rx-response" style="display: none;"></div>
        <div class="dz-default dz-message">
          <span>
            <i class="fas fa-file-prescription"></i><i class="far fa-upload"></i><br>
            Click or drag Rx files here to upload
          </span>
        </div>
      </form>
    </div>
  </div>
