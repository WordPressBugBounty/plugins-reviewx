function __rvxMediaUploadComponent__(){
    return {
        isDragging: false,
        multiple: true,
        // accept: 'image/*,video/*',
        accept: 'image/*',
        disabled: false,
        files: {
            urls: [],
            blobFiles: [],
        },
        maxFileCountError: null,

        maxFileSizeError: null,
        maxFileCount: 5, // Set max file count (adjust as needed)
        maxFileSize: 10,  // Set max file size in MB (adjust as needed)

        init() {
            // video_reviews_allowed
            // console.log('this.reviewSettingsData?.data?.setting.review_settings?.video_reviews_allowed', this.reviewSettingsData?.data?.setting.review_settings.reviews?.video_reviews_allowed)
            if(this.reviewSettingsData?.data?.setting.review_settings.reviews?.video_reviews_allowed){
                this.accept = 'image/*,video/*'
            }
            this.clearAll();  // Clear files on init
            this.$nextTick(() => {
                this.notifyFiles();  // Notify parent components about the state of files
            });
        },

        // formatAcceptHandler(){},

        // Drag-and-drop handlers
        dragEnterHandler() {
            this.isDragging = true;
        },
        dragLeaveHandler() {
            this.isDragging = false;
        },
        dropHandler(e) {
            const fileList = e.dataTransfer?.files;
            if (fileList?.length) {
                this.handleFiles(fileList);
                this.dragLeaveHandler();
            }
        },
        notifyFiles() {
            this.$dispatch('notify-attachment',{ reviewAttachFiles:  this.files });
        },
        // File upload (input and drop) handling
        onUpload(event) {
            const target = event.target;
            console.log('target.files', target?.files)
            if (target.files) {
                this.handleFiles(target.files);
                this.notifyFiles()
            }
        },

        handleFiles(fileList) {
            const newFiles = Array.from(fileList);

            // Reset error states
            this.maxFileCountError = null;
            this.maxFileSizeError = null;

            // Check file count constraint
            if (this.maxFileCount && this.files.blobFiles.length + newFiles.length > this.maxFileCount) {
                this.maxFileCountError = `Cannot upload more than ${this.maxFileCount} files.`;
                return;
            }

            // Check file size constraint
            const maxFileSizeBytes = this.maxFileSize ? this.maxFileSize * 1024 * 1024 : null;
            const overSizedFiles = newFiles.filter(
                (file) => maxFileSizeBytes && file.size > maxFileSizeBytes
            );
            if (overSizedFiles.length > 0) {
                this.maxFileSizeError = `Some files exceed the maximum size of ${this.maxFileSize} MB.`;
                return;
            }

            this.files.blobFiles.push(...newFiles);
            this.files.urls.push(...this.createObjectURLs(newFiles));
        },
        createObjectURLs(files) {
            return files.map((file) => ({
                path: URL.createObjectURL(file),
                type: file.type.startsWith('image') ? 'image' : 'video',
            }));
        },
        remove(index) {
            URL.revokeObjectURL(this.files.urls[index].path);
            this.files.blobFiles.splice(index, 1);
            this.files.urls.splice(index, 1);
            this.notifyFiles()
        },

        clearAll() {
            this.files.urls.forEach((file) => URL.revokeObjectURL(file.path));
            this.files.blobFiles = [];
            this.files.urls = [];
            this.notifyFiles()
        }
    }
}
